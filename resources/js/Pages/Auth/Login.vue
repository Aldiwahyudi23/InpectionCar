<template>
  <Head title="Log in" />

  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-indigo-100 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
      <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200/50">
        <!-- Logo atau Judul -->
        <div class="text-center mb-8">
          <div class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-indigo-700 mb-2">
            CekMobil
          </div>
          <p class="text-gray-700 text-sm">Masuk ke akun Anda</p>
        </div>

        <!-- Pesan Status -->
        <div v-if="status" class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg text-sm">
          {{ status }}
        </div>

        <!-- Form Login -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Input Email -->
          <div>
            <InputLabel for="email" value="Email" class="block text-sm font-medium text-gray-700 mb-2" />
            <TextInput
              id="email"
              v-model="form.email"
              type="email"
              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-colors duration-200"
              required
              autofocus
              autocomplete="username"
              placeholder="email@example.com"
            />
            <InputError class="mt-2 text-sm text-red-700" :message="form.errors.email" />
          </div>

          <!-- Input Password -->
          <div>
            <InputLabel for="password" value="Kata Sandi" class="block text-sm font-medium text-gray-700 mb-2" />
            <div class="relative mt-1">
              <TextInput
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="block w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-colors duration-200"
                required
                autocomplete="current-password"
                placeholder="Masukkan kata sandi"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3"
                @click="showPassword = !showPassword"
              >
                <svg
                  v-if="showPassword"
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 text-gray-600 hover:text-indigo-700 transition-colors"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 text-gray-600 hover:text-indigo-700 transition-colors"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                  />
                </svg>
              </button>
            </div>
            <InputError class="mt-2 text-sm text-red-700" :message="form.errors.password" />
          </div>

          <!-- Checkbox Remember Me (HIDDEN) -->
          <div class="hidden">
            <label class="flex items-center">
              <Checkbox 
                v-model:checked="form.remember" 
                name="remember" 
                class="text-indigo-700 focus:ring-indigo-600 border-gray-300 rounded"
              />
              <span class="ms-2 text-sm text-gray-700">Ingatkan saya</span>
            </label>
          </div>

          <!-- Tombol Login dan Lupa Password -->
          <div class="flex items-center justify-between mt-4">
            <div></div> <!-- Spacer untuk alignment -->
            
            <Link
              v-if="canResetPassword"
              :href="route('password.request')"
              class="text-sm text-indigo-700 hover:text-indigo-600 transition-colors duration-200 underline"
            >
              Lupa Kata Sandi?
            </Link>
          </div>

          <!-- Tombol Login -->
          <div class="mt-6">
            <PrimaryButton
              :class="[
                'w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white',
                'bg-gradient-to-r from-sky-600 to-indigo-700 hover:from-sky-600 hover:to-indigo-800',
                'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600',
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
                Memproses...
              </span>
              <span v-else>Masuk</span>
            </PrimaryButton>
          </div>
        </form>

        <!-- Tautan ke Login OTP -->
        <div class="text-center mt-6 pt-6 border-t border-gray-200">
          <Link
            :href="route('login-otp')"
            class="text-sm text-indigo-700 hover:text-indigo-600 transition-colors duration-200 font-medium"
          >
            <span class="flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              Masuk Menggunakan Nomor HP
            </span>
          </Link>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
          <p class="text-xs text-gray-600">
            © 2024 CekMobil. Aldi Wahyudi
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const showPassword = ref(false);

const form = useForm({
  email: '',
  password: '',
  remember: true, // Default value di-set true
});

// Pastikan remember selalu true
onMounted(() => {
  form.remember = true;
});

const submit = () => {
  // Pastikan remember selalu true sebelum submit
  form.remember = true;
  
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

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

/* Custom focus styles */
input:focus {
  outline: none;
  ring: 2px;
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