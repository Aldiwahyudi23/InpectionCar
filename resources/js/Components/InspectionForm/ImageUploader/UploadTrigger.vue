<template>
  <!-- Versi besar (hanya 1 file) -->
  <label
    v-if="maxFiles == 1"
    @click="$emit('click')"
    class="block w-full border-2 border-dashed rounded-lg cursor-pointer transition-colors duration-200 h-28"
    :class="{
      'border-gray-300 hover:border-indigo-400 bg-gray-50': true,
      'opacity-50 cursor-not-allowed': isUploading
    }"
    :disabled="isUploading"
  >
    <div class="h-full flex flex-col items-center justify-center p-4 text-center">
      <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2
                 l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20
                 h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2
                 2 0 002 2z" />
      </svg>
      <p class="text-sm text-gray-600 font-medium">Upload Image</p>
      <p class="text-xs text-gray-500">Click to open options</p>
    </div>
  </label>

  <!-- Versi kecil (lebih dari 1 file) -->
  <label
    v-else
    @click="$emit('click')"
    class="flex flex-col items-center justify-center p-2 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition-colors w-24 h-24"
    :class="{
      'opacity-50 cursor-not-allowed': isUploading
    }"
    :disabled="isUploading"
  >
         <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2
                 l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20
                 h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2
                 2 0 002 2z" />
      </svg>
     <p class="text-sm text-gray-600 font-medium">Upload</p>

  </label>
  
</template>

<script setup>
defineProps({
  isUploading: Boolean,
  maxFiles: Number,
  settings: Object
});

defineEmits(['click']);
</script>
