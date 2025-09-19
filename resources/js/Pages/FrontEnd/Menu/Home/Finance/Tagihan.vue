<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    transactions: Array,
    totalPendingAmount: Number,
    groupedByRoleType: Object,
    role: Object,
});

// State untuk modal konfirmasi dan loading
const showConfirmModal = ref(false);
const distributionToReleaseId = ref(null);
const isReleasing = ref(false);

// format angka ke Rupiah
const formatNumber = (num) => {
    if (!num) return '0';
    return new Intl.NumberFormat('id-ID').format(num);
};

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
    <AppLayout title="Daftar Tagihan">
        <Head title="Daftar Tagihan" />

        <div class="w-full px-4 sm:px-6 lg:px-6 py-6">
            <h1 class="text-xl font-bold text-gray-800 mb-4">Daftar Tagihan</h1>

            <!-- Summary Total -->
            <div class="bg-white shadow rounded-lg p-4 mb-3">
                <p class="text-sm font-medium text-gray-600">Total Tagihan Pending</p>
                <p class="text-2xl font-bold text-red-600">
                    Rp {{ formatNumber(props.totalPendingAmount) }}
                </p>
                <div v-if="props.role == 'Admin' || props.role == 'coordinator'">
                    <div v-for="(total, roleType) in props.groupedByRoleType" :key="roleType">
                        <p>{{ roleType }}: Rp {{ formatNumber(Number(total)) }}</p>
                    </div>
                </div>
            </div>

            <!-- List Transaksi -->
            <div class="space-y-4">
                <div
                    v-for="transaction in props.transactions"
                    :key="transaction.id"
                    class="bg-white shadow rounded-lg p-4"
                >
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="font-semibold text-gray-800">
                            Transaksi {{ transaction.invoice_number }}
                        </h2>
                        <span class="text-sm text-gray-500">Total Pending:
                            Rp {{ formatNumber(transaction.distributions.reduce((sum, d) => sum + Number(d.amount), 0)) }}
                        </span>
                    </div>

                    <div v-if="props.role == 'Admin' || props.role == 'coordinator' ">
                        <!-- Distribusi Pending -->
                        <div v-if="transaction.distributions.length > 0" class="border-t pt-2">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="text-gray-600">
                                        <th class="py-1">Nominal</th>
                                        <th class="py-1">Status</th>
                                        <th class="py-1">Tujuan</th>
                                        <th class="py-1">Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="dist in transaction.distributions"
                                        :key="dist.id"
                                        class="border-t"
                                    >
                                        <td class="py-1">Rp {{ formatNumber(dist.amount) }}</td>
                                        <td class="py-1 text-yellow-600 font-medium">
                                            {{ dist.is_released ? 'Diterima' : 'Belum diKirim'}}
                                        </td>
                                        <td class="py-1">{{ dist.user ? dist.user.name : 'Owner' }}</td>
                                        <td class="py-1 flex items-center space-x-2">
                                            <Link
                                                :href="route('finance.show', dist.id)"
                                                class="text-indigo-600 hover:underline text-xs sm:text-sm"
                                            >
                                                Detail
                                            </Link>
                                            <button
                                                v-if="!dist.is_released"
                                                @click="openConfirmModal(dist.id)"
                                                :disabled="isReleasing"
                                                class="flex items-center justify-center px-2 py-1 bg-indigo-600 text-white text-xs sm:text-sm rounded hover:bg-indigo-700 transition-colors disabled:bg-indigo-400 disabled:cursor-not-allowed"
                                            >
                                                <svg v-if="isReleasing" class="animate-spin -ml-1 mr-1 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span v-else>Kirim</span>
                                            </button>
                                            <span v-else class="text-green-600 text-xs sm:text-sm">✔ Diberikan</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-sm text-green-600">Semua distribusi sudah released ✅</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl p-6 sm:p-8 max-w-sm w-full mx-4 transform transition-all scale-100 ease-out duration-300">
                <div class="flex flex-col items-center text-center">
                    <div class="bg-indigo-100 p-3 rounded-full mb-4">
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
