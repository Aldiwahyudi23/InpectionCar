<template>
    <div class="mb-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Nama Mobil
        </label>
        <div class="relative">
            <input
                v-model="carSearchQuery"
                type="text"
                placeholder="Cari atau ketik nama mobil..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
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
                v-if="showSuggestions"
                class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
            >
                <div v-if="filteredCars.length > 0">
                    <div
                        v-for="car in filteredCars"
                        :key="car.id"
                        class="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                        @mousedown="selectCar(car)"
                    >
                        <div class="font-medium text-gray-900">
                            {{ formatCarName(car) }}
                        </div>
                    </div>
                </div>
                <div v-else class="p-4 text-sm text-gray-500 text-center">
                    Tidak ada data mobil yang cocok. <br>
                    Silakan input manual dengan format: <br>
                    <span class="font-medium text-gray-800">Toyota Avanza 1.5 G AT Bensin 2019</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Car Images Display -->
    <div v-if="selectedCar && carImages.length > 0" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
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
</template>

<script setup>
import { ref, watch, computed} from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    carId: [Number, String],
    carName: String,
    carDetail: Array
});

const emit = defineEmits([
    'update:carId', 
    'update:carName', 
    'carSelected',
    'carImagesLoaded' // TAMBAHKAN INI
]);

// State untuk autocomplete
const carSearchQuery = ref('');
const showSuggestions = ref(false);
const isSearching = ref(false);
const filteredCars = ref([]);
const selectedCar = ref(null);
const carImages = ref([]);

// Methods
const formatCarName = (car) => {
    if (!car) return '';
    const parts = [];
    if (car.brand?.name) parts.push(car.brand.name);
    if (car.model?.name) parts.push(car.model.name);
    if (car.type?.name) parts.push(car.type.name);
    if (car.cc) parts.push((car.cc / 1000).toFixed(1));
    if (car.transmission) parts.push(car.transmission);
    if (car.year) parts.push(car.year.toString());
    if (car.fuel_type) parts.push(car.fuel_type);
    if (car.production_period) parts.push(`(${car.production_period})`);
    return parts.join(' ');
};

const searchCars = debounce(() => {
    if (!carSearchQuery.value.trim()) {
        filteredCars.value = [];
        showSuggestions.value = false;
        return;
    }
    
    isSearching.value = true;
    const query = carSearchQuery.value.toLowerCase().trim();
    filteredCars.value = props.carDetail.filter(car => {
        const carName = formatCarName(car).toLowerCase();
        return carName.includes(query);
    });
    showSuggestions.value = true;
    isSearching.value = false;
}, 300);

const handleInputBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

// Di dalam method selectCar di CarSearchInput.vue
const selectCar = async (car) => {
    selectedCar.value = car;
    carSearchQuery.value = formatCarName(car);
    emit('update:carId', car.id);
    emit('update:carName', formatCarName(car));
    emit('carSelected', car);
    showSuggestions.value = false;
    
    // Load gambar dan emit ke parent
    const images = await loadCarImages(car.id);
    emit('carImagesLoaded', images);
};

// Modifikasi loadCarImages untuk return images
const loadCarImages = async (carId) => {
    try {
        const response = await fetch(`/api/cars/${carId}/images`);
        if (response.ok) {
            const data = await response.json();
            const images = Array.isArray(data) ? data : [];
            carImages.value = images;
            return images; // Return images
        } else {
            carImages.value = [];
            return [];
        }
    } catch (error) {
        console.error('Error loading car images:', error);
        carImages.value = [];
        return [];
    }
};

const getImageSrc = (image) => {
    if (!image || !image.file_path) return '';
    return `/storage/${image.file_path}`;
};

// Watch untuk reset ketika carId berubah dari luar
watch(() => props.carId, (newValue) => {
    if (!newValue) {
        selectedCar.value = null;
        carSearchQuery.value = '';
        carImages.value = [];
    }
});

watch(carSearchQuery, (newValue) => {
    if (!selectedCar.value || formatCarName(selectedCar.value) !== newValue) {
        emit('update:carId', null);
        selectedCar.value = null;
        carImages.value = [];
    }
});
</script>