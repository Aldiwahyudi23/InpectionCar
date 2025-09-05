<template>
  <div class="min-h-screen flex items-center justify-center bg-sky-50 p-4">
    <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full text-center space-y-6 transform transition-all duration-300 hover:scale-105 border border-indigo-200">
      <!-- Icon Sambutan -->
      <div class="flex justify-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 4a4 4 0 100 8 4 4 0 000-8z" />
          <path d="M15 15a5.5 5.5 0 00-6 0c.254.49.52.96 1.134 1.48C11.16 17.65 12 18.25 12 18.25s.84-.6 1.866-1.77c.614-.52 1.396-1.127 1.134-1.48z" />
        </svg>
      </div>

      <!-- Pesan Sambutan -->
      <h1 class="text-3xl font-extrabold text-indigo-800">Halo, {{ user.name }}!</h1>

      <!-- Status Akun -->
      <div v-if="region">
        <p class="text-gray-700 text-base mt-2">
          Selamat datang kembali! Kami senang Anda telah bergabung.
          Semoga Anda bisa bekerja dengan baik bersama kami.
        </p>
        <div class="bg-indigo-100 text-indigo-800 px-6 py-4 rounded-xl shadow-md font-bold text-lg mt-4 border border-indigo-300">
          <p>Wilayah Anda: <span class="font-bold">{{ region.name }}</span></p>
        </div>
        <button 
          @click="goToDashboard"
          class="w-full mt-6 px-6 py-3 rounded-xl font-bold text-lg text-white bg-indigo-600 shadow-lg transform transition-transform duration-200 hover:bg-indigo-700 hover:scale-105"
        >
          Mulai
        </button>
      </div>
      <div v-else>
        <div class="bg-red-50 text-red-600 px-6 py-4 rounded-xl shadow-md font-medium text-base border border-red-200">
          <p>Mohon maaf, akun Anda belum bisa digunakan.</p>
          <p class="mt-2">Silakan hubungi admin atau koordinator daerah Anda untuk aktivasi.</p>
        </div>
      </div>

      <!-- Informasi Tambahan (opsional) -->
      <div class="text-sm text-gray-500 mt-4 pt-4 border-t border-gray-200">
        <p>Terima kasih telah bergabung dengan kami. Kami siap membantu Anda!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.global.user;
const region = page.props.global.region;
const roles = page.props.global.has_roles;
const permissions = page.props.global.permissions;

const goToDashboard = () => {
  router.visit('/dashboard');
};
</script>

<style scoped>
/* Styling tambahan yang mungkin dibutuhkan */
</style>
