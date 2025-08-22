<template>
  <div
    class="block w-full rounded-lg transition-colors duration-200 h-auto p-2"
    :class="{
      'border-2 border-dashed border-indigo-300 bg-indigo-50': maxFiles == 1,
      'bg-white': maxFiles > 1
    }"
  >
    <div class="grid grid-cols-3 gap-2">
      <div
        v-for="(image, idx) in images"
        :key="image.id || image.preview"
        class="relative w-full overflow-hidden rounded-md border border-gray-200 cursor-pointer"
        :style="getImageContainerStyle(image)"
        @click="$emit('openPreview', idx)"
      >
        <img
          :src="getImageSrc(image)"
          class="absolute top-0 left-0 w-full h-full object-cover"
        >
        <div
          v-if="image.isNew || image.rotation !== 0"
          class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center text-white text-xs font-bold"
        >
          <span v-if="image.isNew">BARU</span>
          <span v-if="image.rotation !== 0" class="ml-1">ROTASI</span>
        </div>

        <button
          @click.stop="$emit('removeImage', image)"
          type="button"
          class="absolute top-1 right-1 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs shadow-sm hover:bg-red-600 transition-colors z-10"
          aria-label="Remove image"
          :disabled="isUploading"
        >
          ×
        </button>
      </div>

      <div
        v-if="allowMultiple && images.length < maxFiles"
        class="flex flex-col items-center justify-center p-2 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition-colors"
        @click="$emit('addMore')"
        :disabled="isUploading"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <p class="mt-1 text-xs text-gray-600">Tambah</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  images: Array,
  allowMultiple: Boolean,
  maxFiles: Number,
  isUploading: Boolean
});

defineEmits(['openPreview', 'removeImage', 'addMore']);

const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};

const getImageContainerStyle = (image) => {
  if (!image.width || !image.height || image.width === 0 || image.height === 0) {
    return {
      paddingBottom: '100%',
      position: 'relative'
    };
  }
  const aspectRatioPercentage = (image.height / image.width) * 100;
  return {
    paddingBottom: `${aspectRatioPercentage}%`,
    position: 'relative'
  };
};
</script>
