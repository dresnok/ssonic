<?php
// src_my_source/php/generate_thumbs.php

header("Content-Type: application/json; charset=utf-8");
date_default_timezone_set('Europe/Warsaw');

// === KONFIGURACJA ===
$fotoBase  = __DIR__ . '/../foto';
$thumbBase = __DIR__ . '/../source/thumbs';

$fotoDir  = realpath($fotoBase);
$thumbDir = realpath($thumbBase) ?: $thumbBase;

$jsonPath = __DIR__ . '/../source/data_user.json';
$statsPath = __DIR__ . '/../source/gallery_stats.json';

$maxThumbWidth = 300;

$defaultAllowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'jfif'];

// === ROZSZERZENIA Z CONFIG.JS PRZEZ GET ===
$allowedExt = $_GET['allowedExt'] ?? '';

$allowedExt = explode(',', $allowedExt);

$allowedExt = array_map(static function ($ext) {
    return strtolower(trim(ltrim($ext, '.')));
}, $allowedExt);

$allowedExt = array_filter($allowedExt);

$allowedExt = array_values(array_intersect($allowedExt, $defaultAllowedExt));

if (empty($allowedExt)) {
    $allowedExt = $defaultAllowedExt;
}

// === SPRAWDŹ ŚCIEŻKI ===
if (!$fotoDir || !is_dir($fotoDir)) {
    echo json_encode([
        'error' => '❌ Brak katalogu foto',
        'path'  => $fotoBase
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if (!is_dir($thumbDir)) {
    mkdir($thumbDir, 0775, true);
}

// === FUNKCJE ===
function list_images(string $dir, array $allowedExt): array
{
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    $files = [];

    foreach ($rii as $file) {
        if ($file->isDir()) {
            continue;
        }

        $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            continue;
        }

        $rel = str_replace($dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $rel = str_replace('\\', '/', $rel);

        $files[] = $rel;
    }

    sort($files, SORT_NATURAL | SORT_FLAG_CASE);

    return $files;
}

function create_thumbnail(string $src, string $dest, int $maxWidth = 300): bool
{
    $info = @getimagesize($src);

    if (!$info) {
        return false;
    }

    [$width, $height] = $info;

    if (!$width || !$height) {
        return false;
    }

    $newWidth = min($maxWidth, $width);
    $newHeight = (int) round(($newWidth / $width) * $height);

    switch ($info['mime']) {
        case 'image/jpeg':
            $srcImg = @imagecreatefromjpeg($src);
            break;

        case 'image/png':
            $srcImg = @imagecreatefrompng($src);
            break;

        case 'image/webp':
            $srcImg = @imagecreatefromwebp($src);
            break;

        case 'image/gif':
            $srcImg = @imagecreatefromgif($src);
            break;

        default:
            return false;
    }

    if (!$srcImg) {
        return false;
    }

    $thumb = imagecreatetruecolor($newWidth, $newHeight);

    imagealphablending($thumb, false);
    imagesavealpha($thumb, true);

    $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
    imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);

    imagecopyresampled(
        $thumb,
        $srcImg,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $width,
        $height
    );

    $result = imagewebp($thumb, $dest, 85);

    imagedestroy($srcImg);
    imagedestroy($thumb);

    return $result;
}

// === KROK 1: WCZYTAJ JSON, JEŚLI ISTNIEJE ===
$data = file_exists($jsonPath)
    ? json_decode(file_get_contents($jsonPath), true)
    : [];

if (!is_array($data)) {
    $data = [];
}

// === KROK 2: SKANUJ FOLDER FOTO ===
$fotoList = list_images($fotoDir, $allowedExt);

if (empty($fotoList)) {
    echo json_encode([
        'error'      => 'Brak zdjęć w katalogu /source/foto',
        'status'     => 'no_files',
        'allowedExt' => $allowedExt
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$data = [];
$allUrls = [];

$publicFotoPath = '/src_my_source/foto';
$publicSourcePath = '/src_my_source/source';

foreach ($fotoList as $relPath) {
    $src = $fotoDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);

    $thumbRelPath = preg_replace('/\.[^.\/\\\\]+$/', '.webp', $relPath);

    $thumbPath = $thumbDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $thumbRelPath);

$thumbRel = $publicSourcePath . '/thumbs/' . $thumbRelPath;
$url = $publicFotoPath . '/' . $relPath;

    $thumbRel = str_replace('\\', '/', $thumbRel);
    $url = str_replace('\\', '/', $url);

    $allUrls[] = $url;

    if (!file_exists($thumbPath)) {
        $destDir = dirname($thumbPath);

        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        create_thumbnail($src, $thumbPath, $maxThumbWidth);
    }

    $width = null;
    $height = null;

    $info = @getimagesize($src);

    if ($info) {
        $width = $info[0];
        $height = $info[1];
    }

    $data[] = [
        'url'    => $url,
        'thumb'  => $thumbRel,
        'size'   => filesize($src),
        'mtime'  => date('Y-m-d H:i:s', filemtime($src)),
        'width'  => $width,
        'height' => $height
    ];
}

// === KROK 3: USUŃ DUPLIKATY PO URL ===
$unique = [];

foreach ($data as $item) {
    $unique[$item['url']] = $item;
}

$data = array_values($unique);

// === KROK 4: USUŃ Z JSON ZDJĘCIA, KTÓRYCH JUŻ NIE MA ===
$data = array_filter($data, static function ($item) use ($allUrls) {
    return in_array($item['url'], $allUrls, true);
});

$data = array_values($data);

// === KROK 5: ZAPISZ JSON ===
file_put_contents(
    $jsonPath,
    json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

// === KROK 6: STATYSTYKI ===
$stats = [
    'total_files'   => count($data),
    'total_folders' => count(array_unique(array_map(static function ($f) {
        $parts = explode('/', $f['url']);
        array_pop($parts);
        return implode('/', $parts);
    }, $data))),
    'total_size_mb' => round(array_sum(array_column($data, 'size')) / 1024 / 1024, 2),
    'allowed_ext'   => $allowedExt,
    'by_extension'  => []
];

foreach ($data as $item) {
    $ext = strtolower(pathinfo($item['url'], PATHINFO_EXTENSION));

    if (!isset($stats['by_extension'][$ext])) {
        $stats['by_extension'][$ext] = [
            'count'   => 0,
            'size_mb' => 0
        ];
    }

    $stats['by_extension'][$ext]['count']++;
    $stats['by_extension'][$ext]['size_mb'] += round($item['size'] / 1024 / 1024, 2);
}

file_put_contents(
    $statsPath,
    json_encode($stats, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

// === KROK 7: ZWRÓĆ FAKTYCZNĄ LISTĘ ZDJĘĆ ===
echo json_encode(
    array_values($data),
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);

exit;