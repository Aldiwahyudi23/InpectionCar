<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40 p-4">
    <div class="bg-white w-full max-w-sm rounded-xl shadow-xl overflow-hidden">
      <div class="p-4">
        <h3 class="text-lg font-medium text-center text-gray-800 mb-4">Select Image Source</h3>
        <div class="flex justify-center gap-6">
          <button @click="$emit('openWebcam')" class="flex flex-col items-center justify-center w-24 h-24 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors">
            <div class="bg-blue-100 p-3 rounded-full mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <span class="text-sm font-medium text-gray-700">Camera</span>
          </button>
          
          <button @click="$emit('triggerGallery')" class="flex flex-col items-center justify-center w-24 h-24 rounded-xl bg-purple-50 hover:bg-purple-100 transition-colors">
            <div class="bg-purple-100 p-3 rounded-full mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <span class="text-sm font-medium text-gray-700">Gallery</span>
          </button>
        </div>
      </div>
      <button @click="$emit('close')" class="w-full py-3 text-center text-gray-500 font-medium border-t border-gray-100 hover:bg-gray-50 transition-colors">Cancel</button>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

defineProps({
  show: {
    type: Boolean,
    default: false
  }
});

defineEmits(['close', 'openWebcam', 'triggerGallery']);
</script>