<template>
  <button
    v-if="canInstall && !isAppInstalled"
    @click="installApp"
    class="fixed bottom-25 light-6 bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-full shadow-lg z-50 transition-all duration-300 animate-pulse"
    title="Install Aplikasi"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
    </svg>
  </button>
</template>

<script setup>
import { usePWA } from '@/Composables/usePWA';

const { canInstall, isAppInstalled, installApp } = usePWA();
</script>