<template>
    <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100">
        <div class="bg-indigo-50 px-6 py-2 border-b border-indigo-100">
            <h2 class="text-xl font-semibold text-indigo-700">Detail Kendaraan</h2>
        </div>

        <div v-if="inspection" class="p-4 space-y-4">
            <!-- Form Input Plate Number -->
            <div class="space-y-2 pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                <label class="block text-sm font-medium text-gray-700">
                    Nomor Plat Kendaraan
                </label>
                <input
                    v-model="form.plate_number"
                    type="text"
                    placeholder="Contoh: B 1234 ABC"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
                    @input="updateVehicleData"
                >
            </div>

            <!-- Form Input Car Name with Auto-complete -->
            <div class="space-y-2 pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                <label class="block text-sm font-medium text-gray-700">
                    Nama Mobil
                </label>
                <div class="relative">
                    <input
                        v-model="carSearchQuery"
                        type="text"
                        placeholder="Cari atau ketik nama mobil..."
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
                        @input="searchCars"
                        @focus="showSuggestions = true"
                        @blur="handleInputBlur"
                    >
                    
                    <div v-if="isSearching" class="absolute right-3 top-3">
                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
                        <!-- Pesan jika tidak ada hasil -->
                        <div v-else class="p-4 text-sm text-gray-500 text-center">
                            Tidak ada data mobil yang cocok. <br>
                            Silakan input manual dengan format: <br>
                            <span class="font-medium text-gray-800">Toyota Avanza 1.5 G AT Bensin 2019</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" v-model="form.car_id">
            </div>

            <!-- Bagian ini hanya tampil jika car_id ada -->
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

            <PrimaryButton
                type="button"
                @click="updateVehicleDetails"
                class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ form.processing ? 'Mengirim...' : 'Perbarui Detail Kendaraan' }}</span>
                <ActionMessage :on="form.recentlySuccessful" class="me-3 text-sm text-green-600 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Tersimpan.
                </ActionMessage>
            </PrimaryButton>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import PrimaryButton from '../PrimaryButton.vue';
import ActionMessage from '../ActionMessage.vue';

const props = defineProps({
    inspection: {
        type: Object,
        default: null
    },
    CarDetail: Array
});

const emit = defineEmits(['update-vehicle']);

const form = useForm({
    plate_number: props.inspection?.plate_number || '',
    car_id: props.inspection?.car_id || null,
    car_name: props.inspection?.car?.name || '' // Mengambil nama dari relasi jika ada
});

// State
const carSearchQuery = ref(form.car_name);
const showSuggestions = ref(false);
const isSearching = ref(false);
const filteredCars = ref([]);
const selectedCar = ref(null);
const carImages = ref([]);

// State untuk lightbox
const showLightbox = ref(false);
const currentImageIndex = ref(0);

// --- Initial Setup ---
onMounted(() => {
    // Sinkronisasi data awal
    if (props.inspection?.car_id && props.CarDetail?.length > 0) {
        const car = props.CarDetail.find(c => c.id === props.inspection.car_id);
        if (car) {
            selectCar(car);
        }
    } else if (!props.inspection?.car_id && props.inspection?.car_name) {
        carSearchQuery.value = props.inspection.car_name;
    }

    // Add event listener for keyboard navigation
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

// --- Sinkronisasi input ke form.car_name ---
watch(carSearchQuery, (val) => {
    // Reset car_id saat user mengetik
    if (form.car_id && formatCarName(selectedCar.value) !== val) {
        form.car_id = null;
        selectedCar.value = null;
        carImages.value = [];
    }
    form.car_name = val;
});

// --- Helpers ---
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

const searchCars = debounce(() => {
    if (!carSearchQuery.value.trim()) {
        filteredCars.value = [];
        showSuggestions.value = false;
        return;
    }
    isSearching.value = true;
    try {
        const query = carSearchQuery.value.toLowerCase().trim();
        filteredCars.value = props.CarDetail.filter(car =>
            formatCarName(car).toLowerCase().includes(query)
        );
        showSuggestions.value = true;
    } finally {
        isSearching.value = false;
    }
}, 300);

const handleInputBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

const selectCar = async (car) => {
    selectedCar.value = car;
    carSearchQuery.value = formatCarName(car);
    form.car_id = car.id;
    form.car_name = formatCarName(car); // Sinkronkan car_name dengan yang dipilih
    showSuggestions.value = false;
    await loadCarImages(car.id);
    updateVehicleData();
};

const getImageSrc = (image) => {
    if (!image || !image.file_path) return '';
    return `/storage/${image.file_path}`;
};

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

// Lightbox functions
const openLightbox = (index) => {
    currentImageIndex.value = index;
    showLightbox.value = true;
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
};

const closeLightbox = () => {
    showLightbox.value = false;
    document.body.style.overflow = ''; // Re-enable scrolling
};

const nextImage = () => {
    currentImageIndex.value = (currentImageIndex.value + 1) % carImages.value.length;
};

const prevImage = () => {
    currentImageIndex.value = (currentImageIndex.value - 1 + carImages.value.length) % carImages.value.length;
};

// Keyboard navigation for lightbox
const handleKeydown = (event) => {
    if (!showLightbox.value) return;
    
    switch (event.key) {
        case 'Escape':
            closeLightbox();
            break;
        case 'ArrowRight':
            nextImage();
            break;
        case 'ArrowLeft':
            prevImage();
            break;
    }
};

const updateVehicleData = () => {
    const vehicleData = {
        plate_number: form.plate_number,
        car_id: form.car_id,
        car_name: form.car_name
    };
    emit('update-vehicle', vehicleData);
};

const updateVehicleDetails = () => {
    if (!props.inspection) return;
    form.post(route('inspections.updateVehicleDetails', { inspection: props.inspection.id }), {
        preserveScroll: true,
    });
};

// --- Watch inspection props ---
watch(() => props.inspection, (newInspection) => {
    if (!newInspection) return;
    form.plate_number = newInspection.plate_number || '';
    form.car_id = newInspection.car_id || null;

    if (newInspection.car_id && props.CarDetail?.length > 0) {
        const car = props.CarDetail.find(c => c.id === newInspection.car_id);
        if (car) {
            selectCar(car);
        }
    } else if (newInspection.car_name) {
        carSearchQuery.value = newInspection.car_name;
        form.car_name = newInspection.car_name;
    }
}, { deep: true });
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

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