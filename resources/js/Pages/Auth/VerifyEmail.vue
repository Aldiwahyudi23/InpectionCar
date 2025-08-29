<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Verifikasi Email" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200/50">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-blue-700 mb-2">
                        CekMobil
                    </div>
                    <p class="text-gray-600 text-sm">Verifikasi Email</p>
                </div>

                <!-- Informasi -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-lg text-sm text-blue-700 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Sebelum melanjutkan, mohon verifikasi alamat email Anda dengan mengklik tautan yang kami kirimkan.
                </div>

                <!-- Pesan Sukses -->
                <div v-if="verificationLinkSent" class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg text-sm text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Tautan verifikasi baru telah dikirim ke alamat email Anda. <br> Jika dipesan Email tidak muncul cek di SPAM di dalam menu email
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <PrimaryButton
                            :class="[
                                'w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white',
                                'bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800',
                                'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                                'transition-all duration-200 transform hover:scale-105',
                                form.processing ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Mengirim...
                            </span>
                            <span v-else>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Kirim Ulang Email Verifikasi
                            </span>
                        </PrimaryButton>
                    </div>

                    <!-- Action Links -->
                    <div class="flex flex-col sm:flex-row sm:justify-center sm:space-x-6 space-y-3 sm:space-y-0">
                        <Link
                            :href="route('profile.show')"
                            class="text-sm text-blue-600 hover:text-blue-500 transition-colors duration-200 font-medium flex items-center justify-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Edit Profil
                        </Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200 font-medium flex items-center justify-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Keluar
                        </Link>
                    </div>
                </form>

                <!-- Informasi Tambahan -->
                <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600 text-center">
                    <p class="mb-2">Tidak menerima email?</p>
                    <ul class="text-xs space-y-1">
                        <li>• Periksa folder spam atau junk email</li>
                        <li>• Pastikan email yang terdaftar sudah benar</li>
                        <li>• Tunggu beberapa menit sebelum meminta ulang</li>
                    </ul>
                </div>

                <!-- Footer -->
                <div class="text-center mt-8">
                    <p class="text-xs text-gray-500">
                        © 2024 CekMobil. Aldi Wahyudi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions */
.form-enter-active,
.form-leave-active {
    transition: all 0.3s ease;
}

.form-enter-from,
.form-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Responsive Design */
@media (max-width: 640px) {
    .w-full {
        width: 100%;
    }
    .max-w-md {
        max-width: 95%;
    }
    .p-8 {
        padding: 1.5rem;
    }
}
</style>