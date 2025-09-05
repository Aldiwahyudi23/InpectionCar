<template>
    <AppLayout>
        <Head :title="`Detail Inspeksi - ${inspection.car.brand.name} ${inspection.car.model.name}`" />

        <div class="flex-1 overflow-y-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="space-y-6 bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Informasi Inspeksi</h3>
                        </div>
                        <div class="flex items-center space-x-3">
                           
                            <span :class="`px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full ${statusClass(inspection.status)}`">
                                {{ formatStatus(inspection.status) }}
                            </span>
                        </div>
                    </div>

                    <div class="px-2 py-2 sm:p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Detail Mobil</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-16 w-16">
                                    <img class="h-16 w-16 rounded-md object-cover" :src="inspection.car.image || 'https://placehold.co/100x100/22c55e/ffffff?text=Mobil'" :alt="inspection.car.brand.name">
                                </div>
                                <div class="ml-4">
                                    <div class="text-lg font-medium text-gray-900">{{ inspection.car.brand.name }} {{ inspection.car.model.name }} {{ inspection.car.type.name }}</div>
                                    <div class="text-sm text-gray-500">Plat Nomor: {{ inspection.plate_number }}</div>
                                    <div class="text-sm text-gray-500">Tahun: {{ inspection.car.year }}</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Transmisi</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ inspection.car.transmission }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jenis Bahan Bakar</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ inspection.car.fuel_type }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">CC</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ inspection.car.cc }} cc</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Periode Produksi</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ inspection.car.production_period }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-2 py-2 sm:p-6 bg-gray-50 rounded-md">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Informasi Inspektur</h4>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                <img class="h-12 w-12 rounded-full object-cover" :src="inspection.user.profile_photo_url || 'https://placehold.co/48x48/22c55e/ffffff?text=USR'" :alt="inspection.user.name">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ inspection.user.name }}</div>
                                <div class="text-sm text-gray-500">{{ inspection.user.email }}</div>
                                <div class="text-sm text-gray-500">{{ inspection.user.phone_number }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="px-2 py-2 sm:p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Detail Inspeksi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal & Waktu Inspeksi</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(inspection.inspection_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pembacaan Odometer</label>
                                <p class="mt-1 text-sm text-gray-900">{{ inspection.odometer_reading ? `${inspection.odometer_reading} km` : 'Tidak tercatat' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dibuat Pada</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(inspection.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-2 py-2 sm:p-6 bg-gray-50 rounded-md" v-if="inspection.items && inspection.items.length > 0">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Hasil Inspeksi</h4>
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Item</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Kondisi</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Catatan</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="(item, index) in inspection.items" :key="index">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            {{ item.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ item.condition || 'Tidak Tersedia' }}
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            {{ item.notes || 'Tidak ada catatan' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <span :class="`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${getItemStatusClass(item.status)}`">
                                                {{ getItemStatusText(item.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="px-2 py-2 sm:p-6" v-if="inspection.notes && inspection.notes.length > 0">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Catatan Tambahan</h4>
                        <div class="space-y-4">
                            <div v-for="(note, index) in inspection.notes" :key="index" class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-900">{{ note.user?.name || 'Sistem' }}</span>
                                    <span class="text-sm text-gray-500">{{ formatDateTime(note.created_at) }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ note.content }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-2 py-2 sm:px-6 bg-gray-50 rounded-md flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Terakhir diperbarui: {{ formatDateTime(inspection.updated_at) }}
                        </div>
                        <div class="flex space-x-3">
                            <button
                                v-if="inspection.status === 'draft'"
                                @click="handleAction('assign')"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Tugaskan Inspektur
                            </button>
                            <button
                                v-if="inspection.status === 'in_progress'"
                                @click="handleAction('complete')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Tandai Selesai
                            </button>
                             <button
                                v-if="inspection.status !== 'rejected'"
                                @click="handleAction('reject')"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Tolak Inspeksi
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-6 bg-white rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold mb-4">Riwayat Proses</h2>
                    <div v-if="inspection.logs.length > 0" class="relative border-l-2 border-gray-300 pl-6">
                        <div
                            v-for="log in [...inspection.logs].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))"
                            :key="log.id"
                            class="mb-6 relative"
                        >
                            <div class="absolute -left-[11px] top-1 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow"></div>
                            
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="font-medium">
                                    <span class="font-bold">{{ log.user?.name ?? 'Sistem' }}</span> {{ log.description }}
                                </p>
                                <small class="text-gray-500">{{ formatDateTime(log.created_at) }}</small>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500">Belum ada log.</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { defineProps } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    inspection: {
        type: Object,
        required: true
    }
});

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('id-ID', options);
};

const formatStatus = (status) => {
    const statusMap = {
        'pending': 'Pending',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'rejected': 'Rejected'
    };
    return statusMap[status] || status;
};

const statusClass = (status) => {
    const classMap = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800'
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
};

const getItemStatusText = (status) => {
    const statusMap = {
        'good': 'Good',
        'fair': 'Fair',
        'poor': 'Poor',
        'damaged': 'Damaged',
        'not_checked': 'Not Checked'
    };
    return statusMap[status] || status;
};

const getItemStatusClass = (status) => {
    const classMap = {
        'good': 'bg-green-100 text-green-800',
        'fair': 'bg-yellow-100 text-yellow-800',
        'poor': 'bg-orange-100 text-orange-800',
        'damaged': 'bg-red-100 text-red-800',
        'not_checked': 'bg-gray-100 text-gray-800'
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
};

const handleAction = (action) => {
    let confirmMessage = '';
    let status = '';

    if (action === 'assign') {
        confirmMessage = 'Apakah Anda yakin ingin menugaskan inspektur ini?';
        status = 'in_progress';
    } else if (action === 'complete') {
        confirmMessage = 'Apakah Anda yakin ingin menyelesaikan inspeksi ini?';
        status = 'completed';
    } else if (action === 'reject') {
        confirmMessage = 'Apakah Anda yakin ingin menolak inspeksi ini?';
        status = 'rejected';
    }

    if (confirm(confirmMessage)) {
        router.post(route('coordinator.inspections.update-status', props.inspection.id), { status }, {
            onSuccess: () => {
                router.reload();
            },
            preserveScroll: true
        });
    }
};
</script>