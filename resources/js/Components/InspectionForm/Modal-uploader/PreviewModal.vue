<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-95 z-50 flex flex-col items-center justify-center"
  >
    <div class="w-full h-full flex flex-col">
      <!-- Header -->
      <div class="flex justify-between items-center p-4 bg-black bg-opacity-70 text-white shadow-md">
        <button
          @click="cancelPreview"
          class="p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <div class="text-lg font-semibold">
          {{ point?.name || Camera }} ({{ currentPreviewIndex + 1 }}/{{ editableImages.length }})
        </div>
        <button
          @click="rotateImage"
          class="p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
        </button>
      </div>

      <!-- Image Viewer -->
      <div class="flex-1 flex items-center justify-center overflow-hidden relative">
        <div
          class="relative w-full max-w-full"
          :style="{
            paddingTop: aspectRatio ? (100 / aspectRatio) + '%' : '75%',
          }"
          @touchstart="handleTouchStart"
          @touchmove="handleTouchMove"
          @touchend="handleTouchEnd"
        >
          <img
            v-if="currentImage"
            :src="getImageSrc(currentImage)"
            class="absolute inset-0 w-full h-full object-contain transition-transform duration-300 ease-in-out bg-black"
            :style="{ transform: `rotate(${currentImage.rotation}deg)` }"
          />
        </div>

        <!-- Navigation Buttons -->
        <button
          v-if="editableImages.length > 1"
          @click="prevImage"
          class="absolute left-4 p-3 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button
          v-if="editableImages.length > 1"
          @click="nextImage"
          class="absolute right-4 p-3 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col gap-3 p-4 bg-black bg-opacity-70 shadow-inner">
        <!-- Tombol Hapus untuk gambar yang sudah tersimpan -->
        <button
          v-if="currentImage && !currentImage.isNew"
          @click="removeCurrentImage"
          class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium"
          :disabled="isUploading"
        >
          Hapus Gambar
        </button>

        <!-- Tombol Tambah Gambar jika masih bisa menambah -->
        <button
          v-if="allowMultiple && editableImages.length < maxFiles"
          @click="triggerAddMorePhotos"
          class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium"
          :disabled="isUploading"
        >
          Tambah Gambar
        </button>

        <div class="flex gap-3 w-full">
          <button
            @click="cancelPreview"
            class="flex-1 px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium"
            :disabled="isUploading"
          >
            Batal
          </button>

          <button
            v-if="!isUploading"
            @click="saveImages"
            class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium"
          >
            Simpan
          </button>

          <button
            v-else
            class="flex-1 px-4 py-3 bg-indigo-400 text-white rounded-lg cursor-not-allowed flex items-center justify-center font-medium"
            disabled
          >
            Menyimpan<span class="loading-dots ml-1"><span>.</span><span>.</span><span>.</span></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: Boolean,
  images: {
    type: Array,
    default: () => []
  },
  initialIndex: {
    type: Number,
    default: 0
  },
  allowMultiple: Boolean,
  maxFiles: Number,
  isUploading: Boolean,
  aspectRatio: Number,
  point: Object,
});

const emit = defineEmits(['close', 'saveImages', 'removePreviewImage', 'triggerAddMorePhotos']);

const currentPreviewIndex = ref(0);
const editableImages = ref([]);
const touchStartX = ref(0);
const touchEndX = ref(0);

// Computed property untuk gambar yang sedang aktif
const currentImage = computed(() => {
  if (editableImages.value.length > 0) {
    return editableImages.value[currentPreviewIndex.value];
  }
  return null;
});

// Watch untuk perubahan props images
watch(
  () => props.images,
  (newImages) => {
    // Salin images ke editableImages
    editableImages.value = newImages.map((img) => ({ ...img }));

    if (props.show && newImages.length > 0) {
      // Selalu tampilkan gambar terbaru (index terakhir)
      currentPreviewIndex.value = newImages.length - 1;
    } else {
      currentPreviewIndex.value = 0;
    }

    // Jika tidak ada gambar, tutup modal
    if (newImages.length === 0 && props.show) {
      cancelPreview();
    }
  },
  { deep: true, immediate: true }
);

// Watch untuk perubahan show prop
watch(
  () => props.show,
  (isShowing) => {
    if (isShowing && props.images.length > 0) {
      // Selalu tampilkan gambar terbaru saat modal dibuka
      currentPreviewIndex.value = props.images.length - 1;
    }
  }
);

/**
 * Mendapatkan URL sumber gambar
 */
const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};

/**
 * Memutar gambar 90 derajat
 */
const rotateImage = () => {
  if (currentImage.value) {
    currentImage.value.rotation = (currentImage.value.rotation + 90) % 360;
  }
};

/**
 * Navigasi ke gambar berikutnya
 */
const nextImage = () => {
  currentPreviewIndex.value =
    currentPreviewIndex.value < editableImages.value.length - 1 
      ? currentPreviewIndex.value + 1 
      : 0;
};

/**
 * Navigasi ke gambar sebelumnya
 */
const prevImage = () => {
  currentPreviewIndex.value =
    currentPreviewIndex.value > 0 
      ? currentPreviewIndex.value - 1 
      : editableImages.value.length - 1;
};

/**
 * Menangani swipe gesture untuk navigasi gambar
 */
const handleTouchStart = (e) => {
  touchStartX.value = e.touches[0].clientX;
};

const handleTouchMove = (e) => {
  touchEndX.value = e.touches[0].clientX;
};

const handleTouchEnd = () => {
  const minSwipeDistance = 50;
  if (touchStartX.value - touchEndX.value > minSwipeDistance) {
    nextImage();
  } else if (touchEndX.value - touchStartX.value > minSwipeDistance) {
    prevImage();
  }
  touchStartX.value = 0;
  touchEndX.value = 0;
};

/**
 * Menyimpan gambar (hanya jika tidak sedang uploading)
 */
const saveImages = () => {
  if (!props.isUploading) {
    emit('saveImages', editableImages.value);
    // Tidak langsung close, tunggu sampai upload selesai
  }
};

/**
 * Membatalkan preview dan menutup modal
 */
const cancelPreview = () => {
  if (!props.isUploading) {
    emit('close');
  }
};



/**
 * Menghapus gambar yang sedang dilihat
 */
const removeCurrentImage = () => {
  if (currentImage.value && !props.isUploading) {
    const imageToRemove = currentImage.value;

    // Emit gambar yang akan dihapus (bukan index)
    emit('removePreviewImage', imageToRemove);

    // Hapus dari daftar lokal
    editableImages.value.splice(currentPreviewIndex.value, 1);

    // Adjust index setelah hapus
    if (currentPreviewIndex.value >= editableImages.value.length) {
      currentPreviewIndex.value = Math.max(0, editableImages.value.length - 1);
    }

    // Tutup modal kalau sudah tidak ada gambar
    if (editableImages.value.length === 0) {
      emit('close');
    }
  }
};

/**
 * Memicu penambahan foto baru
 */
const triggerAddMorePhotos = () => {
  if (!props.isUploading) {
    emit('triggerAddMorePhotos');
  }
};
</script>

<style scoped>
.loading-dots span {
  animation: blink 1.4s infinite both;
}
.loading-dots span:nth-child(2) {
  animation-delay: 0.2s;
}
.loading-dots span:nth-child(3) {
  animation-delay: 0.4s;
}
@keyframes blink {
  0%,
  80%,
  100% {
    opacity: 0;
  }
  40% {
    opacity: 1;
  }
}

/* Smooth transitions untuk semua elemen */
button {
  transition: all 0.2s ease-in-out;
}

img {
  transition: transform 0.3s ease-in-out;
}
</style>