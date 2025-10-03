<template>
    <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100">
        <div class="bg-indigo-200 px-6 py-2 border-b flex items-center justify-between">
            <h4 class="text-base font-semibold text-indigo-700">
            Detail Kendaraan
            </h4>
        </div>

        <div v-if="inspection" class="p-4 space-y-2">
            <!-- Form Input Plate Number -->
            <div class="space-y-2 pb-2 border-b border-gray-100 last:border-0 last:pb-0">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Plat Kendaraan
                </label>
                <div class="flex items-center space-x-2">
                    <!-- Kode Wilayah (Huruf) -->
                    <input
                        v-model="plateAreaCode"
                        type="text"
                        placeholder="Contoh: B"
                        class="w-1/4 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center transition duration-150"
                        :class="{'border-red-500 focus:border-red-500': !isPlateNumberValid || isPlateInvalid}"
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
                        :class="{'border-red-500 focus:border-red-500': !isPlateNumberValid || isPlateInvalid}"
                        @input="handlePlateInput('number')"
                        maxlength="4"
                    >
                    <!-- Huruf Acak (Huruf) -->
                    <input
                        v-model="plateSuffix"
                        type="text"
                        placeholder="ABC"
                        class="w-1/4 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center transition duration-150"
                        :class="{'border-red-500 focus:border-red-500': !isPlateNumberValid || isPlateInvalid}"
                        @input="handlePlateInput('suffix')"
                        maxlength="3"
                    >
                </div>
                <!-- Tampilkan pesan error jika ada -->
                    <span v-if="plateNumberError" class="text-xs text-red-500 font-normal ml-2">{{ plateNumberError }}</span>
                    <span v-if="inspectionValidationMessage" class="text-xs text-red-500 font-normal ml-2">{{ inspectionValidationMessage }}</span>
                    <span v-if="inspectionCountMessage" class="text-xs text-green-500 font-normal ml-2">{{ inspectionCountMessage }}</span>
            </div>

            <!-- Form Input Car Name with Auto-complete -->
            <div class="space-y-2 pb-2 border-b border-gray-100 last:border-0 last:pb-0">
                <label class="block text-sm font-medium text-gray-700">
                    Nama Mobil
                   
                </label>
                <div class="relative">
                    <input
                        v-model="carSearchQuery"
                        type="text"
                        placeholder="Cari atau ketik nama mobil..."
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
                        :class="{'border-red-500 focus:border-red-500': isCarNameInvalid}"
                        @input="handleCarInput"
                        @focus="showSuggestions = true"
                        @blur="handleInputBlur"
                    >
                    
                    <div v-if="isSearching" class="absolute right-3 top-3">
                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <!-- Bagian ini akan menampilkan validasi jika nama mobil kosong -->
                    <span v-if="isCarNameInvalid" class="text-xs text-red-500 font-normal ml-2">Nama mobil tidak boleh kosong.</span>
                    
<!-- Tampilkan pesan jika car_id tidak ada -->
<div v-if="!form.car_id && carSearchQuery" class="mt-1 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
    <div class="flex items-start">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <p class="text-yellow-800 font-medium mb-1">Informasi Penting</p>
            <p class="text-yellow-700 text-sm mb-2">
                Anda memasukkan nama mobil secara manual. Data detail mobil (spesifikasi, gambar, deskripsi) 
                <span class="font-semibold">tidak akan tersedia</span> dalam laporan inspeksi.
            </p>
            <p class="text-yellow-700 text-sm mb-2">
                Untuk laporan yang lengkap dengan semua detail mobil, silakan pilih mobil dari hasil pencarian.
            </p>
            <p class="text-yellow-700 text-sm">
                Jika tidak ingin mengubah data mobil dan anda tidak sengaja sudah menghapus atau edit, <span class="font-semibold">jangan klik tombol "Perbarui Detail Kendaraan"</span>. 
                Sebagai gantinya, <span class="font-semibold">refresh halaman</span> untuk kembali ke data awal.
            </p>
        </div>
    </div>
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
                :disabled="!canUpdate"
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
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import PrimaryButton from '../PrimaryButton.vue';
import ActionMessage from '../ActionMessage.vue';

const props = defineProps({
    inspection: {
        type: Object,
        default: null
    },
    CarDetail: Array,
    allInspections: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update-vehicle', 'update:validation', 'update:hasUnsavedChanges']);

const form = useForm({
    plate_number: props.inspection?.plate_number || '',
    car_id: props.inspection?.car_id || null,
    car_name: props.inspection?.car_name || ''
});

// State untuk menyimpan nilai awal
const initialPlateNumber = ref(props.inspection?.plate_number || '');
const initialCarId = ref(props.inspection?.car_id || null);
const initialCarName = ref(props.inspection?.car_name || '');

// State untuk input plat nomor terpisah
const plateAreaCode = ref('');
const plateNumber = ref('');
const plateSuffix = ref('');

// State untuk fungsionalitas validasi baru
const isPlateInvalid = ref(false);
const inspectionValidationMessage = ref('');
const inspectionCountMessage = ref('');

// State untuk fungsionalitas lain
const carSearchQuery = ref(form.car_name);
const showSuggestions = ref(false);
const isSearching = ref(false);
const filteredCars = ref([]);
const selectedCar = ref(null);
const carImages = ref([]);

// State untuk lightbox
const showLightbox = ref(false);
const currentImageIndex = ref(0);

// --- Initial Setup & Parsing Data ---
onMounted(() => {
    parsePlateNumber(props.inspection?.plate_number);
    if (props.inspection?.car_id && props.CarDetail?.length > 0) {
        const car = props.CarDetail.find(c => c.id === props.inspection.car_id);
        if (car) {
            selectCar(car);
        }
    } else if (!props.inspection?.car_id && props.inspection?.car_name) {
        carSearchQuery.value = props.inspection.car_name;
        // Jika tidak ada car_id, pastikan form.car_id null
        form.car_id = null;
    }
    window.addEventListener('keydown', handleKeydown);

     // Kirim status ke parent bahwa ada perubahan yang belum disimpan
    emit('update:hasUnsavedChanges', isFormChanged.value);

    // Kirim status validasi awal ke induk
    emit('update:validation', isFormInvalid.value);

});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

// --- Logic Plate Number ---
const parsePlateNumber = (plateStr) => {
    if (!plateStr) {
        plateAreaCode.value = '';
        plateNumber.value = '';
        plateSuffix.value = '';
        return;
    }
    const plate = plateStr.toUpperCase().trim();
    const match = plate.match(/^([A-Z]{1,2})?([0-9]{1,4})?([A-Z]{1,3})?$/);
    if (match) {
        plateAreaCode.value = match[1] || '';
        plateNumber.value = match[2] || '';
        plateSuffix.value = match[3] || '';
    } else {
        let currentPart = 'area';
        let area = '';
        let number = '';
        let suffix = '';
        for (const char of plate) {
            if (currentPart === 'area' && /[A-Z]/.test(char)) {
                area += char;
            } else if (currentPart === 'area' && /[0-9]/.test(char)) {
                currentPart = 'number';
                number += char;
            } else if (currentPart === 'number' && /[0-9]/.test(char)) {
                number += char;
            } else if (currentPart === 'number' && /[A-Z]/.test(char)) {
                currentPart = 'suffix';
                suffix += char;
            } else if (currentPart === 'suffix' && /[A-Z]/.test(char)) {
                suffix += char;
            }
        }
        plateAreaCode.value = area;
        plateNumber.value = number;
        plateSuffix.value = suffix;
    }
    combinePlateNumber();
};

const combinePlateNumber = () => {
    const combinedPlate = `${plateAreaCode.value}${plateNumber.value}${plateSuffix.value}`;
    form.plate_number = combinedPlate;
    updateVehicleData();
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

// --- Computed Properties for Validation & Button Logic ---
const isPlateNumberValid = computed(() => {
    const combinedPlate = form.plate_number;
    const regex = /^[A-Z]{1,2}\d{1,4}[A-Z]{0,3}$/;
    return regex.test(combinedPlate);
});

// Ini adalah properti computed yang memeriksa apakah nama mobil kosong atau tidak
const isCarNameInvalid = computed(() => {
    return !carSearchQuery.value || carSearchQuery.value.trim() === '';
});

const plateNumberError = computed(() => {
    if (form.plate_number.length > 0 && !isPlateNumberValid.value) {
        return "Format plat nomor tidak valid.";
    }
    return null;
});

// Ini adalah properti computed yang memeriksa semua validasi form
const isFormInvalid = computed(() => {
    const isPlateEmpty = !form.plate_number || form.plate_number.trim() === '';
    const isCarNameEmpty = !carSearchQuery.value || carSearchQuery.value.trim() === '';
    return isPlateEmpty || isCarNameEmpty || !isPlateNumberValid.value || isPlateInvalid.value ;
});

// Ini adalah properti computed yang memeriksa apakah ada perubahan data dari nilai awal
const isFormChanged = computed(() => {
    const plateChanged = form.plate_number !== initialPlateNumber.value;
    const carChanged = form.car_id !== initialCarId.value || carSearchQuery.value !== initialCarName.value;
    return plateChanged || carChanged;
});

// Ini adalah properti computed yang menentukan apakah tombol 'Update' bisa diklik atau tidak
// Tombol bisa diklik jika ada perubahan DAN tidak ada error validasi
const canUpdate = computed(() => {
    return isFormChanged.value && !isFormInvalid.value;
});

// --- Helpers ---
const formatCarName = (car) => {
    if (!car) return '';
    const parts = [];
    if (car.brand?.name) parts.push(car.brand.name);
    if (car.model?.name) parts.push(car.model.name);
    if (car.type?.name) parts.push(car.type.name);
    if (car.cc) parts.push((car.cc / 1000).toFixed(1)); // ubah ke liter
    if (car.transmission) parts.push(car.transmission);
    if (car.year) parts.push(car.year.toString());
    if (car.fuel_type) parts.push(car.fuel_type);
    if (car.production_period) parts.push(`(${car.production_period})`);
    return parts.join(' ');
};

// Fungsi untuk menangani input mobil
const handleCarInput = () => {
    // Jika pengguna mengetik manual (bukan memilih dari dropdown), kosongkan car_id
    if (form.car_id) {
        form.car_id = null;
        selectedCar.value = null;
        carImages.value = [];
    }
    
    // Lakukan pencarian
    searchCars();
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
        
        // Setelah dropdown tertutup, pastikan car_name sesuai dengan query
        form.car_name = carSearchQuery.value;
        updateVehicleData();
    }, 200);
};

const selectCar = async (car) => {
    selectedCar.value = car;
    carSearchQuery.value = formatCarName(car);
    form.car_id = car.id;
    form.car_name = formatCarName(car);
    showSuggestions.value = false;
    await loadCarImages(car.id);
    updateVehicleData();

     // Kirim status ke parent bahwa ada perubahan yang belum disimpan
    emit('update:hasUnsavedChanges', isFormChanged.value);

    // Kirim status validasi ke induk
    emit('update:validation', isFormInvalid.value);
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
        onSuccess: () => {
            // PERBARUI NILAI INITIAL SETELAH BERHASIL SIMPAN
            initialPlateNumber.value = form.plate_number;
            initialCarId.value = form.car_id;
            initialCarName.value = form.car_name;
            
            // Kirim status ke parent bahwa sudah tidak ada perubahan yang belum disimpan
            emit('update:hasUnsavedChanges', false);
            
            // Optional: Tampilkan pesan sukses
            console.log('Data berhasil disimpan!');
        },
        onError: (errors) => {
            // Handle error jika perlu
            console.error('Gagal menyimpan data:', errors);
        }
    });
};

// --- LOGIKA VALIDASI BARU UNTUK NOMOR PLAT DAN MENGIRIM STATUS KE INDUK ---
// Watcher ini akan berjalan setiap kali form.plate_number atau form.car_name berubah
watch([() => form.plate_number, () => form.car_name], (newValues) => {
    inspectionValidationMessage.value = '';
    isPlateInvalid.value = false;
    inspectionCountMessage.value = '';
    
    const newPlateNumber = newValues[0];

    // Pengecekan plat nomor hanya jika tidak kosong
    if (newPlateNumber && newPlateNumber.length >= 6) {
        const existingInspections = props.allInspections.filter(i =>
            i.plate_number === newPlateNumber && i.id !== props.inspection?.id
        );
        
        if (existingInspections.length > 0) {
            const blockingStatuses = ['draft', 'in_progress', 'pending', 'pending_review', 'revision'];
            const blockingInspection = existingInspections.find(i => blockingStatuses.includes(i.status));

            if (blockingInspection) {
                isPlateInvalid.value = true;
                inspectionValidationMessage.value = `Nomor plat ini sedang dalam proses inspeksi dengan status: ${blockingInspection.status.replace(/_/g, ' ').toUpperCase()}.`;
            } else {
                const completedInspections = existingInspections.filter(i => ['approved', 'rejected', 'completed', 'cancelled'].includes(i.status));
                
                if (completedInspections.length > 0) {
                    completedInspections.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    const latestInspection = completedInspections[0];

                    if (latestInspection.car_id) {
                        const car = props.CarDetail.find(c => c.id === latestInspection.car_id);
                        if (car) {
                            selectCar(car);
                        }
                    } else if (latestInspection.car_name) {
                        carSearchQuery.value = latestInspection.car_name;
                        form.car_name = latestInspection.car_name;
                        form.car_id = null; // Pastikan car_id null jika tidak ada
                    }

                    inspectionCountMessage.value = `Nomor plat ini sudah pernah diperiksa ${completedInspections.length} kali sebelumnya.`;
                }
            }
        }
    }
    
     // Kirim status ke parent bahwa ada perubahan yang belum disimpan
    emit('update:hasUnsavedChanges', isFormChanged.value);

    // Emit status validasi ke induk setelah semua pengecekan selesai
    emit('update:validation', isFormInvalid.value);
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
