<template>
    <AppLayout title="Buat Inspeksi Baru">
        <Head title="Inspek Baru" />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="space-y-6 bg-white rounded-xl shadow-md p-6">
                <div class="mb-4">
                    <h3 class="text-xl md:text-3xl font-bold text-gray-900 mb-6 text-center">Buat Inspeksi Baru</h3>
                    <p class="text-gray-600">Isi detail kendaraan dan jadwal untuk memulai inspeksi.</p>
                </div>

                <!-- Notifikasi Error dari Backend -->
                <div v-if="form.errors.form_error" class="mb-4 text-sm text-red-600 bg-red-100 p-3 rounded-md">
                    {{ form.errors.form_error }}
                </div>

                <!-- Form Input Plate Number -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Plat Kendaraan
                    </label>
                    <div class="flex items-center space-x-2">
                        <!-- Kode Wilayah (Huruf) -->
                        <input
                            v-model="plateAreaCode"
                            type="text"
                            placeholder="D"
                            class="w-1/4 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center transition duration-150"
                            @input="handlePlateInput('area')"
                            maxlength="2"
                        >
                        <!-- Nomor Acak (Angka) -->
                        <input
                            v-model="plateNumber"
                            type="tel"
                            pattern="[0-9]*"
                            placeholder="1234"
                            class="w-2/4 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center transition duration-150"
                            @input="handlePlateInput('number')"
                            maxlength="4"
                        >
                        <!-- Huruf Acak (Huruf) -->
                        <input
                            v-model="plateSuffix"
                            type="text"
                            placeholder="ABC"
                            class="w-1/4 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center transition duration-150"
                            @input="handlePlateInput('suffix')"
                            maxlength="3"
                        >
                    </div>

                    <!-- Pesan Validasi Plat Nomor -->
                    <div v-if="inspectionValidationMessage" class="mt-2 text-sm text-red-600">
                        {{ inspectionValidationMessage }}
                    </div>
                    
                    <!-- Pesan Riwayat Inspeksi -->
                    <div v-if="inspectionCountMessage" class="mt-2 text-sm text-green-600">
                        {{ inspectionCountMessage }}
                    </div>
                    <!-- Tambahkan di bawah input plat nomor -->
                    <div v-if="!isPlateValid" class="mt-2 text-sm text-yellow-600">
                        ⚠ Kolom kode wilayah dan nomor plat harus diisi
                    </div>
                </div>

                <!-- Form Pemilihan Mobil dengan Dropdown Terkait -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Mobil
                    </label>
                    
                    <!-- Brand Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                        <select 
                            v-model="selectedBrand"
                            @change="onBrandChange"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            :disabled="loading.brand"
                        >
                            <option value="">Pilih Brand</option>
                            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                {{ brand.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Model Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                        <select 
                            v-model="selectedModel"
                            @change="onModelChange"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            :disabled="!selectedBrand || loading.model"
                        >
                            <option value="">Pilih Model</option>
                            <option v-for="model in models" :key="model.id" :value="model.id">
                                {{ model.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Type Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type/Varian</label>
                        <select 
                            v-model="selectedType"
                            @change="onTypeChange"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            :disabled="!selectedModel || loading.type"
                        >
                            <option value="">Pilih Type</option>
                            <option v-for="type in types" :key="type.id" :value="type.id">
                                {{ type.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Capacity & Year Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas Mesin</label>
                            <select 
                                v-model="selectedCapacity"
                                @change="onCapacityChange"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                :disabled="!selectedType || loading.capacity"
                            >
                                <option value="">Pilih Kapasitas</option>
                                <option v-for="capacity in capacities" :key="capacity.cc" :value="capacity.cc">
                                    {{ capacity.display }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                            <select 
                                v-model="selectedYear"
                                @change="onYearChange"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                :disabled="!selectedCapacity || loading.year"
                            >
                                <option value="">Pilih Tahun</option>
                                <option v-for="year in years" :key="year.year" :value="year.year">
                                    {{ year.year }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Transmission Selection (Styled Radio Buttons) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transmisi</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label v-for="transmission in transmissions" :key="transmission.transmission" 
                                   class="relative flex cursor-pointer rounded-lg border-2 border-gray-200 p-3 focus:outline-none transition-all duration-200 hover:border-indigo-300"
                                   :class="{
                                       'border-indigo-500 bg-indigo-50': selectedTransmission === transmission.transmission,
                                       'border-gray-200': selectedTransmission !== transmission.transmission
                                   }">
                                <input 
                                    type="radio" 
                                    v-model="selectedTransmission" 
                                    :value="transmission.transmission"
                                    @change="onTransmissionChange"
                                    class="sr-only"
                                    :disabled="!selectedYear || loading.transmission"
                                >
                                <div class="flex w-full items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900">
                                                {{ formatTransmission(transmission.transmission) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-indigo-600">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="12" fill="#fff" stroke="currentColor" stroke-width="2" />
                                            <circle v-if="selectedTransmission === transmission.transmission" cx="12" cy="12" r="6" fill="currentColor" />
                                        </svg>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Fuel Type Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bahan Bakar</label>
                        <select 
                            v-model="selectedFuel"
                            @change="onFuelChange"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            :disabled="!selectedTransmission || loading.fuel"
                        >
                            <option value="">Pilih Bahan Bakar</option>
                            <option v-for="fuel in fuels" :key="fuel.fuel_type" :value="fuel.fuel_type">
                                {{ fuel.fuel_type }}
                            </option>
                        </select>
                    </div>

                    <!-- Production Period -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Periode Produksi</label>
                        <select 
                            v-model="selectedPeriod"
                            @change="onPeriodChange"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            :disabled="!selectedFuel || loading.period"
                        >
                            <option value="">Pilih Periode Produksi</option>
                            <option v-for="period in periods" :key="period.production_period" :value="period.production_period">
                                {{ period.production_period }}
                            </option>
                        </select>
                    </div>

                    <!-- Display Selected Car -->
                    <div v-if="selectedCarDetail" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <h4 class="font-medium text-green-800 mb-2">Mobil Terpilih:</h4>
                        <p class="text-green-700">{{ selectedCarDetail.brand.name }} {{ selectedCarDetail.model.name }} {{ selectedCarDetail.type.name }} {{ formatCc(selectedCarDetail.cc) }} {{ formatTransmission(selectedCarDetail.transmission) }} {{ selectedCarDetail.fuel_type }} {{ selectedCarDetail.year }} ({{ selectedCarDetail.production_period }})</p>
                    </div>

                    <!-- Loading Indicator -->
                    <div v-if="isLoading" class="mt-2 text-sm text-gray-500 flex items-center">
                        <svg class="animate-spin h-4 w-4 mr-2 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memuat data...
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
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
                        <input type="checkbox" v-model="form.is_scheduled" id="schedule-toggle" class="sr-only peer" :disabled="hasActiveInspections">
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            required
                        >
                    </div>

                    <!-- SELECT INSPECTOR_ID (Hanya untuk Admin & Coordinator) -->
                    <div v-if="roles.includes('Admin') || roles.includes('coordinator')" class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="inspector">
                            Pilih Inspektor
                        </label>
                        <select
                            id="inspector"
                            v-model="form.inspector_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            required
                        >
                            <option value="" disabled>Pilih Inspektor</option>
                            <option v-for="inspector in filteredInspectors" :key="inspector.id" :value="inspector.user.id">
                                {{ inspector.user.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="mt-6 flex justify-end">
                    <button
                        @click="submitInspection"
                        :disabled="!isFormValid || form.processing || isPlateInvalid"
                        :class="{
                            'px-6 py-2 rounded-md text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed': true,
                            'bg-gradient-to-r from-indigo-700 to-sky-600  border border-transparent text-white hover:bg-blue-700': !form.is_scheduled,
                            'bg-gradient-to-r from-green-700 to-indigo-600  border-transparent text-white hover:bg-green-700': form.is_scheduled
                        }"
                    >
                        {{ buttonText }}
                    </button>
                </div>

                <!-- Bagian ini hanya tampil jika car_id ada -->
                <div v-if="form.car_id && selectedCarDetail" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div v-if="selectedCarDetail.description" class="mb-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Deskripsi:</h3>
                        <div class="prose prose-sm max-w-none text-gray-600" v-html="selectedCarDetail.description"></div>
                    </div>

                    <div v-if="carImages.length > 0">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Gambar Mobil:</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            <div
                                v-for="(image, index) in carImages"
                                :key="image.id || index"
                                class="relative group cursor-pointer"
                                @click="openLightbox(index)"
                            >
                                <img
                                    :src="getImageSrc(image)"
                                    :alt="image.name || 'Car Image'"
                                    class="w-full h-24 object-cover rounded-lg border border-gray-200 transition-transform duration-200 group-hover:scale-105"
                                >
                                <div
                                    v-if="image.note"
                                    class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center p-2"
                                >
                                    <p class="text-white text-xs text-center">{{ image.note }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500 text-sm">
                        Tidak ada gambar tersedia untuk mobil ini
                    </div>
                </div>
                
                <!-- Lightbox Modal -->
                <div v-if="showLightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" @click="closeLightbox">
                    <div class="relative max-w-4xl max-h-full w-full h-full flex items-center justify-center">
                        <!-- Close Button -->
                        <button
                            @click="closeLightbox"
                            class="absolute top-4 right-4 z-10 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>

                        <!-- Navigation Arrows -->
                        <button
                            v-if="carImages.length > 1"
                            @click.stop="prevImage"
                            class="absolute left-4 z-10 text-white bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-70 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <button
                            v-if="carImages.length > 1"
                            @click.stop="nextImage"
                            class="absolute right-4 z-10 text-white bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-70 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>

                        <!-- Image Display -->
                        <img
                            :src="getImageSrc(carImages[currentImageIndex])"
                            :alt="'Car Image ' + (currentImageIndex + 1)"
                            class="max-w-full max-h-full object-contain"
                            @click.stop
                        >

                        <!-- Image Counter -->
                        <div v-if="carImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white bg-black bg-opacity-50 px-3 py-1 rounded-full text-sm">
                            {{ currentImageIndex + 1 }} / {{ carImages.length }}
                        </div>

                        <!-- Image Note -->
                        <div v-if="carImages[currentImageIndex]?.note" class="absolute bottom-4 left-4 text-white bg-black bg-opacity-50 px-3 py-1 rounded text-sm max-w-md">
                            {{ carImages[currentImageIndex].note }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, watchEffect } from 'vue';
import { usePage, useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    CarDetail: Array,
    Category: Array,
    team: Array,
    inspection: Array,
    activeInspections: Boolean,
});

// State form Inertia
const form = useForm({
    plate_number: '',
    car_id: null,
    car_name: '',
    category_id: '',
    is_scheduled: false,
    scheduled_at_date: null,
    scheduled_at_time: null,
    inspector_id: null,
});

const page = usePage();
const user = page.props.global.user;
const region = page.props.global.region;
const roles = page.props.global.has_roles;

const formatCc = (cc) => {
  return (cc / 1000).toFixed(1) + "L";
}

// State untuk input plat nomor
const plateAreaCode = ref('');
const plateNumber = ref('');
const plateSuffix = ref('');

// State untuk dropdown pemilihan mobil
const selectedBrand = ref('');
const selectedModel = ref('');
const selectedType = ref('');
const selectedCapacity = ref('');
const selectedYear = ref('');
const selectedTransmission = ref('');
const selectedFuel = ref('');
const selectedPeriod = ref('');
const selectedCarDetail = ref(null);

// Data untuk dropdown
const brands = ref([]);
const models = ref([]);
const types = ref([]);
const capacities = ref([]);
const years = ref([]);
const transmissions = ref([]);
const fuels = ref([]);
const periods = ref([]);

// Loading states dengan timeout
const loading = ref({
    brand: false,
    model: false,
    type: false,
    capacity: false,
    year: false,
    transmission: false,
    fuel: false,
    period: false
});

const isLoading = computed(() => {
    return Object.values(loading.value).some(status => status);
});

// State lainnya
const carImages = ref([]);
const inspectionValidationMessage = ref('');
const isPlateInvalid = ref(false);
const inspectionCountMessage = ref('');
const showLightbox = ref(false);
const currentImageIndex = ref(0);

// Computed properties
const hasActiveInspections = computed(() => props.activeInspections);

const isFormValid = computed(() => {
    // Validasi spesifik untuk setiap bagian plat nomor
    const isPlateAreaValid = plateAreaCode.value.trim().length >= 1; // Minimal 1 huruf
    const isPlateNumberValid = plateNumber.value.trim().length >= 1; // Minimal 3 angka
    // const isPlateSuffixValid = plateSuffix.value.trim().length >= 1; // Minimal 1 huruf (opsional)
    
    const isPlateValid = isPlateAreaValid && isPlateNumberValid;
    
    if (!isPlateValid) return false;
    if (!form.plate_number.trim() || !form.category_id) return false;
    if (!selectedCarDetail.value) return false;
    if (form.is_scheduled && (!form.scheduled_at_date || !form.scheduled_at_time)) return false;
    if ((roles.includes('Admin') || roles.includes('coordinator')) && !form.inspector_id) return false;
    
    return true;
});

const buttonText = computed(() => {
    return form.is_scheduled ? 'Buat Jadwal' : 'Mulai Inspeksi';
});

const filteredInspectors = computed(() => {
    const activeTeam = props.team.filter(member => member.status === 'active');
    const userIsAdmin = roles.includes('Admin');
    const userIsCoordinator = roles.includes('coordinator');

    if (userIsAdmin) return activeTeam;
    if (userIsCoordinator) return activeTeam.filter(member => member.region_id === region.id);
    return [];
});

// Format functions
const formatTransmission = (transmission) => {
    const transmissionMap = {
        'AT': 'Automatic',
        'MT': 'Manual',
        'CVT': 'CVT',
        'AMT': 'AMT'
    };
    return transmissionMap[transmission] || transmission;
};

// Optimized API calls dengan timeout
const fetchWithTimeout = async (url, options = {}) => {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout
    
    try {
        const response = await fetch(url, {
            ...options,
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        return response;
    } catch (error) {
        clearTimeout(timeoutId);
        throw error;
    }
};

const fetchBrands = async () => {
    loading.value.brand = true;
    try {
        const response = await fetchWithTimeout('/api/car-brands');
        brands.value = await response.json();
    } catch (error) {
        console.error('Error fetching brands:', error);
    } finally {
        loading.value.brand = false;
    }
};

const fetchModels = async (brandId) => {
    if (!brandId) return;
    loading.value.model = true;
    try {
        const response = await fetchWithTimeout(`/api/car-models/${brandId}`);
        models.value = await response.json();
    } catch (error) {
        console.error('Error fetching models:', error);
    } finally {
        loading.value.model = false;
    }
};

const fetchTypes = async (modelId) => {
    if (!modelId) return;
    loading.value.type = true;
    try {
        const response = await fetchWithTimeout(`/api/car-types/${modelId}`);
        types.value = await response.json();
    } catch (error) {
        console.error('Error fetching types:', error);
    } finally {
        loading.value.type = false;
    }
};

const fetchCapacities = async (typeId) => {
    if (!typeId) return;
    loading.value.capacity = true;
    try {
        const response = await fetchWithTimeout(`/api/car-capacities/${typeId}`);
        capacities.value = await response.json();
    } catch (error) {
        console.error('Error fetching capacities:', error);
    } finally {
        loading.value.capacity = false;
    }
};

const fetchYears = async (typeId, capacity) => {
    if (!typeId || !capacity) return;
    loading.value.year = true;
    try {
        const response = await fetchWithTimeout(`/api/car-years/${typeId}/${capacity}`);
        years.value = await response.json();
    } catch (error) {
        console.error('Error fetching years:', error);
    } finally {
        loading.value.year = false;
    }
};

const fetchTransmissions = async (typeId, capacity, year) => {
    if (!typeId || !capacity || !year) return;
    loading.value.transmission = true;
    try {
        const response = await fetchWithTimeout(`/api/car-transmissions/${typeId}/${capacity}/${year}`);
        transmissions.value = await response.json();
    } catch (error) {
        console.error('Error fetching transmissions:', error);
    } finally {
        loading.value.transmission = false;
    }
};

const fetchFuels = async (typeId, capacity, year, transmission) => {
    if (!typeId || !capacity || !year || !transmission) return;
    loading.value.fuel = true;
    try {
        const response = await fetchWithTimeout(`/api/car-fuels/${typeId}/${capacity}/${year}/${transmission}`);
        fuels.value = await response.json();
    } catch (error) {
        console.error('Error fetching fuels:', error);
    } finally {
        loading.value.fuel = false;
    }
};

const fetchPeriods = async (typeId, capacity, year, transmission, fuel) => {
    if (!typeId || !capacity || !year || !transmission || !fuel) return;
    loading.value.period = true;
    try {
        const response = await fetchWithTimeout(`/api/car-periods/${typeId}/${capacity}/${year}/${transmission}/${fuel}`);
        periods.value = await response.json();
    } catch (error) {
        console.error('Error fetching periods:', error);
    } finally {
        loading.value.period = false;
    }
};

const fetchCarDetail = async (typeId, capacity, year, transmission, fuel, period) => {
    if (!typeId || !capacity || !year || !transmission || !fuel || !period) return;
    
    try {
        const response = await fetchWithTimeout(`/api/car-details/${typeId}/${capacity}/${year}/${transmission}/${fuel}/${period}`);
        selectedCarDetail.value = await response.json();
        
        if (selectedCarDetail.value) {
            form.car_id = selectedCarDetail.value.id;
            form.car_name = `${selectedCarDetail.value.brand.name} ${selectedCarDetail.value.model.name} ${selectedCarDetail.value.type.name} ${formatCc(selectedCarDetail.value.cc)} ${formatTransmission(selectedCarDetail.value.transmission)} ${selectedCarDetail.value.fuel_type} ${selectedCarDetail.value.year} (${selectedCarDetail.value.production_period})`;
            await loadCarImages(selectedCarDetail.value.id);
        }
    } catch (error) {
        console.error('Error fetching car detail:', error);
    }
};

// Event handlers
const onBrandChange = () => {
    resetSelections(['model', 'type', 'capacity', 'year', 'transmission', 'fuel', 'period']);
    if (selectedBrand.value) fetchModels(selectedBrand.value);
};

const onModelChange = () => {
    resetSelections(['type', 'capacity', 'year', 'transmission', 'fuel', 'period']);
    if (selectedModel.value) fetchTypes(selectedModel.value);
};

const onTypeChange = () => {
    resetSelections(['capacity', 'year', 'transmission', 'fuel', 'period']);
    if (selectedType.value) fetchCapacities(selectedType.value);
};

const onCapacityChange = () => {
    resetSelections(['year', 'transmission', 'fuel', 'period']);
    if (selectedType.value && selectedCapacity.value) fetchYears(selectedType.value, selectedCapacity.value);
};

const onYearChange = () => {
    resetSelections(['transmission', 'fuel', 'period']);
    if (selectedType.value && selectedCapacity.value && selectedYear.value) fetchTransmissions(selectedType.value, selectedCapacity.value, selectedYear.value);
};

const onTransmissionChange = () => {
    resetSelections(['fuel', 'period']);
    if (selectedType.value && selectedCapacity.value && selectedYear.value && selectedTransmission.value) fetchFuels(selectedType.value, selectedCapacity.value, selectedYear.value, selectedTransmission.value);
};

const onFuelChange = () => {
    resetSelections(['period']);
    if (selectedType.value && selectedCapacity.value && selectedYear.value && selectedTransmission.value && selectedFuel.value) fetchPeriods(selectedType.value, selectedCapacity.value, selectedYear.value, selectedTransmission.value, selectedFuel.value);
};

const onPeriodChange = () => {
    selectedCarDetail.value = null;
    if (selectedType.value && selectedCapacity.value && selectedYear.value && selectedTransmission.value && selectedFuel.value && selectedPeriod.value) {
        fetchCarDetail(selectedType.value, selectedCapacity.value, selectedYear.value, selectedTransmission.value, selectedFuel.value, selectedPeriod.value);
    }
};

// Helper function untuk reset selections
const resetSelections = (fields) => {
    fields.forEach(field => {
        if (field === 'model') selectedModel.value = '';
        if (field === 'type') selectedType.value = '';
        if (field === 'capacity') selectedCapacity.value = '';
        if (field === 'year') selectedYear.value = '';
        if (field === 'transmission') selectedTransmission.value = '';
        if (field === 'fuel') selectedFuel.value = '';
        if (field === 'period') selectedPeriod.value = '';
    });
    selectedCarDetail.value = null;
    
    // Reset data arrays
    if (fields.includes('model')) models.value = [];
    if (fields.includes('type')) types.value = [];
    if (fields.includes('capacity')) capacities.value = [];
    if (fields.includes('year')) years.value = [];
    if (fields.includes('transmission')) transmissions.value = [];
    if (fields.includes('fuel')) fuels.value = [];
    if (fields.includes('period')) periods.value = [];
};

// Plate number logic
const combinePlateNumber = () => {
    form.plate_number = `${plateAreaCode.value}${plateNumber.value}${plateSuffix.value}`.toUpperCase();
};


const handlePlateInput = (type) => {
    if (type === 'area') {
        plateAreaCode.value = plateAreaCode.value.toUpperCase().replace(/[^A-Z]/g, '');
    } else if (type === 'number') {
        plateNumber.value = plateNumber.value.replace(/[^0-9]/g, '');
    } else if (type === 'suffix') {
        plateSuffix.value = plateSuffix.value.toUpperCase().replace(/[^A-Z]/g, '');
    }
    combinePlateNumber();
};

const isPlateValid = computed(() => {
    return plateAreaCode.value.trim().length > 0 && 
           plateNumber.value.trim().length > 0;
});

// Image functions
const loadCarImages = async (carId) => {
    try {
        const response = await fetchWithTimeout(`/api/cars/${carId}/images`);
        if (response.ok) {
            const data = await response.json();
            carImages.value = Array.isArray(data) ? data : [];
        } else {
            carImages.value = [];
        }
    } catch (error) {
        console.error('Error loading car images:', error);
        carImages.value = [];
    }
};

const getImageSrc = (image) => {
    return image?.file_path ? `/storage/${image.file_path}` : '';
};

// Lightbox functions
const openLightbox = (index) => {
    currentImageIndex.value = index;
    showLightbox.value = true;
    document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
    showLightbox.value = false;
    document.body.style.overflow = '';
};

const nextImage = () => {
    currentImageIndex.value = (currentImageIndex.value + 1) % carImages.value.length;
};

const prevImage = () => {
    currentImageIndex.value = (currentImageIndex.value - 1 + carImages.value.length) % carImages.value.length;
};

const handleKeydown = (event) => {
    if (!showLightbox.value) return;
    switch (event.key) {
        case 'Escape': closeLightbox(); break;
        case 'ArrowRight': nextImage(); break;
        case 'ArrowLeft': prevImage(); break;
    }
};

// Mounted
onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    fetchBrands();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

// Watch effects
watchEffect(() => {
    const userIsAdminOrCoordinator = roles.includes('Admin') || roles.includes('coordinator');
    if (!userIsAdminOrCoordinator) {
        form.inspector_id = user?.id || null;
    }
});

watchEffect(() => {
    if (hasActiveInspections.value) {
        form.is_scheduled = true;
    }
});

// Plate number validation
watch(() => form.plate_number, (newPlateNumber) => {
    inspectionValidationMessage.value = '';
    isPlateInvalid.value = false;
    inspectionCountMessage.value = '';
    // form.car_id = null;
    // form.car_name = '';

    if (newPlateNumber.length >= 6) {
        const existingInspections = props.inspection.filter(i => i.plate_number === newPlateNumber);
        
        if (existingInspections.length > 0) {
            const blockingStatuses = ['draft', 'in_progress', 'pending', 'pending_review', 'revision'];
            const blockingInspection = existingInspections.find(i => blockingStatuses.includes(i.status));

            if (blockingInspection) {
                // Found a blocking inspection
                isPlateInvalid.value = true;
                inspectionValidationMessage.value = `Nomor plat ini sedang dalam proses inspeksi dengan status: ${blockingInspection.status.replace(/_/g, ' ').toUpperCase()}. Silakan selesaikan inspeksi tersebut terlebih dahulu.`;
            } else {
                // No blocking inspections, so find the latest completed one
                const completedInspections = existingInspections.filter(i => ['approved', 'rejected', 'completed', 'cancelled'].includes(i.status));
                
                if (completedInspections.length > 0) {
                    // Sort by creation date to get the most recent one
                    completedInspections.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    const latestInspection = completedInspections[0];

                    // Pre-fill the form with data from the latest inspection
                    form.car_id = latestInspection.car_id;
                    form.car_name = latestInspection.car_name;
                    carSearchQuery.value = latestInspection.car_name;
                    selectCar(latestInspection.car); // Re-use selectCar logic

                    inspectionCountMessage.value = `Nomor plat ini sudah pernah diperiksa ${completedInspections.length} kali sebelumnya.`;
                }
            }
        }
    }
});


// Submit form ke backend
const submitInspection = () => {
    const dataToSend = {
        plate_number: form.plate_number,
        car_id: form.car_id,
        car_name: form.car_name,
        category_id: form.category_id,
        is_scheduled: form.is_scheduled,
        inspector_id: form.inspector_id,
    };

    if (form.is_scheduled) {
        dataToSend.scheduled_at = `${form.scheduled_at_date} ${form.scheduled_at_time}`;
    }

    form.post(route('inspections.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Tentukan redirect berdasarkan respons dari controller
           
        },
        onError: () => {
            // Tampilkan error jika ada
        }
    });
};
</script>

<style scoped>
/* Custom styles for lightbox */
.fixed {
    position: fixed;
}
.inset-0 {
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}
.z-50 {
    z-index: 50;
}
.object-contain {
    object-fit: contain;
}
.transition-transform {
    transition-property: transform;
}
.duration-200 {
    transition-duration: 200ms;
}
.group-hover\:scale-105:hover {
    transform: scale(1.05);
}
</style>
