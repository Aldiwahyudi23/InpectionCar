<template>
  <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex flex-col items-center">
      <!-- Loading dots animation -->
      <div class="flex space-x-2">
        <div 
          v-for="i in 3" 
          :key="i"
          class="w-3 h-3 bg-indigo-600 rounded-full animate-bounce"
          :style="`animation-delay: ${(i - 1) * 0.1}s`"
        ></div>
      </div>
      <p class="mt-4 text-gray-700">Memuat...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const isLoading = ref(true);

onMounted(() => {
  // Simulate loading process
  setTimeout(() => {
    isLoading.value = false;
  }, 2000); // Adjust timing as needed
});
</script>