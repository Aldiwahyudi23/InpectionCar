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
          Preview ({{ currentPreviewIndex + 1 }}/{{ editableImages.length }})
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
<!-- Image Viewer -->
<div class="flex-1 flex items-center justify-center overflow-hidden relative">
  <div
    class="relative w-full max-w-full"
    :style="{
      paddingTop: aspectRatio ? (100 / aspectRatio) + '%' : '75%', // fallback 4:3
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


      <!-- Footer -->
      <div class="flex flex-col gap-3 p-4 bg-black bg-opacity-70 shadow-inner">
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
            class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            Simpan
          </button>

          <button
            v-else
            class="flex-1 px-4 py-3 bg-blue-400 text-white rounded-lg cursor-not-allowed flex items-center justify-center font-medium"
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
});

const emit = defineEmits(['close', 'saveImages', 'removePreviewImage', 'triggerAddMorePhotos']);

const currentPreviewIndex = ref(0);
const editableImages = ref([]);
const touchStartX = ref(0);
const touchEndX = ref(0);

const currentImage = computed(() => {
  if (editableImages.value.length > 0) {
    return editableImages.value[currentPreviewIndex.value];
  }
  return null;
});

// Sync dengan props
watch(
  () => props.images,
  (newImages) => {
    editableImages.value = newImages.map((img) => ({ ...img }));

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
  },
  { deep: true, immediate: true }
);

const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};

const rotateImage = () => {
  if (currentImage.value) {
    currentImage.value.rotation = (currentImage.value.rotation + 90) % 360;
  }
};

const nextImage = () => {
  currentPreviewIndex.value =
    currentPreviewIndex.value < editableImages.value.length - 1 ? currentPreviewIndex.value + 1 : 0;
};

const prevImage = () => {
  currentPreviewIndex.value =
    currentPreviewIndex.value > 0 ? currentPreviewIndex.value - 1 : editableImages.value.length - 1;
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

const saveImages = () => {
  emit('saveImages', editableImages.value);
  emit('close'); // langsung keluar setelah simpan
};

const cancelPreview = () => {
  emit('close');
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
</style>
