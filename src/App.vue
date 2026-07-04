<template>
  <main class="hero portfolio3D" id="heroMain">
    <header class="o-header">
      <nav class="o-nav">
        <div class="nav-left">
          <div v-if="!galleryConfig.useDropdownFolders" class="menu-scroll-wrapper">
            <button class="scroll-btn" type="button" @click="scrollMenu(-1)">‹</button>
            <div ref="menuRef" class="menu-inline">
              <button
                type="button"
                :class="{ active: !selectedFolder }"
                @click="selectFolder('')"
              >
                Wszystkie
              </button>
              <button
                v-for="folder in folders"
                :key="folder"
                type="button"
                :class="{ active: selectedFolder === folder }"
                @click="selectFolder(folder)"
              >
                {{ folder }}
              </button>
            </div>
            <button class="scroll-btn" type="button" @click="scrollMenu(1)">›</button>
          </div>

          <div v-else class="folder-select">
            <select id="folder" v-model="selectedFolder" @change="selectFolder(selectedFolder)">
              <option value="">Wszystkie</option>
              <option v-for="folder in folders" :key="folder" :value="folder">
                {{ folder }}
              </option>
            </select>
          </div>
        </div>

        <div v-if="allPhotos.length" class="nav-center">
          <div class="pagination-min">
            <button class="btn" type="button" :disabled="page === 1" @click="prev">
              ‹ 
            </button>
            <span>Strona {{ page }} / {{ pages }}</span>
            <button class="btn" type="button" :disabled="page === pages" @click="next">
               ›
            </button>
          </div>
        </div>
		
<div class="nav-right">

<label class="form__check slideshow-toggle">
  <input
    class="cfg-checkbox"
    type="checkbox"
    :checked="previewControlsVisible"
    @change="togglePreviewControls"
  />

  <span class="slideshow-icon" aria-hidden="true">
<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M27 46C27 45.4477 27.4477 45 28 45H52C52.5523 45 53 45.4477 53 46V54.1672C53 60.9975 49.8275 67.4404 44.4136 71.6049L40.65 74.5C40.2668 74.7948 39.7332 74.7948 39.35 74.5L35.5864 71.6049C30.1725 67.4404 27 60.9975 27 54.1672V46Z" stroke="#C2CCDE" stroke-linecap="round" stroke-linejoin="round" />
  <path d="M40 21V49" stroke="#C2CCDE" stroke-linecap="round" stroke-linejoin="round" />
  <path d="M47.5 5H32.5C32.2239 5 32 5.22386 32 5.5V12.6863C32 14.808 32.8429 16.8429 34.3431 18.3431L34.8787 18.8787C36.2369 20.2369 38.0791 21 40 21C41.9209 21 43.7631 20.2369 45.1213 18.8787L45.6569 18.3431C47.1571 16.8429 48 14.808 48 12.6863V5.5C48 5.22386 47.7761 5 47.5 5Z" stroke="#C2CCDE" stroke-linecap="round" stroke-linejoin="round" />
</svg>
  </span>
</label>
  
</div>
      </nav>
    </header>

    <section class="hero-portfolio">
      <header class="hero-portfolio--header">
        <h1 class="hero-portfolio--header-intro">Ssonic</h1>
      </header>

      <div v-if="loading" class="loader-screen">
        <div class="loader"></div>
        <p>Generuję miniatury i ładuję galerię...</p>
      </div>

      <article v-else class="hero-portfolio--grid">
        <button
          v-for="(img, index) in paginated"
          :key="`${img.url}-${index}`"
          type="button"
          class="hero-portfolio--grid-item c-card"
          :class="{
            'is-focus': focusedIndex === index,
            'no-focus': focusedIndex !== null && focusedIndex !== index,
          }"
          @mouseenter="focusedIndex = index"
          @mouseleave="focusedIndex = null"
          @click="openPreview(img)"
        >
          <img class="c-card--image" :src="img.thumb" loading="lazy" alt="" />
<span
  v-if="showCaptions && getCardCaption(img)"
  class="c-card--name"
>
  {{ getCardCaption(img) }}
</span>
        </button>
      </article>
    </section>




<Teleport to="body">
  <div
  v-if="selectedImage"
  ref="modalContainerRef"
  class="ed-modal-container"
  :class="{
    'is-active': modalActive,
    'is-fullscreen': previewFullscreen,
	'is-remove': modalRemoving
  }"
  @click.self="closePreview"
>

    <!-- DODAJ TUTAJ -->
<div
  v-if="previewControlsVisible"
  class="preview-controls"
  @click.stop
>
<button
  type="button"
  :class="{ active: magnifierEnabled }"
  @click="toggleMagnifier"
>
  🔍 Lupa
</button>

<button type="button" @click="collageMode = !collageMode">
  {{ collageMode ? 'Pojedyncze zdjęcie' : 'Kolaż' }}
</button>

<button type="button" @click="togglePreviewFullscreen">
  ⛶ Fullscreen
</button>
<button
  class="preview-controls-close"
  type="button"
  aria-label="Ukryj ustawienia"
  @click="hidePreviewControls"
>
  ×
</button>


  <label>
    Zmiana co:
    <input
      v-model.number="slideshowSeconds"
      type="number"
      min="1"
      max="60"
    />
    s
  </label>

  <button
    type="button"
    @click="slideshowActive ? stopSlideshow() : startSlideshow()"
  >
    {{ slideshowActive ? 'Stop' : 'Start' }}
  </button>
</div>
	
	
	
  <div class="ed-modal-content">
    <div class="preview-main">
      <button
        class="preview-navigation preview-navigation--left"
        type="button"
        @click.stop="prevImage"
      >
        ‹
      </button>


<div
  v-if="collageMode"
  class="preview-collage"
  :style="{
    '--collage-columns': collageColumns,
    '--collage-rows': collageRows
  }"
>
<img
  v-for="image in collageImages"
  :key="image.url"
  :src="image.url"
  :alt="image.caption || ''"
  @mousemove="moveMagnifier($event, image.url)"
  @mouseleave="hideMagnifier"
/>
  
  
</div>
<img
v-else
  :key="selectedImage.url"
  class="ed-modal-content--image"
  :src="selectedImage.url"
  :alt="selectedImage.caption || ''"
  @mousemove="moveMagnifier($event, selectedImage.url)"
  @mouseleave="hideMagnifier"
/>

      <button
        class="preview-navigation preview-navigation--right"
        type="button"
        @click.stop="nextImage"
      >
        ›
      </button>
    </div>
  </div>

  <div class="preview-thumbnails-container">
    <button
      class="thumbnail-scroll-button"
      type="button"
      @click.stop="scrollThumbnailStrip(-1)"
    >
      ‹
    </button>

    <div
      ref="thumbnailStripRef"
      class="preview-thumbnails"
    >
      <button
        v-for="image in previewImages"
        :key="image.url"
        type="button"
        class="preview-thumbnail"
        :class="{ 'is-active': selectedImage?.url === image.url }"
        :data-image-url="image.url"
        @click.stop="selectPreviewImage(image)"
      >
        <img
          :src="image.thumb"
          :alt="image.caption || ''"
          loading="lazy"
        />
      </button>
	  
    </div>

    <button
      class="thumbnail-scroll-button"
      type="button"
      @click.stop="scrollThumbnailStrip(1)"
    >
      ›
    </button>
  </div>
  
  
<div
  v-if="magnifierEnabled && magnifier"
  class="image-magnifier"
  :style="{
    left: `${magnifier.left}px`,
    top: `${magnifier.top}px`,
    width: `${magnifier.size}px`,
    height: `${magnifier.size}px`,
    backgroundImage: `url('${magnifier.url}')`,
    backgroundSize: magnifier.backgroundSize,
    backgroundPosition: magnifier.backgroundPosition,
  }"
></div>
</div>
    </Teleport>

    <div v-if="enableToasts && showSlideshowMessage" class="slideshow-toast">
      {{ slideshowMessage }}
    </div>
  </main>
</template>

<script setup>
import {
  ref,
  computed,
  watch,
  nextTick,
  onMounted,
  onUnmounted,
} from 'vue'


const galleryConfig = window.galleryConfig || {}
const allPhotos = ref([])
const filtered = ref([])
const folders = ref([])
const selectedFolder = ref('')
const selectedImage = ref(null)
const modalActive = ref(false)
const modalRemoving = ref(false)
const loading = ref(true)
const focusedIndex = ref(null)
const menuRef = ref(null)
const page = ref(1)
const perPage = galleryConfig.perPage || 40
const showCaptions = galleryConfig.showCaptions ?? true
const enableToasts = galleryConfig.enableToasts ?? true
const showImageCounter = galleryConfig.showImageCounter ?? true


const slideshowActive = ref(false)
const slideshowMessage = ref('')
const showSlideshowMessage = ref(false)
let slideshowTimer = null
let messageTimer = null
let closeTimer = null




// lupa

const MAGNIFIER_SIZE = 180
const MAGNIFIER_ZOOM = 2.4

const magnifierEnabled = ref(false)
const magnifier = ref(null)

const toggleMagnifier = () => {
  magnifierEnabled.value = !magnifierEnabled.value
  hideMagnifier()
}

const hideMagnifier = () => {
  magnifier.value = null
}

const moveMagnifier = (event, imageUrl) => {
  if (!magnifierEnabled.value) return

  const img = event.currentTarget
  const rect = img.getBoundingClientRect()

  const x = event.clientX - rect.left
  const y = event.clientY - rect.top

  const url = img.currentSrc || img.src || imageUrl

  magnifier.value = {
    url,
    left: event.clientX + 22,
    top: event.clientY + 22,
    size: MAGNIFIER_SIZE,
    backgroundSize: `${rect.width * MAGNIFIER_ZOOM}px ${rect.height * MAGNIFIER_ZOOM}px`,
    backgroundPosition: `${-(x * MAGNIFIER_ZOOM - MAGNIFIER_SIZE / 2)}px ${-(y * MAGNIFIER_ZOOM - MAGNIFIER_SIZE / 2)}px`,
  }
}



// aktywny slajd
const modalContainerRef = ref(null)
const previewFullscreen = ref(false)

const togglePreviewFullscreen = async () => {
  if (document.fullscreenElement) {
    await document.exitFullscreen()
    return
  }

  await modalContainerRef.value?.requestFullscreen()
}

const handleFullscreenChange = () => {
  previewFullscreen.value = !!document.fullscreenElement
}

const PREVIEW_CONTROLS_KEY = 'previewControlsHidden'

const previewControlsVisible = ref(
  localStorage.getItem(PREVIEW_CONTROLS_KEY) !== '1'
)

const showPreviewControls = () => {
  localStorage.removeItem(PREVIEW_CONTROLS_KEY)
  previewControlsVisible.value = true
}

const hidePreviewControls = () => {
  localStorage.setItem(PREVIEW_CONTROLS_KEY, '1')
  previewControlsVisible.value = false
}

const togglePreviewControls = (event) => {
  event.target.checked
    ? showPreviewControls()
    : hidePreviewControls()
}






const collageMode = ref(false)

const slideshowSeconds = ref(
  Math.max(1, (galleryConfig.slideshowInterval ?? 4000) / 1000)
)



const collageCount = computed(() => {
  const count = Number(galleryConfig.collageCount) || 4
  return Math.min(Math.max(count, 1), 22)
})

const collageImages = computed(() => {
  const images = previewImages.value
  if (!images.length) return []

  const start = Math.max(0, selectedImageIndex.value)

  return Array.from(
    { length: Math.min(collageCount.value, images.length) },
    (_, index) => images[(start + index) % images.length]
  )
})
const collageColumns = computed(() =>
  Math.ceil(Math.sqrt(collageImages.value.length))
)

const collageRows = computed(() =>
  Math.ceil(collageImages.value.length / collageColumns.value)
)
const startSlideshow = () => {
  clearInterval(slideshowTimer)

  slideshowActive.value = true

  const delay = Math.max(1, Number(slideshowSeconds.value) || 4) * 1000

  slideshowTimer = setInterval(nextImage, delay)
}

watch(slideshowSeconds, () => {
  if (slideshowActive.value) startSlideshow()
})

const thumbnailStripRef = ref(null)

const previewImages = computed(() => filtered.value)

const getImageCounterText = (image) => {
  if (!showImageCounter) return ''

  const index = filtered.value.findIndex(
    (photo) => photo.url === image.url
  )

  if (index === -1) return ''

  return `${index + 1}/${filtered.value.length}`
}

const getCardCaption = (image) => {
  const parts = []

  const counter = getImageCounterText(image)

  if (counter) {
    parts.push(counter)
  }

  if (image.caption) {
    parts.push(image.caption)
  }

  return parts.join(' • ')
}

const selectedImageIndex = computed(() => {
  if (!selectedImage.value) return -1

  return previewImages.value.findIndex(
    (image) => image.url === selectedImage.value.url
  )
})

const imageCounterText = computed(() => {
  if (!selectedImage.value) return ''
  if (selectedImageIndex.value < 0) return ''

  return `Zdjęcie ${selectedImageIndex.value + 1}/${previewImages.value.length}`
})

const scrollActiveThumbnail = async () => {
  await nextTick()

  const strip = thumbnailStripRef.value
  if (!strip || !selectedImage.value) return

  const activeThumbnail = strip.querySelector(
    `[data-image-url="${CSS.escape(selectedImage.value.url)}"]`
  )

  activeThumbnail?.scrollIntoView({
    behavior: 'smooth',
    block: 'nearest',
    inline: 'center',
  })
}

const selectPreviewImage = (image) => {
  selectedImage.value = image
}

const nextImage = () => {
  const images = previewImages.value
  if (!images.length) return

  const currentIndex = selectedImageIndex.value
  const nextIndex =
    currentIndex >= images.length - 1
      ? 0
      : currentIndex + 1

  selectedImage.value = images[nextIndex]
}

const prevImage = () => {
  const images = previewImages.value
  if (!images.length) return

  const currentIndex = selectedImageIndex.value
  const previousIndex =
    currentIndex <= 0
      ? images.length - 1
      : currentIndex - 1

  selectedImage.value = images[previousIndex]
}

const scrollThumbnailStrip = (direction) => {
  thumbnailStripRef.value?.scrollBy({
    left: direction * 400,
    behavior: 'smooth',
  })
}

watch(
  () => selectedImage.value?.url,
  () => {
    scrollActiveThumbnail()
  }
)

const pages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)))
const paginated = computed(() => {
  const start = (page.value - 1) * perPage
  return filtered.value.slice(start, start + perPage)
})



const getPhotoFolder = (url) => {
  const normalized = String(url || '').replace(/\\/g, '/')
  const marker = '/foto/'
  const markerIndex = normalized.indexOf(marker)

  if (markerIndex === -1) return ''

  const relativePath = normalized.slice(markerIndex + marker.length)
  const lastSlash = relativePath.lastIndexOf('/')

  return lastSlash === -1
    ? ''
    : relativePath.slice(0, lastSlash)
}

const mapWithCaptions = (data) => {
  return (Array.isArray(data) ? data : []).map((image) => {
    const url = String(image.url || '').replace(/\\/g, '/')
    const thumb = String(image.thumb || '').replace(/\\/g, '/')
    const fileName = url.split('/').pop() || ''
    const extension = fileName.includes('.')
      ? fileName.split('.').pop().toLowerCase()
      : ''

    const sizeKb = Math.max(
      1,
      Math.round(Number(image.size || 0) / 1024)
    )

    const captionParts = []

    if (galleryConfig.nameCaptions && fileName) {
      captionParts.push(fileName)
    }

    if (galleryConfig.extensionCaptions && extension) {
      captionParts.push(extension.toUpperCase())
    }

    if (galleryConfig.sizeCaptions) {
      captionParts.push(`${sizeKb} KB`)
    }

    if (
      galleryConfig.resolutionCaptions &&
      image.width &&
      image.height
    ) {
      captionParts.push(`${image.width}×${image.height}`)
    }

    return {
      ...image,
      url,
      thumb,
      folder: getPhotoFolder(url),
      ext: extension,
      caption: captionParts.join(' • '),
    }
  })
}

const selectFolder = (folder) => {
  selectedFolder.value = folder
  page.value = 1

  filtered.value = folder
    ? allPhotos.value.filter((photo) => photo.folder === folder)
    : allPhotos.value
}

const scrollMenu = (direction) => {
  const menu = menuRef.value
  if (!menu) return
  const step = menu.querySelector('button')?.offsetWidth || 200
  menu.scrollLeft += direction * step * 2
}

const next = () => {
  if (page.value < pages.value) page.value += 1
}

const prev = () => {
  if (page.value > 1) page.value -= 1
}

const openPreview = async (image) => {
  selectedImage.value = image
  previewControlsVisible.value =
    localStorage.getItem(PREVIEW_CONTROLS_KEY) !== '1'

  modalActive.value = false

  await nextTick()

  modalActive.value = true
  scrollActiveThumbnail()
}

const closePreview = () => {
	hideMagnifier()
  stopSlideshow()
  modalActive.value = false
  modalRemoving.value = true
  clearTimeout(closeTimer)
  closeTimer = setTimeout(() => {
    selectedImage.value = null
    modalRemoving.value = false
  }, 300)
}





const showMessage = (text, duration = 3000) => {
  if (!enableToasts) return
  clearTimeout(messageTimer)
  slideshowMessage.value = text
  showSlideshowMessage.value = true
  messageTimer = setTimeout(() => {
    showSlideshowMessage.value = false
  }, duration)
}



function stopSlideshow() {
  if (!slideshowActive.value) return
  slideshowActive.value = false
  clearInterval(slideshowTimer)
  slideshowTimer = null
  showMessage('⏸ Wyłączony')
}

const handleKey = (event) => {
  if (!selectedImage.value) return
  if (event.key === ' ' || event.key === 'Spacebar') event.preventDefault()

  if (event.key === 'Escape') {
  if (document.fullscreenElement) return
  closePreview()
}
 if (event.key.toLowerCase() === 'f') {
    event.preventDefault()
    togglePreviewFullscreen()
  }
  
  if (event.key === 'ArrowRight' || event.key === ' ' || event.key === 'Spacebar') nextImage()
  if (event.key === 'ArrowLeft') prevImage()
  if (event.key === 'Enter') {
    slideshowActive.value ? stopSlideshow() : startSlideshow()
  }
}





let galleryPromise = null
let galleryLoaded = false

const loadGallery = async (force = false) => {
  if (galleryLoaded && !force) return

  if (galleryPromise) {
    return galleryPromise
  }

  galleryPromise = (async () => {
    loading.value = true

    try {
const params = new URLSearchParams({
  allowedExt: allowedExt.value.join(','),
})

const response = await fetch(
  `/src_my_source/php/generate_thumbs.php?${params.toString()}`,
  {
    cache: 'no-store',
  }
)

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()

      if (!Array.isArray(data)) {
        throw new Error(data?.error || 'Nieprawidłowa odpowiedź PHP')
      }

      const photos = mapWithCaptions(data)

      allPhotos.value = photos

      folders.value = [
        ...new Set(
          photos
            .map((photo) => photo.folder)
            .filter(Boolean)
        ),
      ].sort((a, b) =>
        a.localeCompare(b, 'pl', {
          sensitivity: 'base',
        })
      )

      selectFolder(selectedFolder.value)
      galleryLoaded = true
    } catch (error) {
      console.error('Błąd ładowania galerii:', error)

      allPhotos.value = []
      filtered.value = []
      folders.value = []
      page.value = 1
    } finally {
      loading.value = false
    }
  })()

  try {
    await galleryPromise
  } finally {
    galleryPromise = null
  }
}

const applyBackground = () => {
  const allowedBackgrounds = ['dark', 'green', 'blue', 'red', 'purple']
  const background = galleryConfig.background || 'dark'

  document.body.dataset.background = allowedBackgrounds.includes(background)
    ? background
    : 'dark'
}

const allowedExt = computed(() => {
  const fallback = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'jfif']

  const extensions = Array.isArray(galleryConfig.allowedExt)
    ? galleryConfig.allowedExt
    : fallback

  return extensions
    .map((ext) => String(ext).replace(/^\./, '').toLowerCase().trim())
    .filter(Boolean)
})

onMounted(() => {
  applyBackground()
  window.addEventListener('keydown', handleKey)
  document.addEventListener('fullscreenchange', handleFullscreenChange)
  loadGallery()
})

onUnmounted(() => {
	document.removeEventListener('fullscreenchange', handleFullscreenChange)
  window.removeEventListener('keydown', handleKey)
    clearInterval(slideshowTimer)

  clearTimeout(messageTimer)
  clearTimeout(closeTimer)
})
</script>


<style scoped>
.ed-modal-content {
  width: min(94vw, 1200px);
  max-width: none !important;
  height: calc(100vh - 130px);
  max-height: none;

  display: flex;
  justify-content: center;
  align-items: center;

  background: transparent;
}


.ed-modal-container {
  position: fixed;
  inset: 0;
  overflow: hidden;
}

.preview-main {
  width: 100%;
  height: 100%;
  position: static;

  display: flex;
  justify-content: center;
  align-items: center;
}

.ed-modal-content--image {
  width: auto;
  height: auto;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  display: block;
}

.preview-navigation {
  position: fixed;
  top: 50%;
  z-index: 2100;

  width: 52px;
  height: 72px;
  padding: 0;
  border: 0;
  border-radius: 8px;

  background: rgba(0, 0, 0, 0.55);
  color: #fff;
  font-size: 2.5rem;
  line-height: 1;
  cursor: pointer;

  transform: translateY(-50%);
  transition:
    background 0.2s ease,
    opacity 0.2s ease;
}

.preview-navigation--left {
  left: 24px;
}

.preview-navigation--right {
  right: 24px;
}

.preview-navigation:hover {
  background: rgba(0, 0, 0, 0.85);
}

.preview-navigation:active {
  background: rgba(0, 0, 0, 1);
}

.preview-thumbnails-container {
  position: absolute;
  left: 50%;
  bottom: 20px;
  z-index: 2200;

  width: min(90vw, 1100px);
  display: flex;
  align-items: center;
  gap: 0.5rem;

  transform: translateX(-50%);
}

.preview-thumbnails {
  flex: 1;
  min-width: 0;
  display: flex;
  gap: 0.5rem;

  overflow-x: auto;
  overflow-y: hidden;

  padding: 0.5rem;
  border-radius: 8px;

  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(6px);

  scroll-behavior: smooth;
  scrollbar-width: thin;
}

.preview-thumbnail {
  width: 76px;
  height: 60px;
  flex: 0 0 76px;
  padding: 0;
  border: 2px solid transparent;
  border-radius: 4px;
  overflow: hidden;
  background: #111;
  opacity: 0.55;
  cursor: pointer;
  transition:
    opacity 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.preview-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.preview-thumbnail:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.preview-thumbnail.is-active {
  border-color: #fff;
  opacity: 1;
}

.thumbnail-scroll-button {
  width: 36px;
  height: 60px;
  flex: 0 0 36px;
  padding: 0;
  border: 0;
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.65);
  color: #fff;
  font-size: 2rem;
  cursor: pointer;
}

.thumbnail-scroll-button:hover {
  background: rgba(0, 0, 0, 0.9);
}

@media (max-width: 640px) {
  .preview-thumbnail {
    width: 62px;
    height: 50px;
    flex-basis: 62px;
  }

  .thumbnail-scroll-button {
    display: none;
  }

  .preview-navigation {
    width: 42px;
    height: 58px;
    font-size: 2rem;
  }

  .preview-navigation--left {
    left: 8px;
  }

  .preview-navigation--right {
    right: 8px;
  }

  .preview-thumbnails-container {
    bottom: 8px;
    width: calc(100vw - 16px);
  }

  .preview-thumbnails {
    padding: 0.35rem;
  }

  .ed-modal-content {
    height: calc(100vh - 90px);
  }

  .ed-modal-content--image {
    max-height: calc(100vh - 115px);
  }
}


.preview-controls {
  position: fixed;
  top: 20px;
  left: 50%;
  z-index: 9999;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: white;
  color: black;
  border: 2px solid red;
}

.preview-controls input {
  width: 65px;
}





.preview-controls-close {
  position: absolute;
  top: -10px;
  right: -10px;

  width: 28px;
  height: 28px;
  padding: 0;
  border: 0;
  border-radius: 50%;

  background: #111;
  color: #fff;
  font-size: 20px;
  line-height: 28px;
  cursor: pointer;
}


/* przycisk */

.form__check {
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

input.cfg-checkbox {
  appearance: none;
  -webkit-appearance: none;

  width: 18px;
  height: 18px;
  min-width: 18px;
  flex: 0 0 18px;

  margin: 0;
  padding: 0;
  border: 0;
  border-radius: 4px;

  background: rgba(255,255,255,.14);
  display: inline-grid;
  place-items: center;
  cursor: pointer;
}

input.cfg-checkbox::after {
  content: "";
  width: 10px;
  height: 10px;
  border-radius: 2px;
  background: rgba(255,255,255,.9);
  transform: scale(0);
  transition: transform 140ms ease;
}

input.cfg-checkbox:checked {
  background: rgba(52,165,142,.65);
}

input.cfg-checkbox:checked::after {
  transform: scale(1);
}

input.cfg-checkbox:focus-visible {
  outline: 2px solid rgba(255,255,255,.28);
  outline-offset: 3px;
}


/* fullscreen */
.ed-modal-container.is-fullscreen {
  background: #000;
}

.ed-modal-container.is-fullscreen .preview-controls,
.ed-modal-container.is-fullscreen .preview-thumbnails-container,
.ed-modal-container.is-fullscreen .preview-navigation {
  display: none;
}

.ed-modal-container.is-fullscreen .ed-modal-content {
  width: 100vw;
  height: 100vh;
}

.ed-modal-container.is-fullscreen .preview-main {
  width: 100vw;
  height: 100vh;
  background: #000;
}

.ed-modal-container.is-fullscreen .ed-modal-content--image {
  max-width: 100vw;
  max-height: 100vh;
  object-fit: contain;
}

.preview-collage {
  width: min(90vw, 1100px);
  height: calc(100vh - 160px);
  display: grid;
  gap: 8px;
  grid-template-columns: repeat(var(--collage-columns), 1fr);
  grid-template-rows: repeat(var(--collage-rows), 1fr);
}

.preview-collage img {
  width: 100%;
  height: 100%;
  min-width: 0;
  min-height: 0;
  object-fit: cover;
}

.ed-modal-container.is-fullscreen .preview-collage {
  width: 100vw;
  height: 100vh;
  gap: 0;
  background: #000;
  grid-template-columns: repeat(var(--collage-columns), 1fr);
  grid-template-rows: repeat(var(--collage-rows), 1fr);
}

.ed-modal-container.is-fullscreen .preview-collage img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}




/* svg */
.slideshow-toggle {
  gap: 6px;
}

.slideshow-icon {
  width: 28px;
  height: 28px;
  display: inline-grid;
  place-items: center;
}

.slideshow-icon svg {
  width: 28px;
  height: 28px;
  display: block;
}

.slideshow-icon path {
  stroke: #1b2b35;
  stroke-linecap: round;
  stroke-linejoin: round;
  transition: stroke 160ms ease;
}

.cfg-checkbox:checked + .slideshow-icon path {
  stroke: #34a58e;
}


/* lupa */

.image-magnifier {
  position: fixed;
  z-index: 2600;
  border: 2px solid rgba(255,255,255,.9);
  border-radius: 50%;
  background-repeat: no-repeat;
  background-color: #111;
  box-shadow: 0 10px 30px rgba(0,0,0,.45);
  pointer-events: none;
  transform: translate(0, 0);
}

.preview-controls button.active {
  background: rgba(52,165,142,.65);
  color: #fff;
}



</style>

