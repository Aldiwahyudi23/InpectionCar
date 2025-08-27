<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
      <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200/50">
        <!-- Logo -->
        <div class="text-center mb-8">
          <div class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-blue-700 mb-2">
            CekMobil
          </div>
          <h2 class="text-xl font-semibold text-gray-800">
            {{ otpSent ? 'Masukkan Kode OTP' : 'Login dengan Nomor HP' }}
          </h2>
          <p class="text-gray-600 text-sm mt-1">
            {{ otpSent ? 'Masukkan 4 digit kode yang dikirim via SMS' : 'Masukkan nomor HP Anda untuk melanjutkan' }}
          </p>
        </div>

        <!-- Form Nomor HP -->
        <form v-if="!otpSent" @submit.prevent="sendOtp" class="space-y-6">
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Nomor HP</label>
            <input
              id="phone"
              v-model="form.phone"
              type="text"
              required
              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              placeholder="08xxxxxxxxxx"
            />
            <InputError class="mt-2 text-sm text-red-600" :message="form.errors.phone" />
          </div>

          <div>
            <button
              type="submit"
              :disabled="form.processing"
              :class="[
                'w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white',
                'bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800',
                'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                'transition-all duration-200 transform hover:scale-105',
                form.processing ? 'opacity-50 cursor-not-allowed' : ''
              ]"
            >
              <span v-if="form.processing" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mengirim...
              </span>
              <span v-else>Kirim OTP</span>
            </button>
          </div>
        </form>

        <!-- Form OTP -->
        <form v-else @submit.prevent="verifyOtp" class="space-y-6">
          <div class="flex justify-center space-x-3">
            <input
              v-for="(digit, index) in otpDigits"
              :key="index"
              v-model="otpDigits[index]"
              type="text"
              maxlength="1"
              :id="`otp-${index}`"
              :class="[
                'w-14 h-14 text-center border border-gray-300 rounded-lg shadow-sm',
                'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                'text-xl font-semibold transition-colors duration-200',
                form.errors.otp ? 'border-red-300' : 'border-gray-300'
              ]"
              @input="handleOtpInput(index)"
              @keydown.delete="handleOtpDelete(index)"
              @paste="handleOtpPaste"
            />
          </div>

          <!-- Pesan Error OTP -->
          <InputError class="text-center text-sm text-red-600" :message="form.errors.otp" />

          <!-- Countdown dan Resend OTP -->
          <div class="text-center">
            <div v-if="countdown > 0" class="text-sm text-gray-600 mb-4">
              Kode OTP berlaku dalam <span class="font-medium text-blue-600">{{ countdown }}</span> detik
            </div>
            <div v-else class="text-sm text-gray-600 mb-4">
              Kode OTP sudah tidak berlaku
            </div>
            
            <button
              v-if="countdown <= 0"
              type="button"
              @click="resendOtp"
              :disabled="form.processing"
              class="text-blue-600 hover:text-blue-700 font-medium transition-colors duration-200 flex items-center justify-center mx-auto"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Kirim Ulang OTP
            </button>
          </div>

          <!-- Tombol Verifikasi -->
          <div>
            <button
              type="submit"
              :disabled="form.processing || !isOtpComplete"
              :class="[
                'w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white',
                'bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800',
                'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                'transition-all duration-200 transform hover:scale-105',
                (form.processing || !isOtpComplete) ? 'opacity-50 cursor-not-allowed' : ''
              ]"
            >
              <span v-if="form.processing" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memverifikasi...
              </span>
              <span v-else>Verifikasi OTP</span>
            </button>
          </div>
        </form>

        <!-- Tautan ke Login Email -->
        <div class="text-center mt-8 pt-6 border-t border-gray-200">
          <a
            :href="route('login')"
            class="text-sm text-blue-600 hover:text-blue-500 transition-colors duration-200 font-medium flex items-center justify-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Masuk Menggunakan Email
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

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({
  phone: '',
  otp: '',
});

const otpSent = ref(false);
const otpDigits = ref(['', '', '', '']);
const countdown = ref(90);

// Computed property untuk mengecek apakah OTP sudah lengkap
const isOtpComplete = computed(() => {
  return otpDigits.value.every(digit => digit.length === 1);
});

// Fungsi untuk menangani input OTP
const handleOtpInput = (index) => {
  if (otpDigits.value[index].length === 1 && index < 3) {
    document.getElementById(`otp-${index + 1}`).focus();
  }
  if (isOtpComplete.value) {
    form.otp = otpDigits.value.join('');
    verifyOtp();
  }
};

// Fungsi untuk menangani tombol delete
const handleOtpDelete = (index) => {
  if (otpDigits.value[index].length === 0 && index > 0) {
    document.getElementById(`otp-${index - 1}`).focus();
  }
};

// Fungsi untuk menangani paste OTP
const handleOtpPaste = (event) => {
  event.preventDefault();
  const pasteData = event.clipboardData.getData('text').slice(0, 4);
  if (/^\d+$/.test(pasteData)) {
    const digits = pasteData.split('');
    digits.forEach((digit, index) => {
      if (index < 4) {
        otpDigits.value[index] = digit;
      }
    });
    form.otp = otpDigits.value.join('');
    if (isOtpComplete.value) {
      verifyOtp();
    }
  }
};

// Kirim OTP
const sendOtp = () => {
  form.post('/check-phone', {
    onSuccess: () => {
      otpSent.value = true;
      startCountdown();
      // Fokus ke input OTP pertama
      setTimeout(() => document.getElementById('otp-0')?.focus(), 100);
    },
  });
};

// Verifikasi OTP
const verifyOtp = () => {
  form.post('/verify-otp', {
    onSuccess: () => {
      router.visit('/dashboard');
    },
    onError: () => {
      otpDigits.value = ['', '', '', ''];
      setTimeout(() => document.getElementById('otp-0')?.focus(), 100);
    },
  });
};

// Kirim ulang OTP
const resendOtp = () => {
  form.post('/resend-otp', {
    onSuccess: () => {
      countdown.value = 90;
      startCountdown();
      otpDigits.value = ['', '', '', ''];
      setTimeout(() => document.getElementById('otp-0')?.focus(), 100);
    },
  });
};

// Mulai hitung mundur
const startCountdown = () => {
  const interval = setInterval(() => {
    if (countdown.value > 0) {
      countdown.value--;
    } else {
      clearInterval(interval);
    }
  }, 1000);
};

onMounted(() => {
  if (otpSent.value) {
    startCountdown();
  }
});
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