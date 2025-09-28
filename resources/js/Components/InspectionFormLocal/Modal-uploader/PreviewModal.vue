<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-95 z-50 flex flex-col items-center justify-center"
  >
    <div class="w-full h-full flex flex-col">
      <div class="flex justify-between items-center p-4 bg-black bg-opacity-70 text-white shadow-md">
        <button
          @click="cancelPreview"
          class="p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors"
          :disabled="isUploading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <div class="text-lg font-semibold">
          {{ point?.name || 'Preview' }} ({{ currentPreviewIndex + 1 }}/{{ editableImages.length }})
        </div>
        <div class="flex items-center gap-2">
          <button
            v-if="currentImage && currentImage.isNew"
            @click="removeCurrentImage"
            class="p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors"
            aria-label="Remove new image"
            :disabled="isUploading"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
          <button
            v-if="currentImage && currentImage.isNew"
            @click="rotateImage"
            class="p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors"
            :disabled="isUploading"
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
      </div>

      <!-- Upload Progress Indicator -->
      <div v-if="isUploading" class="bg-blue-900 bg-opacity-50 text-white p-3">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium">Mengupload gambar...</span>
          <span class="text-xs">{{ uploadProgress }}%</span>
        </div>
        <div class="w-full bg-blue-700 bg-opacity-50 rounded-full h-2">
          <div 
            class="bg-blue-400 h-2 rounded-full transition-all duration-300" 
            :style="{ width: uploadProgress + '%' }"
          ></div>
        </div>
        <p class="text-xs mt-1 text-blue-300">{{ currentUpload }}/{{ totalUpload }} gambar terupload</p>
      </div>

      <div class="flex-1 flex items-center justify-center overflow-hidden relative">
        <div
          class="relative w-full max-w-full"
          :style="{
            paddingTop: aspectRatio ? (100 / aspectRatio) + '%' : '75%',
          }"
        >
          <img
            v-if="currentImage"
            :src="getImageSrc(currentImage)"
            class="absolute inset-0 w-full h-full object-contain transition-transform duration-300 ease-in-out bg-black"
            :style="{ transform: `rotate(${currentImage.rotation}deg)` }"
          />
          
          <!-- Overlay saat uploading -->
          <div v-if="currentImage && currentImage.isUploading" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="text-center text-white">
              <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
              <p class="text-sm">Mengupload...</p>
            </div>
          </div>
        </div>

        <button
          v-if="editableImages.length > 1 && !isUploading"
          @click="prevImage"
          class="absolute left-4 p-3 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
          :disabled="isUploading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button
          v-if="editableImages.length > 1 && !isUploading"
          @click="nextImage"
          class="absolute right-4 p-3 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors"
          :disabled="isUploading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <div class="flex flex-col gap-3 p-4 bg-black bg-opacity-70 shadow-inner">
        <div class="flex justify-between items-center w-full">
          <button 
            v-if="currentImage && !currentImage.isNew" 
            @click="removeCurrentImage" 
            class="px-3 py-2 rounded-lg text-white font-medium flex items-center gap-1 transition-colors hover:text-red-400" 
            :disabled="isUploading"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.095 21H7.905a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Hapus Foto
          </button>
          <div v-else class="flex-1"></div>
          
          <button 
            v-if="allowMultiple && editableImages.length < maxFiles && !isUploading" 
            @click="triggerAddMorePhotos" 
            class="px-3 py-2 rounded-lg text-white font-medium flex items-center gap-1 transition-colors hover:text-indigo-400" 
            :disabled="isUploading"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Foto
          </button>
        </div>

        <div class="flex gap-3 w-full">
          <!-- Tombol Simpan - hanya muncul jika tidak sedang uploading -->
          <button
            v-if="!isUploading"
            @click="saveImages"
            class="flex-1 px-4 py-3 rounded-lg bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white transition-colors font-medium hover:from-indigo-600 hover:to-sky-500"
          >
            Simpan & Upload
          </button>

          <!-- Tombol Uploading Progress -->
          <button
            v-else
            class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-700 to-sky-600 rounded-lg cursor-not-allowed flex items-center justify-center font-medium opacity-75"
            disabled
          >
            <div class="flex items-center gap-2">
              <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <span>Mengupload... {{ uploadProgress }}%</span>
            </div>
          </button>
        </div>

        <!-- Info tambahan saat upload -->
        <div v-if="isUploading" class="text-center">
          <p class="text-xs text-blue-300">
            Modal akan tertutup otomatis. Upload terus berjalan di background.
          </p>
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
  // TAMBAH: Props untuk progress upload
  uploadProgress: {
    type: Number,
    default: 0
  },
  currentUpload: {
    type: Number,
    default: 0
  },
  totalUpload: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['close', 'saveImages', 'removePreviewImage', 'triggerAddMorePhotos', 'update:images']);

const currentPreviewIndex = ref(0);
const editableImages = ref([]);

const currentImage = computed(() => {
  if (editableImages.value.length > 0) {
    return editableImages.value[currentPreviewIndex.value];
  }
  return null;
});

watch(
  () => props.images,
  (newImages) => {
    editableImages.value = newImages.map((img) => ({
      ...img,
      rotation: img.rotation || 0,
    }));
    if (newImages.length === 0 && props.show) {
      cancelPreview();
    }
  },
  { deep: true, immediate: true }
);

watch(
  () => props.show,
  (isShowing) => {
    if (isShowing && props.images.length > 0) {
      currentPreviewIndex.value = props.initialIndex;
    }
  }
);

// TAMBAH: Watch untuk progress upload
watch(
  () => props.uploadProgress,
  (progress) => {
    // Jika upload selesai (100%), bisa tambahkan logika tambahan jika perlu
    if (progress === 100) {
      console.log('Upload completed');
    }
  }
);

const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};

const rotateImage = () => {
  if (currentImage.value && !props.isUploading) {
    const newRotation = (currentImage.value.rotation + 90) % 360;
    editableImages.value[currentPreviewIndex.value].rotation = newRotation;
    emit('update:images', editableImages.value);
  }
};

const nextImage = () => {
  if (!props.isUploading) {
    currentPreviewIndex.value =
      currentPreviewIndex.value < editableImages.value.length - 1 
        ? currentPreviewIndex.value + 1 
        : 0;
  }
};

const prevImage = () => {
  if (!props.isUploading) {
    currentPreviewIndex.value =
      currentPreviewIndex.value > 0 
        ? currentPreviewIndex.value - 1 
        : editableImages.value.length - 1;
  }
};

const saveImages = () => {
  if (!props.isUploading) {
    emit('saveImages', editableImages.value);
  }
};

const cancelPreview = () => {
  if (!props.isUploading) {
    emit('close');
  }
};

const removeCurrentImage = () => {
  if (currentImage.value && !props.isUploading) {
    const imageToRemove = currentImage.value;
    
    const indexToRemove = editableImages.value.findIndex(img => 
      (img.isNew && imageToRemove.isNew && img.preview === imageToRemove.preview) || 
      (!img.isNew && !imageToRemove.isNew && img.id === imageToRemove.id)
    );
    
    if (indexToRemove !== -1) {
      emit('removePreviewImage', imageToRemove);
      editableImages.value.splice(indexToRemove, 1);
      emit('update:images', editableImages.value);
    }

    if (currentPreviewIndex.value >= editableImages.value.length) {
      currentPreviewIndex.value = Math.max(0, editableImages.value.length - 1);
    }
    
    if (editableImages.value.length === 0) {
      emit('close');
    }
  }
};

const triggerAddMorePhotos = () => {
  if (!props.isUploading) {
    emit('triggerAddMorePhotos');
  }
};

// TAMBAH: Handle escape key untuk close modal
import { onMounted, onUnmounted } from 'vue';

const handleKeydown = (event) => {
  if (event.key === 'Escape' && !props.isUploading) {
    cancelPreview();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
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

button {
  transition: all 0.2s ease-in-out;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

img {
  transition: transform 0.3s ease-in-out;
}

/* Animation untuk progress bar */
.progress-bar {
  transition: width 0.3s ease-in-out;
}
</style>