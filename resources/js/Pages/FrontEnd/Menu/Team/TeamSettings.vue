<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-100 p-4 flex items-center justify-center font-inter antialiased">
      <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl p-6 md:p-8">
        
        <!-- Bagian Profil Pengguna -->
        <section class="profile-card">
          <div class="profile-photo-container">
            <img 
              :src="regionTeam.user.profile_photo_url || 'https://placehold.co/150x150/e5e7eb/6b7280?text=Foto'" 
              alt="Foto Profil Pengguna"
              class="profile-photo"
            >
          </div>
          <div class="flex-1 text-center sm:text-left">
            <h1 class="text-2xl font-bold text-gray-900 mb-0.5">{{ regionTeam.user.name }}</h1>
            <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-start text-gray-500 text-sm space-y-1 sm:space-y-0 sm:space-x-2">
              <span>{{ regionTeam.user.email }} | {{ regionTeam.user.numberPhone }}</span>
              <span v-if="regionTeam.status === 'active'" class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
              <span v-else-if="regionTeam.status === 'inactive'" class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Tidak Aktif</span>
              <span v-else class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Dibekukan</span>
            </div>
          </div>
        </section>
  
        <!-- Formulir Pengaturan -->
        <form @submit.prevent="submit" class="space-y-4">
          <h2 class="text-xl font-bold text-gray-800 text-center mb-2">Pengaturan Anggota Tim</h2>
          
          <input type="hidden" v-model="form.user_id" />
  
          <!-- Bagian Form -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Select Status -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select 
                id="status" 
                v-model="form.status" 
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all duration-200"
                :disabled="isInputDisabled"
                required
              >
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
                <option disabled value="paused">Dibekukan</option>
              </select>
            </div>
  
            <div class="hidden md:block"></div>
          </div>
          
          <!-- Section Tagihan Inspector -->
          <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Tagihan Inspector</h3>
            <p class="text-xs text-gray-500 mb-4 italic">
              *Harga **Self Submission** adalah tagihan jika inspektur mendapatkan pelanggan sendiri. **External Submission** adalah tagihan jika jadwal inspeksi dibuat oleh Koordinator.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              
              <!-- Harga (Self Submission) -->
              <div>
                <label for="inspection_price_self" class="block text-sm font-medium text-gray-700 mb-1">Harga (Self Submission)</label>
                <div class="mt-1 relative rounded-lg shadow-sm">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">Rp</span>
                  </div>
                  <input 
                    type="text" 
                    id="inspection_price_self" 
                    v-model="formattedPriceSelf"
                    class="block w-full pl-9 pr-3 py-2 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                    placeholder="0"
                    :disabled="isInputDisabled"
                    required
                  />
                </div>
              </div>
              
              <!-- Harga (External Submission) -->
              <div>
                <label for="inspection_price_external" class="block text-sm font-medium text-gray-700 mb-1">Harga (External Submission)</label>
                <div class="mt-1 relative rounded-lg shadow-sm">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">Rp</span>
                  </div>
                  <input 
                    type="text" 
                    id="inspection_price_external" 
                    v-model="formattedPriceExternal"
                    class="block w-full pl-9 pr-3 py-2 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                    placeholder="0"
                    :disabled="isInputDisabled"
                    required
                  />
                </div>
              </div>
            </div>
          </div>
  
          <div class="flex justify-end pt-4">
            <button
              type="submit"
              :disabled="isButtonDisabled"
              class="w-full sm:w-auto px-6 py-2 rounded-lg font-semibold transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white"
            >
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>{{ buttonText }}</span>
            </button>
          </div>
        </form>
        
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

const props = defineProps({
  regionTeam: Object,
  // Menambahkan prop untuk data pengguna yang sedang login
  auth: Object,
});

// Simpan data awal saat komponen dimuat
const initialFormState = ref(null);

const form = useForm({
  _method: 'put',
  user_id: props.regionTeam.user.id,
  status: props.regionTeam.status,
  settings: props.regionTeam.settings || {
    inspection_price_self: 0,
    inspection_price_external: 0,
  },
});

onMounted(() => {
  // Simpan data awal dari props setelah komponen dimuat
  initialFormState.value = {
    status: props.regionTeam.status,
    settings: {
      inspection_price_self: props.regionTeam.settings?.inspection_price_self || 0,
      inspection_price_external: props.regionTeam.settings?.inspection_price_external || 0,
    },
  };
});

const formatPrice = (value) => {
  if (!value) return '';
  const number = typeof value === 'string' ? parseFloat(value.replace(/\./g, '').replace(',', '.')) : value;
  if (isNaN(number)) return value;
  return new Intl.NumberFormat('id-ID').format(number);
};

const parsePrice = (value) => {
  const parsedValue = parseFloat(value.replace(/\./g, '').replace(',', '.'));
  return isNaN(parsedValue) ? 0 : parsedValue;
};

const formattedPriceSelf = computed({
  get: () => formatPrice(form.settings.inspection_price_self),
  set: (value) => {
    form.settings.inspection_price_self = parsePrice(value);
  },
});

const formattedPriceExternal = computed({
  get: () => formatPrice(form.settings.inspection_price_external),
  set: (value) => {
    form.settings.inspection_price_external = parsePrice(value);
  },
});

// Logika untuk mendeteksi jika halaman ini milik pengguna yang sedang login
const isOwnProfile = computed(() => {
  // Pastikan props.auth dan props.auth.user ada
  return props.auth?.user?.id === props.regionTeam.user.id;
});

// Properti terkomputasi untuk menonaktifkan input saat status 'paused' atau halaman ini milik pengguna sendiri
const isInputDisabled = computed(() => {
  return form.status === 'paused' || isOwnProfile.value;
});

// Menggunakan JSON.stringify untuk perbandingan mendalam
const isFormChanged = computed(() => {
  if (!initialFormState.value) return false;

  const currentFormState = {
    status: form.status,
    settings: {
      inspection_price_self: form.settings.inspection_price_self,
      inspection_price_external: form.settings.inspection_price_external,
    },
  };

  return JSON.stringify(currentFormState) !== JSON.stringify(initialFormState.value);
});

const isButtonDisabled = computed(() => {
  return form.status === 'paused' || !isFormChanged.value || isOwnProfile.value;
});

const buttonText = computed(() => {
  if (isOwnProfile.value) {
    return 'Tidak Dapat Mengubah Pengaturan Sendiri';
  }

  if (form.status === 'paused') {
    return 'Akun di team sedang di bekukan';
  }

  const originalSettings = props.regionTeam.settings;
  const hasNoPriceData = !originalSettings || (originalSettings.inspection_price_self === 0 && originalSettings.inspection_price_external === 0);

  if (!isFormChanged.value) {
    return hasNoPriceData ? 'Tambahkan Perjanjian Harga' : 'Persetujuan Harga';
  }

  return 'Perbaharui Perjanjian Harga';
});

const submit = () => {
  form.post(`/team-settings/${props.regionTeam.id}`, {
    onSuccess: () => {
      console.log('Pengaturan berhasil disimpan!');
    },
    onError: (errors) => {
      console.error('Ada kesalahan saat menyimpan:', errors);
    },
  });
};
</script>

<style scoped>
/* Menambahkan gaya kustom untuk profil dan form */
.profile-card {
  @apply flex flex-col items-center space-y-4 pb-6 border-b border-gray-200 mb-6;
}

@media (min-width: 640px) {
  .profile-card {
    @apply flex-row items-start space-y-0 space-x-6;
  }
}

.profile-photo-container {
  @apply flex-shrink-0;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  transition: all 0.3s ease-in-out;
}

.profile-photo-container:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.profile-photo {
  @apply w-28 h-28 md:w-32 md:h-32 rounded-full object-cover shadow-lg;
  border: 4px solid white;
}

/* Transisi halus untuk input saat fokus */
input[type="text"]:focus, select:focus {
  transition: all 0.2s ease-in-out;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

/* Efek hover pada tombol */
button[type="submit"]:hover {
  transform: translateY(-2px);
}

/* Transisi pada tombol */
button[type="submit"] {
  transition: all 0.2s ease-in-out;
}
</style>
