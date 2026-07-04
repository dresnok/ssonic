<?php
// list_folders.php
header("Content-Type: application/json; charset=utf-8");

$jsonPath = __DIR__ . '/../source/data_user.json';

if (!file_exists($jsonPath)) {
    include __DIR__ . '/generate_thumbs.php';
    exit;
}

echo file_get_contents($jsonPath);
