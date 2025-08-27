<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Lupa Password" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200/50">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-blue-700 mb-2">
                        CekMobil
                    </div>
                    <p class="text-gray-600 text-sm">Atur Ulang Password</p>
                </div>

                <!-- Pesan Status -->
                <div v-if="status" class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg text-sm text-center">
                    {{ status }}
                </div>

                <!-- Informasi -->
                <div class="mb-6 text-sm text-gray-600 text-center">
                    Lupa password Anda? Tidak masalah. Beri tahu kami alamat email Anda, dan kami akan mengirimkan tautan untuk mengatur ulang password Anda.
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Email" class="block text-sm font-medium text-gray-700 mb-2" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="email@example.com"
                        />
                        <InputError class="mt-2 text-sm text-red-600" :message="form.errors.email" />
                    </div>

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
                            <span v-else>Kirim Tautan Atur Ulang Password</span>
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Tautan kembali ke login -->
                <div class="text-center mt-8 pt-6 border-t border-gray-200">
                    <a
                        :href="route('login')"
                        class="text-sm text-blue-600 hover:text-blue-500 transition-colors duration-200 font-medium flex items-center justify-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Halaman Login
                    </a>
                </div>

                <!-- Footer -->
                <div class="text-center mt-8">
                    <p class="text-xs text-gray-500">
                        © 2024 CekMobil. Aldi Wahyudi
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