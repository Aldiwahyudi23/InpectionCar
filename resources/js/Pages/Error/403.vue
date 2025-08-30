<template>
  <AppLayout title="Akses Ditolak">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8 text-center">
        <div>
          <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.894-.833-2.664 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Akses Ditolak</h2>
          <p class="mt-2 text-sm text-gray-600">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        </div>
        
        <div class="mt-8 space-y-4">
          <button
            @click="goBack"
            class="w-full bg-indigo-600 border border-transparent rounded-md py-2 px-4 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Kembali
          </button>
          
          <button
            @click="goHome"
            class="w-full bg-gray-200 border border-transparent rounded-md py-2 px-4 text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Ke Halaman Utama
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { defineProps } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const goBack = () => {
  window.history.length > 1 ? router.visit('back') : router.visit('/');
};

const goHome = () => {
  router.visit('/');
};
</script>