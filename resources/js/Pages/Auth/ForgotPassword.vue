<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
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

    <AuthenticationCard>
        <template #logo>
            <div class="text-center">
                <div class="text-4xl font-bold text-indigo-600">CekMobil</div>
                <!-- <div class="text-lg text-gray-600 mt-2">ATURR Yukk</div> -->
            </div>
        </template>

        <div class="mb-4 text-sm text-gray-600 text-center">
            Lupa password Anda? Tidak masalah. Beri tahu kami alamat email Anda, dan kami akan mengirimkan tautan untuk mengatur ulang password Anda.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Kirim Tautan Atur Ulang Password
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>