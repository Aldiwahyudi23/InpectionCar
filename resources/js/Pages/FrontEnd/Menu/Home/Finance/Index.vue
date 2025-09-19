<template>
    <AppLayout title="Laporan Keuangan">
        <Head title="Laporan Distribusi Pendapatan" />

        <!-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4"> -->
        <div class="w-full px-4 sm:px-6 lg:px-6 py-6">
            <!-- Header -->
            <div class="mb-2 text-center">
                <h1 class="text-2xl font-bold text-gray-900">Laporan Distribusi Pendapatan</h1>
            </div>

            <!-- Filter Section -->
            <div class="bg-white shadow rounded-lg p-4 mb-3">
                <h2 class="text-lg font-medium text-gray-900 mb-2">Filter Laporan</h2>
                
                <!-- Filter Layout - Responsif -->
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-2">
                    <!-- Tanggal - Selalu sejajar di mobile dan desktop -->
                    <div class="flex flex-col-2 sm:gap-4 gap-4 w-full lg:w-auto">
                        <div class="w-full sm:w-1/2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                            <input
                                type="date"
                                v-model="filters.date_from"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                        <div class="w-full sm:w-1/2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                            <input
                                type="date"
                                v-model="filters.date_to"
                                :max="today"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                    </div>

                    <!-- Region, Pengguna, dan Status -->
                    <div class="flex flex-col-2  sm:gap-4 gap-4 w-full lg:w-auto">
                        <div v-if="role === 'Admin' || role === 'superadmin'" class="w-full sm:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                            <select v-model="filters.region_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Region</option>
                                <option v-for="region in regions" :key="region.id" :value="region.id">
                                    {{ region.name }}
                                </option>
                            </select>
                        </div>
                        <div v-if="role === 'Admin' || role === 'superadmin' || role === 'coordinator'" class="w-full sm:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pengguna</label>
                            <select v-model="filters.user_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Pengguna</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select v-model="filters.is_released" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Status</option>
                                <option value="1">Diberikan</option>
                                <option value="0">Menunggu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tombol Reset -->
                    <div class="flex-shrink-0 mt-2 lg:mt-0">
                        <button
                            type="button"
                            @click="resetFilters"
                            class="w-full lg:w-auto px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-2 mb-4">
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-2">
                            <p class="text-sm font-medium text-gray-600">Total Nominal</p>
                            <p class="text-xl font-bold text-gray-900">Rp {{ formatNumber(totals.total_amount) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-2">
                            <p class="text-sm font-medium text-gray-600">Diberikan</p>
                            <p class="text-xl font-bold text-gray-900">Rp {{ formatNumber(totals.total_released) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-2">
                            <p class="text-sm font-medium text-gray-600">Menunggu</p>
                            <p class="text-xl font-bold text-gray-900">Rp {{ formatNumber(totals.total_pending) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-gray-100 rounded-full">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-2">
                            <p class="text-sm font-medium text-gray-600">Total Inspek</p>
                            <p class="text-xl font-bold text-gray-900">{{ totals.total_records }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-4 col-span-2 sm:col-span-2 md:col-span-4 lg:col-span-1">
    <div class="flex items-center">
        <!-- Icon Tagihan -->
        <div class="p-2 bg-indigo-100 rounded-full">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M9 14h6m-6-4h6m2 10H7a2 2 0 01-2-2V6a2 2 0 012-2h3l1-1h2l1 1h3a2 2 0 012 2v12a2 2 0 01-2 2z" />
            </svg>
        </div>

        <!-- Info Tagihan -->
        <div class="ml-2">
            <p class="text-sm font-medium text-gray-600">Tagihan</p>
            <p class="text-xl font-bold text-gray-900">Rp {{ formatNumber(props.tagihan) }}</p>

            <!-- Link Detail -->
            <Link :href="route('tagihan.show')" class="text-sm text-indigo-600 hover:underline">
                Lihat detail →
            </Link>
        </div>
    </div>
</div>


            </div>

            <!-- Search & Export Row -->
            <div class="flex justify-between items-center mb-4">
                <!-- Search (kiri) -->
                <div class="w-full sm:w-2">
                    <input
                        type="text"
                        v-model="filters.search"
                        placeholder="Cari data..."
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm"
                    >
                </div>

                <!-- Export (kanan) -->
                <a
                    :href="exportUrl"
                    class="ml-2 px-6 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center focus:outline-none focus:ring-2 focus:ring-green-500"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    CSV
                </a>
            </div>


            <!-- Data Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <!-- Search Bar -->

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID Transaksi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status Transaksi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status Inspeksi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Penerima
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Wilayah
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jenis
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nominal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Persentase
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu Diberikan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Diberikan Oleh
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tombol
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="distribution in distributions.data" :key="distribution.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(distribution.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <!-- Invoice -->
                                    <div>{{ distribution.transaction.invoice_number }}</div>
                                    
                                    <!-- Plate number (lebih kecil & redup) -->
                                    <div class="text-xs text-gray-500">
                                        {{ distribution.transaction.inspection.plate_number }} 
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ distribution.transaction.inspection.car_name }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getStatusClass(distribution.transaction.status)">
                                        {{ translatedStatus(distribution.transaction.status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                     <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getStatusClass(distribution.transaction.inspection.status)">
                                        {{ translatedStatus(distribution.transaction.inspection.status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ distribution.user?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ distribution.region?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">
                                    {{ distribution.role_type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ formatNumber(distribution.amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ distribution.percentage }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="{
                                        'px-2 py-1 text-xs font-medium rounded-full': true,
                                        'bg-green-100 text-green-800': distribution.is_released,
                                        'bg-yellow-100 text-yellow-800': !distribution.is_released
                                    }">
                                        {{ distribution.is_released ? 'Diberikan' : 'Menunggu' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ distribution.released_at ? formatDate(distribution.released_at) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ distribution.released?.name || '-' }}
                                </td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a :href="route('finance.show', distribution.encrypted_id)" 
                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                    Lihat
                                    </a>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="distributions.data.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan {{ distributions.from }} hingga {{ distributions.to }} dari {{ distributions.total }} hasil
                        </div>
                        <div class="flex space-x-2">
                            <button
                                v-for="(link, index) in distributions.links"
                                :key="index"
                                @click="goToPage(link.url)"
                                :disabled="!link.url || link.active"
                                :class="{
                                    'px-3 py-1 text-sm rounded-md': true,
                                    'bg-indigo-600 text-white': link.active,
                                    'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50': !link.active && link.url,
                                    'text-gray-300 bg-gray-100 cursor-not-allowed': !link.url,
                                    'ml-2': index > 0
                                }"
                                v-html="link.label"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="distributions.data.length === 0" class="text-center py-12 bg-white shadow rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-4 text-gray-500">Tidak ada data distribusi pendapatan yang ditemukan.</p>
                <button
                    @click="resetFilters"
                    class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    Atur Ulang Filter
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router,Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { debounce } from 'lodash';

const props = defineProps({
    distributions: Object,
    regions: Array,
    users: Array,
    totals: Object,
    role: String,
    filters: Object,
    tagihan: Object
});

const filters = ref({
    region_id: props.filters?.region_id || '',
    user_id: props.filters?.user_id || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    is_released: props.filters?.is_released || '',
     search: props.filters?.search || ''   // <--- tambahin ini
});

// Menambahkan computed property untuk tanggal hari ini
const today = computed(() => {
    const d = new Date();
    const year = d.getFullYear();
    const month = (d.getMonth() + 1).toString().padStart(2, '0');
    const day = d.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
});

// Mengamati perubahan pada filter untuk menerapkan filter otomatis
watch(filters, debounce(() => {
    router.get(route('report.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['distributions', 'totals', 'filters','tagihan']
    });
}, 500), { deep: true });

const resetFilters = () => {
    filters.value = {
        region_id: '',
        user_id: '',
        date_from: '',
        date_to: '',
        is_released: '',
        search: '',
    };
};

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    Object.entries(filters.value).forEach(([key, value]) => {
        if (value) params.append(key, value);
    });
    return route('report.export') + '?' + params.toString();
});

const formatNumber = (number) => {
    return new Intl.NumberFormat('id-ID').format(number || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const goToPage = (url) => {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true, only: ['distributions', 'totals', 'filters'] });
};

const translatedStatus = computed(() => (status) => {
    switch (status) {
        // Inspection
        case 'draft':
            return 'Dibuat';
        case 'in_progress':
            return 'Dalam Proses';
        case 'pending':
            return 'Menunggu';
        case 'pending_review':
            return 'Menunggu Review';
        case 'approved':
            return 'Disetujui';
        case 'rejected':
            return 'Ditolak';
        case 'revision':
            return 'Revisi';
        case 'completed':
            return 'Selesai';
        case 'cancelled':
            return 'Dibatalkan';

        // Transaction
        case 'paid':
            return 'Sudah di bayar';
        case 'failed':
            return 'Gagal';
        case 'refunded':
            return 'Dikembalikan';
        case 'expired':
            return 'Kadaluarsa';
        case 'released':
            return 'Diberikan';

        default:
            return status;
    }
});

const getStatusClass = (status) => {
    switch (status) {
        // Hijau → sukses / selesai / dibayar / diberikan
        case 'completed':
        case 'released':
        case 'paid':
        case 'approved':
            return 'bg-green-100 text-green-800';

        // Biru → dalam proses
        case 'in_progress':
            return 'bg-blue-100 text-blue-800';

        // Abu-abu → draft / belum ada tindakan
        case 'draft':
            return 'bg-gray-100 text-gray-800';

        // Kuning → menunggu / pending
        case 'pending':
        case 'pending_review':
            return 'bg-yellow-100 text-yellow-800';

        // Oranye → revisi / unpaid
        case 'revision':
        case 'unpaid':
            return 'bg-orange-100 text-orange-800';

        // Merah → gagal / ditolak / dibatalkan / expired
        case 'failed':
        case 'rejected':
        case 'cancelled':
        case 'expired':
            return 'bg-red-100 text-red-800';

        // Biru muda untuk refunded misalnya
        case 'refunded':
            return 'bg-indigo-100 text-indigo-800';

        default:
            return 'bg-gray-100 text-gray-800';
    }
};


</script>

<style scoped>
/* Custom styles for better appearance */
table {
    border-collapse: separate;
    border-spacing: 0;
}

th {
    position: sticky;
    top: 0;
    background-color: #f9fafb;
    z-index: 10;
}

tr:hover {
    background-color: #f9fafb;
}

td, th {
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
}
</style>
