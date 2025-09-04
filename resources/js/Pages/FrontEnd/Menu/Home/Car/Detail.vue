<template>
    <AppLayout title="Kendaraan">
        <Head title="Inspek Baru" />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 relative">
            <div class="space-y-6 bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl md:text-3xl font-bold text-gray-900 text-center flex-grow">
                        Data Detail Kendaraan
                    </h3>
                    <a :href="route('car.create')" class="ml-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 hover:text-indigo-800 transition-colors" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>

                <div
                    v-if="form.errors.form_error"
                    class="mb-4 text-sm text-red-600 bg-red-100 p-3 rounded-md"
                >
                    {{ form.errors.form_error }}
                </div>

                <div class="mb-6">
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

                        <div v-if="isSearching" class="absolute right-3 top-3">
                            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                        </div>

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

                <div v-if="form.car_id && selectedCar" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div v-if="selectedCar.description" class="mb-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Deskripsi:</h3>
                        <div class="prose prose-sm max-w-none text-gray-600" v-html="selectedCar.description"></div>
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
                
                <div v-if="showLightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" @click="closeLightbox">
                    <div class="relative max-w-4xl max-h-full w-full h-full flex items-center justify-center">
                        <button 
                            @click="closeLightbox" 
                            class="absolute top-4 right-4 z-10 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <button 
                            v-if="carImages.length > 1"
                            @click.stop="prevImage" 
                            class="absolute left-4 z-10 text-white bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-70 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        <button 
                            v-if="carImages.length > 1"
                            @click.stop="nextImage" 
                            class="absolute right-4 z-10 text-white bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-70 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <img 
                            :src="getImageSrc(carImages[currentImageIndex])" 
                            :alt="'Car Image ' + (currentImageIndex + 1)"
                            class="max-w-full max-h-full object-contain"
                            @click.stop
                        >

                        <div v-if="carImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white bg-black bg-opacity-50 px-3 py-1 rounded-full text-sm">
                            {{ currentImageIndex + 1 }} / {{ carImages.length }}
                        </div>

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
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    CarDetail: Array,
});

// Inertia form
const form = useForm({
    car_id: null,
    car_name: '',
});

// State autocomplete
const carSearchQuery = ref('');
const showSuggestions = ref(false);
const isSearching = ref(false);
const filteredCars = ref([]);
const selectedCar = ref(null);
const carImages = ref([]);

// Lightbox
const showLightbox = ref(false);
const currentImageIndex = ref(0);

// Format nama mobil
const formatCarName = (car) => {
    if (!car) return '';
    const parts = [];
    if (car.brand?.name) parts.push(car.brand.name);
    if (car.model?.name) parts.push(car.model.name);
    if (car.type?.name) parts.push(car.type.name);
    if (car.cc) parts.push(`${car.cc}cc`);
    if (car.transmission) parts.push(car.transmission);
    if (car.fuel_type) parts.push(car.fuel_type);
    if (car.year) parts.push(car.year.toString());
    if (car.production_period) parts.push(`(${car.production_period})`);
    return parts.join(' ');
};

// Cari mobil
const searchCars = debounce(() => {
    if (!carSearchQuery.value.trim()) {
        filteredCars.value = [];
        showSuggestions.value = false;
        return;
    }
    isSearching.value = true;
    const query = carSearchQuery.value.toLowerCase().trim();
    filteredCars.value = props.CarDetail.filter(car => {
        return formatCarName(car).toLowerCase().includes(query);
    });
    showSuggestions.value = true;
    isSearching.value = false;
}, 300);

// Blur handling
const handleInputBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

// Pilih mobil
const selectCar = async (car) => {
    selectedCar.value = car;
    carSearchQuery.value = formatCarName(car);
    form.car_id = car.id;
    form.car_name = formatCarName(car);
    showSuggestions.value = false;
    await loadCarImages(car.id);
};

// Ambil gambar mobil
const loadCarImages = async (carId) => {
    try {
        const response = await fetch(`/api/cars/${carId}/images`);
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

const getImageSrc = (image) => image?.file_path ? `/storage/${image.file_path}` : '';

// Lightbox control
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

// Keyboard nav
const handleKeydown = (event) => {
    if (!showLightbox.value) return;
    if (event.key === 'Escape') closeLightbox();
    if (event.key === 'ArrowRight') nextImage();
    if (event.key === 'ArrowLeft') prevImage();
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

// Reset car_id kalau user ketik manual
watch(carSearchQuery, (newValue) => {
    if (!selectedCar.value || formatCarName(selectedCar.value) !== newValue) {
        form.car_id = null;
        form.car_name = newValue;
        selectedCar.value = null;
        carImages.value = [];
    }
});
</script>
