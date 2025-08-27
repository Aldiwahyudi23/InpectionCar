<template>
    <AppLayout>
        <Head title="Inspek Baru" />
        <div class="space-y-6 bg-white rounded-xl shadow-md p-6">
            <div class="mb-4">
                <h3 class="text-xl md:text-3xl font-bold text-gray-900 mb-6 text-center">Buat Inspeksi Baru</h3>
                <p class="text-gray-600">Isi detail kendaraan dan jadwal untuk memulai inspeksi.</p>
            </div>

            <!-- Notifikasi Error -->
            <div v-if="form.errors.form_error" class="mb-4 text-sm text-red-600 bg-red-100 p-3 rounded-md">
                {{ form.errors.form_error }}
            </div>

            <!-- Form Input Plate Number -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nomor Plat Kendaraan
                </label>
                <input
                    v-model="form.plate_number"
                    type="text"
                    placeholder="Contoh: B 1234 ABC"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                >
            </div>

            <!-- Form Input Car Name with Auto-complete -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Mobil
                </label>
                <div class="relative">
                    <input
                        v-model="carSearchQuery"
                        type="text"
                        placeholder="Cari atau ketik nama mobil..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        @input="searchCars"
                        @focus="showSuggestions = true"
                        @blur="handleInputBlur"
                    >
                    
                    <!-- Loading Indicator -->
                    <div v-if="isSearching" class="absolute right-3 top-3">
                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <!-- Search Suggestions Dropdown -->
                    <div 
                        v-if="showSuggestions && filteredCars.length > 0" 
                        class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                    >
                        <div 
                            v-for="car in filteredCars" 
                            :key="car.id"
                            class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                            @mousedown="selectCar(car)"
                        >
                            <div class="font-medium text-gray-900">
                                {{ formatCarName(car) }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ car.year }} • {{ car.cc }}cc • {{ car.transmission }} • {{ car.fuel_type }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Info Mobil Terpilih -->
                <div v-if="form.car_id" class="text-sm text-sky-600 mt-2">
                    ✓ Mobil terpilih: {{ carSearchQuery }}
                </div>
                <div v-else-if="carSearchQuery.trim()" class="text-sm text-indigo-600 mt-2">
                    ℹ Mobil baru akan dibuat: {{ carSearchQuery }}
                </div>
            </div>

            <!-- Select Kategori Inspeksi -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2" for="category">
                    Kategori Inspeksi
                </label>
                <select
                    id="category"
                    v-model="form.category_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    required
                >
                    <option value="" disabled>Pilih Kategori</option>
                    <option v-for="category in Category" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
            </div>

            <!-- Toggle Jadwal -->
            <div class="mb-6 flex items-center justify-between">
                <label for="schedule-toggle" class="text-sm font-medium text-gray-700">Jadwalkan Inspeksi?</label>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="form.is_scheduled" id="schedule-toggle" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-700"></div>
                </label>
            </div>

            <!-- Input Tanggal & Waktu (Muncul Jika Toggle Aktif) -->
            <div v-if="form.is_scheduled" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="schedule-date">
                        Tanggal
                    </label>
                    <input
                        type="date"
                        id="schedule-date"
                        v-model="form.scheduled_at_date"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="schedule-time">
                        Waktu
                    </label>
                    <input
                        type="time"
                        id="schedule-time"
                        v-model="form.scheduled_at_time"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        required
                    >
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="mt-6 flex justify-end">
                <button
                    @click="submitInspection"
                    :disabled="!isFormValid || form.processing"
                    :class="{
                        'px-6 py-3 rounded-md text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed': true,
                        'bg-gradient-to-r from-indigo-700 to-sky-600  border border-transparent text-white hover:bg-blue-700': !form.is_scheduled,
                        'bg-gradient-to-r from-green-700 to-indigo-600  border-transparent text-white hover:bg-green-700': form.is_scheduled
                    }"
                >
                    {{ buttonText }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    CarDetail: Array,
    Category: Array
});

// State form Inertia
const form = useForm({
    plate_number: '',
    car_id: null,
    car_name: '', // Digunakan untuk mobil baru
    category_id: '',
    is_scheduled: false,
    scheduled_at_date: null,
    scheduled_at_time: null,
});

// State untuk autocomplete
const carSearchQuery = ref('');
const showSuggestions = ref(false);
const isSearching = ref(false);
const filteredCars = ref([]);
const selectedCar = ref(null);

// computed properties untuk validasi dan teks tombol
const isFormValid = computed(() => {
    // Validasi dasar
    if (!form.plate_number.trim() || !form.category_id) {
        return false;
    }
    // Validasi untuk mobil baru vs mobil lama
    if (!form.car_id && !carSearchQuery.value.trim()) {
        return false; // Jika tidak ada car_id, car_name harus diisi
    }
    // Validasi jika dijadwalkan
    if (form.is_scheduled) {
        return form.scheduled_at_date && form.scheduled_at_time;
    }
    return true;
});

const buttonText = computed(() => {
    return form.is_scheduled ? 'Buat Jadwal' : 'Mulai Inspeksi';
});

// Format nama mobil
const formatCarName = (car) => {
    if (!car) return '';
    const parts = [];
    if (car.brand?.name) parts.push(car.brand.name);
    if (car.model?.name) parts.push(car.model.name);
    if (car.type?.name) parts.push(car.type.name);
    if (car.year) parts.push(car.year.toString());
    return parts.join(' ');
};

// Search cars dengan debounce
const searchCars = debounce(() => {
    if (!carSearchQuery.value.trim()) {
        filteredCars.value = [];
        showSuggestions.value = false;
        return;
    }
    isSearching.value = true;
    const query = carSearchQuery.value.toLowerCase().trim();
    filteredCars.value = props.CarDetail.filter(car => {
        const carName = formatCarName(car).toLowerCase();
        return carName.includes(query);
    });
    showSuggestions.value = true;
    isSearching.value = false;
}, 300);

// Handle input blur dengan delay
const handleInputBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

// Memilih mobil dari hasil pencarian
const selectCar = (car) => {
    selectedCar.value = car;
    carSearchQuery.value = formatCarName(car);
    form.car_id = car.id;
    form.car_name = ''; // Kosongkan car_name jika mobil dipilih
    showSuggestions.value = false;
};

// Lacak perubahan pada carSearchQuery untuk mereset car_id
watch(carSearchQuery, (newValue) => {
    // Jika user mengetik (bukan memilih dari saran), reset car_id
    if (!selectedCar.value || formatCarName(selectedCar.value) !== newValue) {
        form.car_id = null;
        form.car_name = newValue;
    }
});

// Submit form ke backend
const submitInspection = () => {
    const dataToSend = {
        plate_number: form.plate_number,
        category_id: form.category_id,
        is_scheduled: form.is_scheduled,
    };

    if (form.is_scheduled) {
        dataToSend.scheduled_at = `${form.scheduled_at_date} ${form.scheduled_at_time}`;
    }

    if (form.car_id) {
        dataToSend.car_id = form.car_id;
    } else {
        dataToSend.car_name = carSearchQuery.value;
    }

    form.post(route('inspections.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Tentukan redirect berdasarkan respons dari controller
            if (!dataToSend.is_scheduled) {
                const inspectionId = page.props.flash.newInspectionId;
                if (inspectionId) {
                    window.location.href = route('inspections.start', inspectionId);
                } else {
                    console.error('Tidak ada ID inspeksi baru yang dikembalikan.');
                }
            } else {
                // Untuk jadwal, tidak ada redirect, hanya tampilkan pesan sukses
                console.log('Inspeksi berhasil dijadwalkan.');
            }
        },
        onError: () => {
            // Tampilkan error jika ada
        }
    });
};
</script>
