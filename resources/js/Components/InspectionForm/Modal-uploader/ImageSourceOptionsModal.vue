<template>
  <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl p-8 w-full max-w-sm mx-auto shadow-2xl transform transition-all duration-300 scale-100 opacity-100">
      <h3 class="text-xl font-bold text-gray-800 mb-2 text-center">Pilih Sumber Gambar</h3>
       <p class="text-xs text-gray-500 mt-2 text-center">Ambil Foto Maksimal: <b>{{ settings.max_files }}</b> Dengan Rasio & Ukuran: <b>{{ settings.camera_aspect_ratio }}</b> </p>
      <div class="flex flex-row space-x-4 mb-6">
        <button
          @click="$emit('open-webcam')"
          class="flex-1 flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg text-gray-700
                 hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-700
                 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                 transition-all duration-200 ease-in-out transform hover:scale-105"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2 text-gray-500 hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span class="text-sm font-medium">Kamera</span>
        </button>
        <button
          @click="$emit('trigger-gallery')"
          class="flex-1 flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg text-gray-700
                 hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-700
                 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                 transition-all duration-200 ease-in-out transform hover:scale-105"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2 text-gray-500 hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span class="text-sm font-medium">Galeri</span>
        </button>
      </div>

      <div class="mt-4 text-right">
        <button
          @click="$emit('close')"
          class="px-5 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg
                 hover:bg-gray-200 hover:text-gray-700
                 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2
                 transition-colors duration-200 ease-in-out"
        >
          Batal
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
    settings: {
    type: Object,
    default: () => ({
      max_files: 1,
      allowed_types: ['jpg', 'png', 'jpeg'],
      camera_aspect_ratio: '3:4',
      enable_flash: true,
      enable_camera_switch: true,
      max_size: 2048 // Default 2MB dalam KB
    })
  },
});

defineEmits(['close', 'open-webcam', 'trigger-gallery']);
</script>