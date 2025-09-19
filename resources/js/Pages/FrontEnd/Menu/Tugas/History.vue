<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarDaysIcon, ClipboardDocumentListIcon, MagnifyingGlassIcon, XMarkIcon, FunnelIcon } from '@heroicons/vue/24/outline';
import { CarIcon } from 'lucide-vue-next';
import { ref, watch, computed, onMounted } from 'vue';

const props = defineProps({
    tasks: Array,
    encryptedIds: Object,
    filters: Object,
    availableMonths: Object,
    currentMonth: Number,
    currentYear: Number
});

// Mapping status ke label bahasa Indonesia
const statusLabel = (status) => {
   switch (status) {
        case 'draft': return 'Dibuat';
        case 'in_progress': return 'Dalam Proses';
        case 'pending': return 'Menunggu';
        case 'pending_review': return 'Menunggu Review';
        case 'approved': return 'Disetujui';
        case 'rejected': return 'Ditolak';
        case 'revision': return 'Revisi';
        case 'completed': return 'Selesai';
        case 'cancelled': return 'Dibatalkan';
        default: return status;
    }
};

const getButtonLabel = () => 'Lihat Detail';

// State untuk filter dan pencarian
const showSearch = ref(false);
const searchTerm = ref(props.filters.search);
const selectedMonth = ref(props.filters.month);
const selectedYear = ref(props.filters.year);

// Daftar bulan dalam Bahasa Indonesia
const months = [
    { value: 1, name: 'Jan' },
    { value: 2, name: 'Feb' },
    { value: 3, name: 'Mar' },
    { value: 4, name: 'Apr' },
    { value: 5, name: 'Mei' },
    { value: 6, name: 'Jun' },
    { value: 7, name: 'Jul' },
    { value: 8, name: 'Ags' },
    { value: 9, name: 'Sep' },
    { value: 10, name: 'Okt' },
    { value: 11, name: 'Nov' },
    { value: 12, name: 'Des' }
];

// Tahun yang tersedia
const availableYears = computed(() => {
    return Object.keys(props.availableMonths || {}).sort((a, b) => b - a);
});

// Filter data berdasarkan bulan dan tahun
const filterByMonthYear = () => {
    router.get(route('inspections.history'), {
        month: selectedMonth.value,
        year: selectedYear.value,
        search: '' // Reset search saat filter aktif
    }, {
        preserveState: true,
        replace: true
    });
};

// Pencarian dengan debounce
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('inspections.history'), {
            search: searchTerm.value,
            // Tidak mengirim month/year saat searching
        }, {
            preserveState: true,
            replace: true
        });
    }, 300);
};

// Aktifkan mode pencarian
const activateSearch = () => {
    showSearch.value = true;
    searchTerm.value = '';
    // Reset filter saat masuk mode pencarian
    router.get(route('inspections.history'), {
        search: ''
    }, {
        preserveState: true,
        replace: true
    });
};

// Nonaktifkan mode pencarian dan kembali ke filter
const deactivateSearch = () => {
    showSearch.value = false;
    searchTerm.value = '';
    // Kembali ke filter bulan/tahun saat ini
    router.get(route('inspections.history'), {
        month: selectedMonth.value,
        year: selectedYear.value,
        search: ''
    }, {
        preserveState: true,
        replace: true
    });
};

// Watch perubahan filter bulan/tahun
watch([selectedMonth, selectedYear], () => {
    if (!showSearch.value) {
        filterByMonthYear();
    }
});
</script>

<template>
    <AppLayout>
        <Head title="Riwayat Inspeksi" />
        <div class="w-full px-4 sm:px-6 lg:px-6 py-6">
            <h3 class="text-xl md:text-3xl font-bold text-gray-900 mb-6 text-center">
                Riwayat Inspeksi
            </h3>

            <!-- Controls Container -->
            <div class="bg-white rounded-lg shadow-md p-3 mb-6">
                <!-- Mode Pencarian -->
                <div v-if="showSearch" class="flex items-center justify-between">
                    <div class="relative flex-1">
                        <input
                            v-model="searchTerm"
                            @input="handleSearch"
                            type="text"
                            placeholder="Cari berdasarkan plat atau nama mobil..."
                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            autofocus
                        />
                        <button
                            @click="deactivateSearch"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <!-- Mode Filter -->
                <div v-else class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 flex-1">
                        <FunnelIcon class="h-4 w-4 text-gray-500 flex-shrink-0" />
                        <select 
                            v-model="selectedMonth" 
                            class="px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm"
                        >
                            <option v-for="month in months" :key="month.value" :value="month.value">
                                {{ month.name }}
                            </option>
                        </select>
                        
                        <select 
                            v-model="selectedYear" 
                            class="px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm"
                        >
                            <option v-for="year in availableYears" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                    </div>

                    <button
                        @click="activateSearch"
                        class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <MagnifyingGlassIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Info Filter Aktif -->
            <div v-if="!showSearch && searchTerm" class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
                <p class="text-sm text-blue-800 flex items-center gap-2">
                    <MagnifyingGlassIcon class="h-4 w-4" />
                    Menampilkan hasil pencarian: "{{ searchTerm }}"
                    <button @click="deactivateSearch" class="text-blue-600 hover:text-blue-800 ml-2">
                        × Hapus pencarian
                    </button>
                </p>
            </div>

            <!-- Jika ada tasks -->
            <div
                v-if="tasks.length > 0"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2"
            >
                <div
                    v-for="task in tasks"
                    :key="task.id"
                    class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition"
                >
                    <!-- ... (kode card tetap sama) ... -->
                    <!-- Jadwal + Link Log -->
                    <div class="p-4 flex justify-between items-start">
                        <div>
                            <div class="flex items-center mb-1">
                                <CalendarDaysIcon class="h-5 w-5 text-blue-500 mr-2" />
                                <span class="text-sm font-medium text-gray-600">Jadwal</span>
                            </div>
                            <p class="text-sm font-semibold text-blue-700 ml-7 -mt-1">
                                {{ new Date(task.inspection_date).toLocaleDateString('id-ID', {
                                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric',
                                    hour: '2-digit', minute: '2-digit'
                                }) }}
                            </p>
                        </div>

                        <!-- Link ke Log -->
                        <Link
                            :href="route('inspection.log', { inspection: encryptedIds[task.id] })"
                            class="text-xs font-semibold text-indigo-600 hover:underline"
                        >
                            Lihat Log
                        </Link>
                    </div>

                    <!-- Mobil -->
                    <div  class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center">
                            <CarIcon class="h-5 w-5 text-gray-500 mr-2" />
                            <div class="text-sm font-medium text-gray-800">
                               <div v-if="task.car">
                                    {{ `${task.car.brand.name} ${task.car.model.name} ${task.car.type.name} ${task.car.cc} ${task.car.transmission} ${task.car.year}` }}
                                    <span class="text-gray-600">({{ task.car.fuel_type }})</span>
                                </div>
                                <div v-else>
                                    {{ task.car_name }}
                                </div>
                            </div>
                        </div>
                        <!-- Nomor Plat Mobil -->
                        <div class="flex items-center mt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 mr-2">NO POLISI:</span>
                            <span class="text-sm font-bold text-gray-900">{{ task.plate_number }}</span>
                        </div>
                    </div>

                    <!-- Status & Catatan -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center mb-1">
                        <ClipboardDocumentListIcon class="h-5 w-5 text-green-500 mr-2" />
                        <span class="text-sm font-medium text-gray-600">Status</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-800 ml-7 -mt-1">
                        {{ statusLabel(task.status) }}
                        </p>
                    </div>

                    <!-- Tombol detail -->
                    <div class="p-4">
                         <Link
                            :href="route('inspections.review', { id: encryptedIds[task.id] })"
                            class="inline-flex items-center justify-center w-full px-3 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white font-medium rounded-md text-sm transition-colors"
                        >
                            {{ getButtonLabel() }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Jika tidak ada tasks -->
            <div v-else class="text-center py-8 bg-white rounded-lg shadow-md">
                <p class="text-gray-500">
                    {{ searchTerm ? 'Tidak ditemukan hasil pencarian' : 'Belum ada riwayat inspeksi untuk periode ini' }}
                </p>
            </div>

            <!-- Tombol kembali -->
            <div class="text-center mt-6">
                <Link
                    :href="route('job.index')"
                    class="inline-block px-4 py-2 text-sm font-medium text-indigo-600 border border-indigo-600 rounded-md hover:bg-blue-50"
                >
                    Kembali ke Tugas
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-bounce {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}
</style>