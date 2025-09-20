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
                            <!-- Pesan jika tidak ada hasil -->
                            <div v-else class="p-4 text-sm text-gray-500 text-center">
                                Tidak ada data mobil yang cocok. <br>
                                Silakan input manual dengan format: <br>
                                <span class="font-medium text-gray-800">Toyota Avanza 1.5 G AT Bensin 2019</span>
                            </div>
                        </div>
                    </div>
                </div>
            
                <!-- Select Kategori Inspeksi -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="category">
                        Kategori Inspeksi {{ region.id }}
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
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, watchEffect } from 'vue';
import { usePage, useForm, Head } from '@inertiajs/vue3';
import { debounce } from 'lodash';
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
    car_name: '', // Digunakan untuk mobil baru
    category_id: '',
    is_scheduled: false,
    scheduled_at_date: null,
    scheduled_at_time: null,
    inspector_id: null, // Tambahkan inspector_id ke form
});

const page = usePage();
const user = page.props.global.user;
const region = page.props.global.region;
const roles = page.props.global.has_roles;
const permissions = page.props.global.permissions;

const formatCc = (cc) => {
  return (cc / 1000).toFixed(1) + "L";
}

// State untuk input plat nomor terpisah
const plateAreaCode = ref('');
const plateNumber = ref('');
const plateSuffix = ref('');

// State untuk autocomplete
const carSearchQuery = ref('');
const showSuggestions = ref(false);
const isSearching = ref(false);
const filteredCars = ref([]);
const selectedCar = ref(null);
const carImages = ref([]);

// State untuk validasi plat nomor baru
const inspectionValidationMessage = ref('');
const isPlateInvalid = ref(false);
const inspectionCountMessage = ref('');

// State untuk lightbox
const showLightbox = ref(false);
const currentImageIndex = ref(0);

// Computed property untuk mempermudah akses
const hasActiveInspections = computed(() => props.activeInspections);

// --- Logic Plate Number ---
const combinePlateNumber = () => {
    const combinedPlate = `${plateAreaCode.value}${plateNumber.value}${plateSuffix.value}`.toUpperCase();
    form.plate_number = combinedPlate;
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
        if (!form.scheduled_at_date || !form.scheduled_at_time) {
            return false;
        }
    }
    // Validasi tambahan untuk inspector_id
    // Cek jika roles adalah Admin atau Coordinator, maka inspector_id harus ada
    if ((roles.includes('Admin') || roles.includes('coordinator')) && !form.inspector_id) {
        return false;
    }
    return true;
});

const buttonText = computed(() => {
    return form.is_scheduled ? 'Buat Jadwal' : 'Mulai Inspeksi';
});

// Computed property untuk memfilter daftar inspektor
const filteredInspectors = computed(() => {
    // Filter semua anggota tim yang berstatus aktif
    const activeTeam = props.team.filter(member => member.status === 'active');

    const userIsAdmin = roles.includes('Admin');
    const userIsCoordinator = roles.includes('coordinator');

    if (userIsAdmin) {
        // Admin melihat semua anggota tim yang aktif
        return activeTeam;
    } else if (userIsCoordinator) {
        // Coordinator hanya melihat anggota tim yang aktif di regional yang sama
        const currentUserRegionId = region.id;
        return activeTeam.filter(member => member.region_id === currentUserRegionId);
    }
    // Untuk peran lain, daftar ini akan kosong
    return [];
});

// Format nama mobil
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
const selectCar = async (car) => {
    selectedCar.value = car;
    carSearchQuery.value = formatCarName(car);
    form.car_id = car.id;
    form.car_name = formatCarName(car); // Kosongkan car_name jika mobil dipilih
    showSuggestions.value = false;
    await loadCarImages(car.id);
};

// Memuat gambar mobil dari API
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

const getImageSrc = (image) => {
    if (!image || !image.file_path) return '';
    return `/storage/${image.file_path}`;
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

// Add event listener for keyboard navigation
onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

// Lacak perubahan pada carSearchQuery untuk mereset car_id
watch(carSearchQuery, (newValue) => {
    // Jika user mengetik (bukan memilih dari saran), reset car_id
    if (!selectedCar.value || formatCarName(selectedCar.value) !== newValue) {
        form.car_id = null;
        form.car_name = newValue;
        selectedCar.value = null;
        carImages.value = [];
    }
});

// WATCH EFFECT
// This watcher handles the core logic of setting inspector_id based on roles
// It runs immediately and whenever `roles` or `user` changes.
watchEffect(() => {
    const userIsAdminOrCoordinator = roles.includes('Admin') || roles.includes('coordinator');
    
    if (!userIsAdminOrCoordinator) {
        // If the user is not an Admin or Coordinator,
        // their own ID should be automatically assigned.
        form.inspector_id = user?.id || null;
    } else {
        // If they are Admin/Coordinator, we reset the value
        // to null, so they are required to select an inspector.
        form.inspector_id = user?.id || null;
    }
});

// Logic untuk mengaktifkan toggle jadwalkan jika ada inspeksi aktif
watchEffect(() => {
  if (hasActiveInspections.value) {
    form.is_scheduled = true;
  }
});

// --- NEW LOGIC FOR PLATE NUMBER VALIDATION ---
watch(() => form.plate_number, (newPlateNumber) => {
    // Reset state
    inspectionValidationMessage.value = '';
    isPlateInvalid.value = false;
    inspectionCountMessage.value = '';
    form.car_id = null;
    form.car_name = '';
    carSearchQuery.value = '';

    if (newPlateNumber.length >= 6) {
        const existingInspections = props.inspection.filter(i => i.plate_number === newPlateNumber);
        
        if (existingInspections.length > 0) {
            // Check for inspections with a "blocking" status
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
