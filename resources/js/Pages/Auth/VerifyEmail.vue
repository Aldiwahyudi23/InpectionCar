<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
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

    <AuthenticationCard>
        <template #logo>
            <div class="text-center">
                <div class="text-4xl font-bold text-indigo-600">CelMobil</div>
                <!-- <div class="text-lg text-gray-600 mt-2">ATURR Yukk</div> -->
            </div>
        </template>

        <div class="mb-4 text-sm text-gray-600 text-center">
            Sebelum melanjutkan, mohon verifikasi alamat email Anda dengan mengklik tautan yang kami kirimkan ke email Anda. Jika Anda tidak menerima email, kami akan dengan senang hati mengirimkan yang baru.
        </div>

        <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-green-600 text-center">
            Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan di pengaturan profil.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex flex-col items-center justify-between space-y-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Kirim Ulang Email Verifikasi
                </PrimaryButton>

                <div class="flex space-x-4">
                    <Link
                        :href="route('profile.show')"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Edit Profil
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Keluar
                    </Link>
                </div>
            </div>
        </form>
    </AuthenticationCard>
</template>