<script setup>
import { usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const { auth } = usePage().props;

const isActive = computed(() => auth.user?.is_active);

const handleAction = () => {
    if (isActive.value) {
        router.visit(route('dashboard'));
    } else {
        router.post(route('account.inactive.logout'));
    }
};
</script>

<template>
    <Head :title="isActive ? 'Akun Aktif' : 'Akun Tidak Aktif'" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 p-4">
        <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-md w-full text-center space-y-6 transform transition-all duration-300 border border-gray-200">
            <!-- Icon -->
            <div class="flex justify-center mb-4">
                <svg v-if="isActive" class="h-24 w-24 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg v-else class="h-24 w-24 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <!-- Title -->
            <h2 class="text-3xl font-bold" :class="isActive ? 'text-green-800' : 'text-red-800'">
                {{ isActive ? 'Akun Aktif Kembali!' : 'Akun Tidak Aktif' }}
            </h2>

            <!-- Status Badge -->
            <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium" 
                 :class="isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                <span class="w-2 h-2 rounded-full mr-2" :class="isActive ? 'bg-green-500' : 'bg-red-500'"></span>
                Status: {{ isActive ? 'Aktif' : 'Tidak Aktif' }}
            </div>

            <!-- Message -->
            <div class="text-gray-700 space-y-3">
                <p v-if="isActive" class="text-lg">
                    🎉 Selamat! Akun Anda telah diaktifkan kembali.
                </p>
                <p v-else class="text-lg">
                    Maaf, akun Anda saat ini dinonaktifkan.
                </p>

                <div class="bg-gray-50 p-4 rounded-xl border" :class="isActive ? 'border-green-200' : 'border-red-200'">
                    <p v-if="isActive" class="text-green-700 font-medium">
                        Anda sekarang dapat mengakses semua fitur sistem secara penuh.
                    </p>
                    <p v-else class="text-red-700 font-medium">
                        Anda tidak dapat mengakses sistem sampai akun diaktifkan kembali oleh administrator.
                    </p>
                </div>
            </div>

            <!-- Action Button -->
            <button
                @click="handleAction"
                class="w-full py-3 px-6 rounded-xl font-semibold text-lg text-white shadow-lg transform transition-transform duration-200 hover:scale-105"
                :class="isActive ? 
                    'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700' : 
                    'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700'"
            >
                <span class="flex items-center justify-center">
                    <svg v-if="isActive" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <svg v-else class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    {{ isActive ? 'Pergi ke Dashboard' : 'Keluar dari Sistem' }}
                </span>
            </button>

            <!-- Additional Info -->
            <div class="text-sm text-gray-500 pt-4 border-t border-gray-200">
                <p v-if="isActive">
                    Terima kasih telah menunggu. Selamat bekerja! 🚀
                </p>
                <p v-else>
                    Silakan hubungi administrator untuk bantuan lebih lanjut.
                </p>
            </div>

            <!-- User Info -->
            <div class="text-xs text-gray-400">
                <p>User: {{ auth.user?.name }}</p>
                <p>Email: {{ auth.user?.email }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions */
button {
    transition: all 0.3s ease;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}
</style>