<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-90 z-50 flex flex-col items-center justify-center">
    <div class="w-full h-full flex flex-col">
      <div class="flex justify-between items-center p-4 bg-black bg-opacity-50 text-white">
        <button @click="cancelPreview" class="p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <div class="text-lg font-medium">Preview ({{ currentPreviewIndex + 1 }}/{{ editableImages.length }})</div>
        <button @click="rotateImage" class="p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
      </div>

      <div class="flex-1 flex items-center justify-center overflow-hidden relative">
        <div
          class="w-full h-full flex items-center justify-center"
          @touchstart="handleTouchStart"
          @touchmove="handleTouchMove"
          @touchend="handleTouchEnd"
        >
          <img
            v-if="currentImage"
            :src="getImageSrc(currentImage)"
            class="max-w-full max-h-full object-contain"
            :style="{ transform: `rotate(${currentImage.rotation}deg)` }"
          />
        </div>

        <button
          v-if="editableImages.length > 1"
          @click="prevImage"
          class="absolute left-4 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button
          v-if="editableImages.length > 1"
          @click="nextImage"
          class="absolute right-4 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <div class="flex flex-col gap-2 p-4 bg-black bg-opacity-50">
        <div class="flex justify-between">
          <button
            @click="removeCurrentImage" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
            :disabled="isUploading"
          >
            Hapus
          </button>

          <button
            v-if="!isUploading"
            @click="saveImages"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
          >
            Simpan {{ editableImages.length }}
          </button>
          <button
            v-else
            class="px-4 py-2 bg-indigo-400 text-white rounded-lg cursor-not-allowed flex items-center justify-center"
            disabled
          >
            Menyimpan<span class="loading-dots ml-1"><span>.</span><span>.</span><span>.</span></span>
          </button>
        </div>

        <div v-if="allowMultiple && editableImages.length < maxFiles" class="text-center mt-2">
          <button
            @click="addMorePhotos"
            class="inline-block px-4 py-2 rounded-lg text-sm font-medium bg-indigo-700 text-white hover:bg-indigo-800 transition-colors cursor-pointer"
            :disabled="isUploading"
          >
            + Tambah Foto
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
  images: { // Ini adalah allImages dari InputImage.vue
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
});

const emit = defineEmits(['close', 'saveImages', 'removePreviewImage', 'triggerAddMorePhotos']);

const currentPreviewIndex = ref(0);
const editableImages = ref([]); // Salinan lokal gambar yang bisa dimodifikasi
const touchStartX = ref(0);
const touchEndX = ref(0);

const currentImage = computed(() => {
  if (editableImages.value.length > 0) {
    return editableImages.value[currentPreviewIndex.value];
  }
  return null;
});

// Watcher untuk `props.images` dan `props.initialIndex`
watch(() => props.images, (newImages) => {
  editableImages.value = newImages.map(img => ({ ...img }));
  
  if (props.show && newImages.length > 0) {
    if (props.initialIndex === -1 || props.initialIndex >= newImages.length) {
        currentPreviewIndex.value = newImages.length - 1;
    } else {
        currentPreviewIndex.value = props.initialIndex;
    }
  } else {
      currentPreviewIndex.value = 0;
  }

  if (newImages.length === 0 && props.show) {
    cancelPreview();
  }

}, { deep: true, immediate: true });

const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};

const rotateImage = () => {
  if (currentImage.value) {
    currentImage.value.rotation = (currentImage.value.rotation + 90) % 360;
  }
};

const nextImage = () => {
  if (currentPreviewIndex.value < editableImages.value.length - 1) {
    currentPreviewIndex.value++;
  } else {
    currentPreviewIndex.value = 0;
  }
};

const prevImage = () => {
  if (currentPreviewIndex.value > 0) {
    currentPreviewIndex.value--;
  } else {
    currentPreviewIndex.value = editableImages.value.length - 1;
  }
};

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

const removeCurrentImage = () => {
  if (!currentImage.value) return;

  // Emit event ke parent dengan objek gambar yang akan dihapus
  emit('removePreviewImage', currentImage.value); 
  
  // Catatan: Setelah ini, `InputImage` akan memproses penghapusan
  // dan kemudian memperbarui `props.images` dari modal ini.
  // Watcher di atas akan menangani update `editableImages` dan `currentPreviewIndex`
  // berdasarkan perubahan `props.images`.
};

const saveImages = () => {
  emit('saveImages', editableImages.value);
};

const cancelPreview = () => {
  emit('close');
};

const addMorePhotos = () => {
  emit('triggerAddMorePhotos');
};

// Ensure body scroll is managed
watch(() => props.show, (newVal) => {
  if (newVal) {
    document.body.classList.add('modal-open');
  } else {
    document.body.classList.remove('modal-open');
  }
}, { immediate: true });
</script>

<style scoped>
/* Basic modal styles for overlay */
.fixed.inset-0 {
  /* background-color: rgba(0, 0, 0, 0.9); Ini sudah ada di template */
}

body.modal-open {
  overflow: hidden;
}

/* Loading dots animation */
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
  0%, 80%, 100% { opacity: 0; }
  40% { opacity: 1; }
}
</style>