<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Daftar Akun" />

    <AuthenticationCard>
         <template #logo>
            <div class="text-center">
                <div class="text-4xl font-bold text-indigo-600">CekMobil</div>
                <!-- <div class="text-lg text-gray-600 mt-2">ATURR Yukk</div> -->
            </div>
        </template>

        <form @submit.prevent="submit" class="animate__animated animate__fadeInUp">
            <!-- Nama -->
            <div>
                <InputLabel for="name" value="Nama Lengkap" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-300"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2 text-sm text-red-600" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-300"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2 text-sm text-red-600" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-300"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2 text-sm text-red-600" :message="form.errors.password" />
            </div>

            <!-- Konfirmasi Password -->
            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-300"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2 text-sm text-red-600" :message="form.errors.password_confirmation" />
            </div>

            <!-- Syarat dan Ketentuan -->
            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                        <div class="ms-2">
                            Saya setuju dengan <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-600 hover:text-indigo-500 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Syarat Layanan</a> dan <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-600 hover:text-indigo-500 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Kebijakan Privasi</a>
                        </div>
                    </div>
                    <InputError class="mt-2 text-sm text-red-600" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <!-- Tombol Daftar dan Link Login -->
            <div class="flex items-center justify-end mt-6">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-indigo-500 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-300">
                    Sudah punya akun?
                </Link>
                <PrimaryButton class="ms-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Daftar
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>

<style>
@import 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css';
</style>