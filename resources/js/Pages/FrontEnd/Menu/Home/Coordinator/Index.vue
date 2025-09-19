<template>
    <AppLayout title="Buat Inspeksi Baru">
        <Head title="Dashboard Koordinator" />
        <!-- Kontainer utama untuk konten halaman -->
        <div class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-md p-4 md:p-6 flex items-center justify-between">
                <h1 class="text-xl font-bold text-gray-900">Dashboard Koordinator</h1>
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Wilayah: {{ region.name }}</span>
                </div>
            </header>

            <main class="w-full py-4 sm:px-4 lg:px-4">
                <!-- <div class="w-full px-4 sm:px-6 lg:px-6 py-6"></div> -->
                <!-- Bagian Filter -->
                <div class="px-4 sm:px-0 mb-4">
                    <div class="bg-white overflow-hidden shadow-lg rounded-2xl p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Saring Inspeksi</h3>
                        <!-- Kontainer untuk Status & Rentang Tanggal -->
                        <div class="grid grid-cols-2 sm:grid-cols-2 gap-4 mb-4">
                            <!-- Filter Region (hanya admin) -->
<!-- Filter Region (Admin only) -->
<div v-if="isAdmin">
  <label for="region" class="block text-sm font-medium text-gray-700">Region</label>
  <select 
    id="region"
    v-model="filters.region_id"
    @change="updateFilters"
    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none 
           focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
  >
    <option value="all">Semua Region</option>
    <option v-for="reg in allRegions" :key="reg.id" :value="reg.id">
      {{ reg.name }}
    </option>
  </select>
</div>

<!-- Filter User (Admin only) -->
<div v-if="isAdmin">
  <label for="user" class="block text-sm font-medium text-gray-700">User</label>
  <select 
    id="user"
    v-model="filters.user_id"
    @change="updateFilters"
    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none 
           focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
  >
    <option value="">Semua User</option>
    <option v-for="teamUser in regionUsers" :key="teamUser.id" :value="teamUser.id">
      {{ teamUser.name }}
    </option>
  </select>
</div>



                            <!-- Filter Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select 
                                    id="status" 
                                    v-model="filters.status"
                                    @change="updateFilters"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="draft">Dibuat</option>
                                    <option value="in_progress">Dalam Proses</option>
                                    <option value="pending">Tertunda</option>
                                    <option value="pending_review">Menunggu Tinjauan</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                    <option value="revision">Revisi</option>
                                    <option value="completed">Selesai</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </select>
                            </div>
                            <!-- Filter Rentang Tanggal -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Rentang Tanggal</label>
                                <select 
                                    id="date" 
                                    v-model="filters.dateRange"
                                    @change="updateFilters"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                >
                                    <option value="all">Sepanjang Waktu</option>
                                    <option value="today">Hari Ini</option>
                                    <option value="week">Minggu Ini</option>
                                    <option value="month">Bulan Ini</option>
                                </select>
                            </div>
                        </div>
                        <!-- Input Pencarian (Lebar Penuh) -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Cari</label>
                            <input 
                                type="text" 
                                id="search" 
                                v-model="filters.search"
                                @input="debouncedUpdateFilters"
                                placeholder="Cari berdasarkan mobil atau inspektor..."
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>

                <!-- Gambaran Umum Statistik -->
                <div class="px-4 sm:px-0 mb-4">
                    <div class="flex flex-col gap-4">
                        <!-- Kartu Total Inspeksi (Lebar Penuh) -->
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                            <dt class="text-sm font-medium opacity-80">Total Inspeksi</dt>
                            <dd class="text-3xl font-semibold">{{ stats.total }}</dd>
                        </div>
                       <div class="grid grid-cols-3 sm:grid-cols-3 gap-2">
                            <!-- Dibuat -->
                            <div class="bg-yellow-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Dibuat</dt>
                                <dd class="text-2xl font-semibold text-yellow-600">{{ stats.draft }}</dd>
                            </div>

                            <!-- Dalam Proses -->
                            <div class="bg-blue-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Dalam Proses</dt>
                                <dd class="text-2xl font-semibold text-blue-600">{{ stats.in_progress }}</dd>
                            </div>

                            <!-- Tertunda -->
                            <div class="bg-amber-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Tertunda</dt>
                                <dd class="text-2xl font-semibold text-amber-600">{{ stats.pending }}</dd>
                            </div>

                            <!-- Ditinjau -->
                            <div class="bg-indigo-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Ditinjau</dt>
                                <dd class="text-2xl font-semibold text-indigo-600">{{ stats.pending_review }}</dd>
                            </div>

                            <!-- Disetujui -->
                            <div class="bg-green-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Disetujui</dt>
                                <dd class="text-2xl font-semibold text-green-600">{{ stats.approved }}</dd>
                            </div>

                            <!-- Ditolak -->
                            <div class="bg-red-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Ditolak</dt>
                                <dd class="text-2xl font-semibold text-red-600">{{ stats.rejected }}</dd>
                            </div>

                            <!-- Revisi -->
                            <div class="bg-purple-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Revisi</dt>
                                <dd class="text-2xl font-semibold text-purple-600">{{ stats.revision }}</dd>
                            </div>

                            <!-- Dibatalkan -->
                            <div class="bg-gray-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Dibatalkan</dt>
                                <dd class="text-2xl font-semibold text-gray-600">{{ stats.cancelled }}</dd>
                            </div>

                            <!-- Selesai -->
                            <div class="bg-emerald-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Selesai</dt>
                                <dd class="text-2xl font-semibold text-emerald-600">{{ stats.completed }}</dd>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Tabel Inspeksi -->
                <div class="px-4 sm:px-0">
                    <div class="bg-white shadow-lg overflow-hidden rounded-2xl">
                        <div class="flex flex-col">
                            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                    <div class="shadow overflow-hidden border-b border-gray-200 rounded-xl">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Detail Mobil
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Inspektur
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Tanggal Inspeksi
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Status Inspection
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Status Pembayaran
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Tindakan
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="inspection in inspections.data" :key="inspection.id">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-gray-900">{{ inspection.car_name }}</div>
                                                                <div class="text-sm text-gray-500">{{ inspection.plate_number }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ inspection.user.name }}</div>
                                                        <div class="text-sm text-gray-500">{{ inspection.user.email }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ formatDate(inspection.inspection_date) }}</div>
                                                        <div class="text-sm text-gray-500">{{ formatTime(inspection.inspection_date) }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass(inspection.status)}`">
                                                            {{ formatStatus(inspection.status) ?? '-' }}
                                                        </span>
                                                    </td>
                                                   <td class="px-6 py-4 whitespace-nowrap">
                                                        <span 
                                                            :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass(inspection.transaction?.status ?? 'unpaid')}`"
                                                        >
                                                            {{ inspection.transaction?.status ? formatStatus(inspection.transaction.status) : 'Belum Ada Transaksi' }}
                                                        </span>
                                                    </td>

                                                   <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                        <Link 
                                                            :href="route('inspections.review', { id: encryptedIds[inspection.id] })" 
                                                            class="inline-flex items-center px-3 py-1 rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200"
                                                        >
                                                            Lihat
                                                        </Link>

                                                        <Link 
                                                            v-if="inspection.status === 'draft' || inspection.user_id == null" 
                                                            :href="route('coordinator.inspections.show', { id: encryptedIds[inspection.id] })"
                                                            class="inline-flex items-center px-3 py-1 rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors duration-200"
                                                        >
                                                            Tugaskan
                                                        </Link>
                                                    </td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                 <!-- Pagination Wrapper -->
                                    <div class="bg-white px-6 py-2 flex items-center justify-between border-t border-gray-200 mt-2">

                                        <!-- Mobile Pagination -->
                                        <div class="flex-1 flex justify-between sm:hidden">
                                            <Link
                                                :href="inspections.prev_page_url ?? '#'"
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-500 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                :class="{ 'opacity-50 pointer-events-none': !inspections.prev_page_url }"
                                                preserve-state
                                            >
                                                <
                                            </Link>
                                            <Link
                                                :href="inspections.next_page_url ?? '#'"
                                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-500 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                :class="{ 'opacity-50 pointer-events-none': !inspections.next_page_url }"
                                                preserve-state
                                            >
                                                >
                                            </Link>
                                        </div>

                                        <!-- Desktop Pagination -->
                                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm text-gray-700">
                                                    Menampilkan
                                                    <span class="font-medium">{{ inspections.from }}</span>
                                                    sampai
                                                    <span class="font-medium">{{ inspections.to }}</span>
                                                    dari
                                                    <span class="font-medium">{{ inspections.total }}</span>
                                                    hasil
                                                </p>
                                            </div>
                                            <div>
                                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                                    <Link 
                                                        v-for="link in inspections.links"
                                                        :key="link.label"
                                                        :href="link.url || '#'"
                                                        :class="`relative inline-flex items-center px-4 py-2 border text-sm font-medium 
                                                            ${link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'} 
                                                            ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`"
                                                        v-html="link.label"
                                                        preserve-state
                                                    />
                                                </nav>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, reactive, computed, onMounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AppLayout from '@/Layouts/AppLayout.vue';

// Get props from Inertia
const props = defineProps({
    inspections: Object,
    encryptedIds:Object,
    filters: Object,
    stats: Object,
    region: Object,

    allRegions: Array, // [ADMIN ADD]
    isAdmin: Boolean,  // [ADMIN ADD]
});

// Filters
const filters = reactive({
    status: props.filters.status || '',
    dateRange: props.filters.dateRange || 'all',
    search: props.filters.search || '',
    region_id: props.filters.region_id || 'all',
    user_id: props.filters.user_id || '',
});

const regionUsers = ref([]);

// Jika admin dan pilih region, ambil usernya
watch(() => filters.region_id, async (newRegionId) => {
  if (props.isAdmin) {
    if (!newRegionId || newRegionId === "all") {
      // Semua user dari semua region
      let response = await axios.get(route('api.users.all'));
      regionUsers.value = response.data;
    } else {
      // User dari region tertentu
      let response = await axios.get(route('api.region.users', { id: newRegionId }));
      regionUsers.value = response.data;
    }
  }
});

// Debounced search function
const debouncedUpdateFilters = debounce(() => {
    updateFilters();
}, 500);

// Update filters and reload page
const updateFilters = () => {
    router.get(route('coordinator.inspections.index'), filters, {
        preserveState: true,
        replace: true
    });
};

// Format date
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Format time
const formatTime = (dateString) => {
    const options = { hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleTimeString('id-ID', options);
};

// Format status
const formatStatus = (status) => {
    const statusMap = {
        'draft': 'Dibuat',
        'in_progress': 'Dalam Proses',
        'pending': 'Tertunda',
        'pending_review': 'Menunggu Tinjauan',
        'approved': 'Disetujui',
        'rejected': 'Ditolak',
        'revision': 'Revisi',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan',

          // Transaction
        'paid': 'Sudah di bayar',
        'failed': 'Gagal',
        'refunded': 'Dikembalikan',
        'expired': 'Kadaluarsa',
        'released': 'Diberikan',
    };
    return statusMap[status] || status;
};

// Status class
const statusClass = (status) => {
    const classMap = {
       'draft': 'bg-slate-400 text-white',
        'in_progress': 'bg-blue-600 text-white',
        'pending': 'bg-amber-500 text-white',
        'pending_review': 'bg-indigo-500 text-white',
        'approved': 'bg-emerald-500 text-white',
        'rejected': 'bg-red-500 text-white',
        'revision': 'bg-orange-500 text-white',
        'completed': 'bg-emerald-500 text-white',
        'cancelled': 'bg-red-500 text-white',

        'failed': 'bg-red-500 text-white',
        'expired': 'bg-red-500 text-white',
        'paid': 'bg-emerald-500 text-white',
        'refunded': 'bg-orange-500 text-white',
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
};

</script>
