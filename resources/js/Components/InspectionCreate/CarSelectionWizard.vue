<!-- resources/js/Components/Inspection/CarSelectionWizard.vue -->
<template>
    <div class="mb-2">
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
                <option disabled value="">Pilih Model</option>
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
                <option disabled value="">Pilih Type</option>
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
                    <option disabled value="">Pilih Kapasitas</option>
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
                    <option disabled value="">Pilih Tahun</option>
                    <option v-for="year in years" :key="year.year" :value="year.year">
                        {{ year.year }}
                    </option>
                </select>
            </div>
        </div>

     <!-- Transmission Selection -->
<div class="mb-4">
  <label class="block text-sm font-medium text-gray-700 mb-2">Transmisi</label>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    <label
      v-for="transmission in transmissions"
      :key="transmission.transmission"
      class="flex items-center justify-center cursor-pointer rounded-xl border p-3 text-sm font-medium transition-all duration-200"
      :class="{
        'border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm':
          selectedTransmission === transmission.transmission,
        'border-gray-300 text-gray-700 hover:border-indigo-300':
          selectedTransmission !== transmission.transmission
      }"
    >
      <input
        type="radio"
        v-model="selectedTransmission"
        :value="transmission.transmission"
        @change="onTransmissionChange"
        class="sr-only"
        :disabled="!selectedYear || loading.transmission"
      />
      {{ formatTransmission(transmission.transmission) }}
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
                <option disabled value="">Pilih Bahan Bakar</option>
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
                <option disbaled value="">Pilih Periode Produksi</option>
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
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

// Define props dan emits
const props = defineProps({
    carDetail: Array // Data mobil dari parent
});

const emit = defineEmits(['car-selected', 'car-images-loaded']);

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

// Loading states
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

// Format functions
const formatCc = (cc) => {
    return (cc / 1000).toFixed(1) + "L";
};

const formatTransmission = (transmission) => {
  const transmissionMap = {
    AT: { category: "AT", label: "Automatic" },
    MT: { category: "MT", label: "Manual" },

    // Semua yang turunan AT
    CVT: { category: "AT", label: "CVT" },
    "e-CVT": { category: "AT", label: "e-CVT" },
    DCT: { category: "AT", label: "DCT" },
    IVT: { category: "AT", label: "IVT" },
    SSG: { category: "AT", label: "SSG" },
    AGS: { category: "AT", label: "AGS" },
    DHT: { category: "AT", label: "DHT" },

    // Turunan MT
    AMT: { category: "MT", label: "AMT" },
  };

  const mapped = transmissionMap[transmission];

  if (!mapped) return transmission; // fallback kalau enum baru ditambah

  // Kalau label = Automatic / Manual → tampilkan cuma kategori
  if (mapped.label === "Automatic" || mapped.label === "Manual") {
    return mapped.category;
  }

  // Kalau turunan → tampilkan kategori + sub jenis
  return `${mapped.category} (${mapped.label})`;
};


// API calls
const fetchWithTimeout = async (url, options = {}) => {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000);
    
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

// Load gambar mobil
const loadCarImages = async (carId) => {
    try {
        const response = await fetch(`/api/cars/${carId}/images`);
        if (response.ok) {
            const data = await response.json();
            const images = Array.isArray(data) ? data : [];
            emit('car-images-loaded', images);
            return images;
        }
        return [];
    } catch (error) {
        console.error('Error loading car images:', error);
        return [];
    }
};

// Ketika mobil terpilih, emit event ke parent
const fetchCarDetail = async (typeId, capacity, year, transmission, fuel, period) => {
    if (!typeId || !capacity || !year || !transmission || !fuel || !period) return;
    
    try {
        const response = await fetchWithTimeout(`/api/car-details/${typeId}/${capacity}/${year}/${transmission}/${fuel}/${period}`);
        selectedCarDetail.value = await response.json();
        
        if (selectedCarDetail.value) {
            // Update form melalui emit
            emit('car-selected', selectedCarDetail.value);
            
            // Load gambar mobil
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

onMounted(() => {
    fetchBrands();
});
</script>