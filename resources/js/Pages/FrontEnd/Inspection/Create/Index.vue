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

                <!-- Komponen Plat Nomor -->
                <PlateNumberInput 
                    v-model:plate-number="form.plate_number"
                    :inspections="props.inspection"
                    :car-detail="props.CarDetail" 
                    @plate-validity-update="handlePlateValidityUpdate"
                    @car-data-update="handleCarDataUpdate"
                />

               <!-- Komponen Pencarian Mobil - PILIH SALAH SATU -->
                <!-- Tombol toggle untuk ganti mode -->
                <div class="mb-2 flex justify-end">
                    <button 
                        @click="toggleCarSelectionMode"
                        class="text-sm text-indigo-600 hover:text-indigo-800 underline"
                    >
                        {{ carSelectionMode === 'search' ? 'Ganti ke Pilihan Dropdown' : 'Ganti ke Pencarian Teks' }}
                    </button>
                </div>
                <!-- Opsi 1: Pencarian dengan input teks -->
                <CarSearchInput 
                    v-if="carSelectionMode === 'search'"
                    v-model:car-id="form.car_id"
                    v-model:car-name="form.car_name"
                    :car-detail="props.CarDetail"
                    @car-selected="handleCarSelected"
                    @car-images-loaded="handleCarImagesLoaded"
                />
                
                <!-- Opsi 2: Pencarian dengan dropdown wizard -->
                <CarSelectionWizard 
                    v-else
                    :car-detail="props.CarDetail"
                    @car-selected="handleCarSelected"
                    @car-images-loaded="handleCarImagesLoaded"
                />

            
                <!-- Select Kategori Inspeksi -->
                <div class="mb-4">
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
                <div class="mb-4 flex items-center justify-between">
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
                            :min="minDate"
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
                            'bg-gradient-to-r from-indigo-700 to-sky-600 border border-transparent text-white hover:bg-blue-700': !form.is_scheduled,
                            'bg-gradient-to-r from-green-700 to-indigo-600 border-transparent text-white hover:bg-green-700': form.is_scheduled
                        }"
                    >
                        {{ buttonText }}
                    </button>
                </div>

                <!-- BAGIAN DESKRIPSI DAN GAMBAR MOBIL -->
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
import PlateNumberInput from '@/Components/InspectionCreate/PlateNumberInput.vue';
import CarSearchInput from '@/Components/InspectionCreate/CarSearchInput.vue';
import CarSelectionWizard from '@/Components/InspectionCreate/CarSelectionWizard.vue';

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

// State untuk validasi
const isPlateInvalid = ref(false);
const minDate = ref(new Date().toISOString().split('T')[0]);

// STATE BARU UNTUK DESKRIPSI DAN GAMBAR MOBIL
const selectedCarDetail = ref(null);
const carImages = ref([]);
const showLightbox = ref(false);
const currentImageIndex = ref(0);

// Tambahkan state untuk mode seleksi
const carSelectionMode = ref('search'); // 'search' atau 'wizard'

// Fungsi toggle mode
const toggleCarSelectionMode = () => {
    carSelectionMode.value = carSelectionMode.value === 'search' ? 'wizard' : 'search';
    // Reset data mobil ketika ganti mode
    form.car_id = null;
    form.car_name = '';
    selectedCarDetail.value = null;
    carImages.value = [];
};

// Modifikasi handleCarSelected untuk menerima data dari kedua komponen
const handleCarSelected = (car) => {
    if (car.car_id && car.car_name) {
        // Format dari CarSearchInput
        form.car_id = car.car_id;
        form.car_name = car.car_name;
        selectedCarDetail.value = props.CarDetail.find(c => c.id === car.car_id) || null;
    } else if (car.id) {
        // Format dari CarSelectionWizard (object mobil lengkap)
        form.car_id = car.id;
        form.car_name = `${car.brand?.name} ${car.model?.name} ${car.type?.name} ${formatCc(car.cc)} ${formatTransmission(car.transmission)} ${car.fuel_type} ${car.year} (${car.production_period})`;
        selectedCarDetail.value = car;
    }
};

// Computed properties
const hasActiveInspections = computed(() => props.activeInspections);

const isFormValid = computed(() => {
    // Validasi dasar
    if (!form.plate_number.trim() || !form.category_id) {
        return false;
    }
    
    // Validasi untuk mobil
    if (!form.car_id && !form.car_name.trim()) {
        return false;
    }
    
    // Validasi jika dijadwalkan
    if (form.is_scheduled) {
        if (!form.scheduled_at_date || !form.scheduled_at_time) {
            return false;
        }
    }
    
    // Validasi untuk admin/coordinator
    if ((roles.includes('Admin') || roles.includes('coordinator')) && !form.inspector_id) {
        return false;
    }
    
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
const formatCc = (cc) => {
    return (cc / 1000).toFixed(1) + "L";
};

const formatTransmission = (transmission) => {
    const transmissionMap = {
        'AT': 'Automatic',
        'MT': 'Manual',
        'CVT': 'CVT',
        'AMT': 'AMT'
    };
    return transmissionMap[transmission] || transmission;
};

// Event handlers dari komponen anak
const handlePlateValidityUpdate = (validity) => {
    isPlateInvalid.value = !validity;
};

// Di Create.vue - perbaiki handleCarDataUpdate
const handleCarDataUpdate = (carData) => {
    console.log('Car data received from plate validation:', carData);
    
    if (carData) {
        form.car_id = carData.id;
        form.car_name = carData.name;
        
        // Cari detail mobil dari props.CarDetail
        if (carData.id) {
            selectedCarDetail.value = props.CarDetail.find(car => car.id === carData.id) || null;
            if (selectedCarDetail.value) {
                // Load gambar mobil
                loadCarImages(selectedCarDetail.value.id);
            }
        }
        
        // Jika ada data inspection lengkap, gunakan jika diperlukan
        if (carData.inspectionData) {
            console.log('Inspection data available:', carData.inspectionData);
        }
    } else {
        // Reset jika tidak ada data
        form.car_id = null;
        form.car_name = '';
        selectedCarDetail.value = null;
        carImages.value = [];
    }
};

// Tambahkan fungsi loadCarImages jika belum ada
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

const handleCarImagesLoaded = (images) => {
    carImages.value = images;
};

// Fungsi untuk lightbox
const getImageSrc = (image) => {
    return image?.file_path ? `/storage/${image.file_path}` : '';
};

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

// Watch effect untuk inspector_id
watchEffect(() => {
    const userIsAdminOrCoordinator = roles.includes('Admin') || roles.includes('coordinator');
    
    if (!userIsAdminOrCoordinator) {
        form.inspector_id = user?.id || null;
    } else {
        form.inspector_id = user?.id || null;
    }
});

// Logic untuk mengaktifkan toggle jadwalkan jika ada inspeksi aktif
watchEffect(() => {
    if (hasActiveInspections.value) {
        form.is_scheduled = true;
    }
});

// Watch untuk reset selectedCarDetail ketika car_id berubah
watch(() => form.car_id, (newCarId) => {
    if (!newCarId) {
        selectedCarDetail.value = null;
        carImages.value = [];
    } else {
        // Cari detail mobil ketika car_id berubah
        selectedCarDetail.value = props.CarDetail.find(car => car.id === newCarId) || null;
    }
});

// Add event listener for keyboard navigation
onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

// Submit form
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
        onSuccess: () => {
            // Handle success
        },
        onError: () => {
            // Handle error
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