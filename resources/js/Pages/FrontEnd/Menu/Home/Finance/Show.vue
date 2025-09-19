<script setup>
import { Head, Link ,router , usePage} from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {ref, computed } from 'vue';

const props = defineProps({
    distribution: Object,
    transaction: Object,
    distributionAll: Array,
    inspection: Object,
    inspectionId: Object,
    role: Object,
});

const page = usePage()
const user = page.props.auth.user; 


const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('id-ID').format(number || 0);
};

const translatedStatus = computed(() => (status) => {
    switch (status) {
        case 'completed':
            return 'Selesai';
        case 'in_progress':
            return 'Dalam Proses';
        case 'draft':
            return 'Draf';
        case 'Released':
        case 'released':
            return 'Diberikan';
        case 'Pending':
        case 'pending':
            return 'Menunggu';
        case 'paid':
            return 'Dibayar';
        case 'unpaid':
            return 'Belum Dibayar';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return status;
    }
});

const getStatusClass = (status) => {
    switch (status) {
        case 'completed':
        case 'released':
        case 'paid':
            return 'bg-green-100 text-green-800';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800';
        case 'draft':
            return 'bg-gray-100 text-gray-800';
        case 'pending':
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'unpaid':
            return 'bg-orange-100 text-orange-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getDistributionStatusClass = (isReleased) => {
    return isReleased ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
};


// State untuk modal konfirmasi dan loading
const showConfirmModal = ref(false);
const distributionToReleaseId = ref(null);
const isReleasing = ref(false);

// Fungsi untuk membuka modal konfirmasi
const openConfirmModal = (id) => {
    distributionToReleaseId.value = id;
    showConfirmModal.value = true;
};

// Fungsi untuk menutup modal
const closeModal = () => {
    showConfirmModal.value = false;
    distributionToReleaseId.value = null;
};

// Fungsi untuk menjalankan rilis setelah konfirmasi
const confirmRelease = () => {
    if (distributionToReleaseId.value) {
        isReleasing.value = true;
        router.put(route("distributions.release", distributionToReleaseId.value), {}, {
            onFinish: () => {
                isReleasing.value = false;
                closeModal();
            },
        });
    }
};


</script>

<template>
    <AppLayout title="Detail Distribusi">
        <Head title="Detail Distribusi" />

        <div class="w-full px-4 sm:px-6 lg:px-6 py-6 animate-fade-in">

            <!-- Header -->
            <div class="flex flex-col text-center mb-4">
                <h1 class="text-xl sm:text-xl font-bold text-gray-800">Detail Distribusi</h1>
                <p class="text-sm text-gray-500 mt-1">Informasi lengkap mengenai distribusi dana</p>
            </div>

            <!-- Main Distribution Details -->
            <div class="bg-white shadow-lg rounded-xl p-4 sm:p-6 space-y-2 border-t-4 border-indigo-500 transition-all duration-300 hover:shadow-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg sm:text-xl font-semibold text-indigo-700">Ringkasan Pembagian</h2>
                    <div class="px-3 py-1 rounded-full text-xs font-semibold" :class="getDistributionStatusClass(distribution.is_released)">
                        {{ distribution.is_released ? 'Diberikan' : 'Menunggu' }}
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Tanggal Distribusi</span>
                            <span class="text-gray-800 font-semibold">{{ formatDate(distribution.created_at) }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">ID Transaksi</span>
                            <span class="text-gray-800 font-semibold">{{ distribution.transaction?.invoice_number ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Nominal</span>
                            <span class="text-gray-800 font-semibold">Rp {{ formatNumber(distribution.amount) }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Persentase</span>
                            <span class="text-gray-800 font-semibold">{{ distribution.percentage }}%</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Diberikan Oleh</span>
                            <span class="text-gray-800 font-semibold">{{ distribution.released?.name ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Waktu Pemberian</span>
                            <span class="text-gray-800 font-semibold">{{ distribution.released_at ? formatDate(distribution.released_at) : '-' }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Catatan</span>
                            <span class="text-gray-800 font-semibold">{{ distribution.calculation_note ? distribution.calculation_note : 'Tidak ada catatan' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inspection & User Info Card -->
            <div class="bg-white shadow-lg rounded-xl p-4 sm:p-6 border-t-4 border-blue-500 transition-all duration-300 hover:shadow-xl">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Informasi Inspeksi & Pengguna</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Mobil</span>
                            <span class="text-gray-800 font-semibold">
                                {{ inspection.car?.brand?.name }} {{ inspection.car?.model?.name }} {{ inspection.car?.type?.name }} ({{ inspection.car?.year }})
                            </span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Nama Inspektor</span>
                            <span class="text-gray-800 font-semibold">{{ inspection.user?.name ?? '-' }}</span>
                                <!--  (lebih kecil & redup) -->
                                <div class="text-xs text-gray-500">
                                    {{ inspection.user?.numberPhone ?? '-' }} 
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ inspection.user?.roles[0]?.name ?? '-' }}
                                </div>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Dibuat Oleh</span>
                            <span class="text-gray-800 font-semibold">{{ inspection.submitted?.name ?? '-' }}</span>
                             <!--  (lebih kecil & redup) -->
                                <div class="text-xs text-gray-500">
                                    {{ inspection.user?.numberPhone ?? '-' }} 
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ inspection.user?.roles[0]?.name ?? '-' }}
                                </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Tanggal Inspeksi</span>
                            <span class="text-gray-800 font-semibold">{{ formatDate(inspection.inspection_date) }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Status Inspeksi</span>
                            <span class="px-3 py-1 mt-1 rounded-full text-xs font-semibold self-start" :class="getStatusClass(inspection.status)">
                                {{ translatedStatus(inspection.status) }}
                            </span>
                        </div>
                       <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-500">Status Inspeksi</span>
                            <span class="mt-1 self-start">
                                <a 
                                :href="route('inspections.review', inspectionId)"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition"
                                >
                                📄 Report
                                </a>
                            </span>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Customer & Transaction Info Card -->
            <div class="bg-white shadow-lg rounded-xl p-4 sm:p-6 border-t-4 border-green-500 transition-all duration-300 hover:shadow-xl">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Data Pelanggan & Transaksi</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <h3 class="text-md font-medium text-gray-700 border-b pb-2">Informasi Pelanggan</h3>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Nama Pelanggan</span>
                            <span class="text-gray-800 font-semibold">{{ inspection.customer?.name ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Telepon</span>
                            <span class="text-gray-800 font-semibold">{{ inspection.customer?.phone ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Email</span>
                            <span class="text-gray-800 font-semibold">{{ inspection.customer?.email ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Alamat</span>
                            <span class="text-gray-800 font-semibold">{{ inspection.customer?.address ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-md font-medium text-gray-700 border-b pb-2">Informasi Transaksi</h3>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Nominal Transaksi</span>
                            <span class="text-gray-800 font-semibold">Rp {{ formatNumber(transaction.amount) }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Tanggal Transaksi</span>
                            <span class="text-gray-800 font-semibold">{{ formatDate(transaction.created_at) }}</span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Status Transaksi</span>
                            <span class="px-3 py-1 mt-1 rounded-full text-xs font-semibold self-start" :class="getStatusClass(transaction.status)">
                                {{ translatedStatus(transaction.status) }}
                            </span>
                        </div>
                        <div class="flex flex-col p-1 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Pembayaran dilakukan oleh</span>
                            <span class="text-gray-800 font-semibold">{{ transaction.payer?.name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribution Detail Table -->
            <div v-if="role == 'Admin' || role == 'coordinator'" class="bg-white shadow-lg rounded-xl p-4 sm:p-6 border-t-4 border-yellow-500 overflow-x-auto transition-all duration-300 hover:shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Detail Pembagian Transaksi</h2>
                    <span class="text-sm text-gray-500">{{ distributionAll.length }} Penerima</span>
                </div>
                
                <div class="relative overflow-x-auto rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th scope="col" class="px-4 py-3">Penerima</th>
                                <th scope="col" class="px-4 py-3">Wilayah</th>
                                <th scope="col" class="px-4 py-3">Jenis</th>
                                <th scope="col" class="px-4 py-3 text-right">Nominal</th>
                                <th scope="col" class="px-4 py-3 text-center">Persentase</th>
                                <th scope="col" class="px-4 py-3 text-center">Status</th>
                                <th scope="col" class="px-4 py-3">Diberikan Oleh</th>
                                <th scope="col" class="px-4 py-3">Waktu Diberikan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(d, index) in distributionAll" :key="d.id" 
                                class="bg-white border-b hover:bg-gray-50 transition-colors duration-200"
                                :class="index % 2 === 0 ? '' : 'bg-gray-50'">
                                <td class="px-4 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ d.user?.name ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    {{ d.region?.name ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    {{ d.role_type }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right font-medium">
                                    Rp {{ formatNumber(d.amount) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    {{ d.percentage }}%
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="getDistributionStatusClass(d.is_released)">
                                        {{ d.is_released ? 'Diberikan' : 'Menunggu' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    {{ d.released?.name ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    {{ d.released_at ? formatDate(d.released_at) : '-' }}
                                </td>

                            <td v-if="d.transaction.paid_by == user.id || role == 'Admin'" class="px-4 py-4 whitespace-nowrap">
                            <!-- Jika role_type adalah Pendapatan -->
                            <template v-if="d.role_type === 'Pendapatan'">
                                <!-- Kalau user Admin -->
                                <template v-if="role == 'Admin'">
                                <button
                                    v-if="!d.is_released"
                                    @click="openConfirmModal(d.encrypted_id)"
                                    :disabled="isReleasing"
                                    class="flex items-center justify-center px-2 py-1 bg-indigo-600 text-white text-xs sm:text-sm rounded hover:bg-indigo-700 transition-colors disabled:bg-indigo-400 disabled:cursor-not-allowed"
                                >
                                    <svg
                                    v-if="isReleasing"
                                    class="animate-spin -ml-1 mr-1 h-3 w-3 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    />
                                    </svg>
                                    <span v-else>Kirim</span>
                                </button>
                                <span v-else class="text-green-600 text-xs sm:text-sm">
                                    ✔ Diberikan
                                </span>
                                </template>

                                <!-- Kalau user bukan Admin -->
                                <template v-else>
                                <span class="flex items-center text-red-600 text-xs sm:text-sm">
                                    ✖ Hanya Owner yang bisa konfirmasi
                                </span>
                                </template>
                            </template>

                            <!-- Kalau role_type bukan Pendapatan -->
                            <template v-else>
                                <button
                                v-if="!d.is_released"
                                @click="openConfirmModal(d.encrypted_id)"
                                :disabled="isReleasing"
                                class="flex items-center justify-center px-2 py-1 bg-indigo-600 text-white text-xs sm:text-sm rounded hover:bg-indigo-700 transition-colors disabled:bg-indigo-400 disabled:cursor-not-allowed"
                                >
                                <svg
                                    v-if="isReleasing"
                                    class="animate-spin -ml-1 mr-1 h-3 w-3 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                    />
                                    <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    />
                                </svg>
                                <span v-else>Kirim</span>
                                </button>
                                <span v-else class="text-green-600 text-xs sm:text-sm">
                                ✔ Diberikan
                                </span>
                            </template>
                            </td>


                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

                <!-- Confirmation Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl p-6 sm:p-8 max-w-sm w-full mx-4 transform transition-all scale-100 ease-out duration-300">
                <div class="flex flex-col items-center text-center">
                    <div class="bg-indigo-100 p-1 rounded-full mb-4">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Konfirmas</h3>
                    <p class="text-sm text-gray-600 mt-2">
                        Anda yakin ingin mengirim distribusi ini? ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="mt-6 flex justify-center space-x-4">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="confirmRelease"
                        :disabled="isReleasing"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition-colors disabled:bg-indigo-400 disabled:cursor-not-allowed flex items-center"
                    >
                        <span v-if="isReleasing" class="flex items-center">
                            <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                        <span v-else>Ya, Setor</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}

table th {
    @apply sticky top-0;
    z-index: 10;
}

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c5c5c5;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
