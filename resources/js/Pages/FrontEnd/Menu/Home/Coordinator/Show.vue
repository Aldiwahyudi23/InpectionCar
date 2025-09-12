<template>
    <AppLayout>
        <Head :title="`Detail Inspeksi - ${inspection.car.brand.name} ${inspection.car.model.name}`" />

        <div class="flex-1 overflow-y-auto bg-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Detail Inspeksi</h3>
                            <p class="text-sm text-gray-500">Informasi lengkap tentang inspeksi kendaraan.</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <span :class="`px-4 py-2 inline-flex text-sm leading-5 font-bold rounded-full ${statusClass(inspection.status)}`">
                                {{ formatStatus(inspection.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kendaraan</h4>
                    <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        <img class="h-24 w-24 rounded-lg object-cover shadow-md flex-shrink-0" :src="inspection.car.image || 'https://placehold.co/100x100/22c55e/ffffff?text=Mobil'" :alt="inspection.car.brand.name">
                        <div class="text-center sm:text-left">
                            <div class="text-xl font-bold text-gray-900">{{ inspection.car.brand.name }} {{ inspection.car.model.name }}</div>
                            <div class="text-sm text-gray-600">
                                {{ inspection.car.type.name }} | {{ inspection.car.year }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                Plat Nomor: <span class="font-medium text-gray-800">{{ inspection.plate_number }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-y-4 gap-x-6 mt-6 border-t pt-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">No. Mesin</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.nosin || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">No. Rangka</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.noka || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Warna</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.color || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Transmisi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.car.transmission }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Bahan Bakar</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.car.fuel_type }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">CC</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.car.cc }} cc</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Periode Produksi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.car.production_period }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Odometer</label>
                            <p class="mt-1 text-sm text-gray-900">{{ inspection.km ? `${inspection.km} km` : 'Belum tercatat' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 relative">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Inspektur</h4>
                    
                    <div class="flex items-center space-x-4">
                        <img class="h-16 w-16 rounded-full object-cover shadow flex-shrink-0" :src="inspection.user.profile_photo_url || 'https://placehold.co/48x48/22c55e/ffffff?text=USR'" :alt="inspection.user.name">
                        
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900">{{ inspection.user.name }}</div>
                            <div class="text-sm text-gray-500">{{ inspection.user.email }}</div>
                            <div class="text-sm text-gray-500">{{ inspection.user.numberPhone }}</div>
                        </div>
                    </div>
                    
                    <a
                        v-if="inspection.user.numberPhone"
                        :href="`https://wa.me/62${inspection.user.numberPhone}`"
                        target="_blank"
                        class="absolute bottom-6 right-6 text-green-500 hover:text-green-600 transition-colors"
                        aria-label="Hubungi Inspektur via WhatsApp"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.04 2.167c-5.462 0-9.917 4.455-9.917 9.916 0 1.583.376 3.107 1.077 4.485l-1.161 4.24 4.321-1.139a9.854 9.854 0 0 0 4.298 1.056c5.46 0 9.914-4.453 9.914-9.913s-4.454-9.915-9.914-9.915zm4.84 14.73c-.15.223-.523.292-.79.358-.266.066-.62.099-.967.126-.347.027-1.127.054-2.147-.393-1.02-.448-1.742-1.282-2.13-1.898-.386-.615-.812-1.637-.456-2.585.356-.948 1.144-1.258 1.393-1.464.25-.206.536-.292.834-.292.3.001.594-.099.86-.099.266 0 .47.009.682.045.212.036.467.247.66.757.2.51.688 1.637.747 1.765.059.128.094.275.028.468-.066.193-.362.593-.523.702-.16.11-.322.22-.48.329-.158.109-.323.238-.426.377-.103.139-.214.33-.06.6.155.27.425.432.748.552.324.12.656.168.99.25.334.08.665.178.966.262.3.084.577.162.77.25.192.088.356.168.487.24.131.071.257.147.34.19.083.04.168.105.28.188.112.083.272.253.377.425.105.171.18.34.25.503.07.163.14.33.19.5.05.173.085.34.103.5.018.16.035.32.035.597 0 .167-.008.31-.027.442-.02.132-.045.242-.08.332-.034.09-.07.167-.105.23-.035.063-.122.14-.2.203-.078.063-.173.123-.28.188-.108.065-.25.127-.4.188-.15.061-.31.115-.487.168z"/>
                        </svg>
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4" v-if="inspection.notes">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Catatan Tambahan</h4>
                    <p class="mt-1 text-sm text-gray-900 leading-relaxed whitespace-pre-wrap">
                         <div v-html="inspection.notes || '-'"></div>
                    </p>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-4">
                    <h2 class="text-lg font-semibold mb-4">Riwayat Proses</h2>
                    <div v-if="inspection.logs.length > 0" class="relative border-l-2 border-gray-200 pl-6">
                        <div
                            v-for="log in [...inspection.logs].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))"
                            :key="log.id"
                            class="mb-6 relative"
                        >
                            <div class="absolute -left-[11px] top-1 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow"></div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-sm text-gray-900">
                                    <span class="font-bold">{{ log.user?.name ?? 'Sistem' }}</span> {{ log.description }}
                                </p>
                                <small class="text-gray-500">{{ formatDateTime(log.created_at) }}</small>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 text-sm italic">Belum ada riwayat proses yang tersedia.</div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <div class="text-sm text-gray-500">
                        Terakhir diperbarui: <span class="font-medium text-gray-700">{{ formatDateTime(inspection.updated_at) }}</span>
                    </div>
                    <div class="flex flex-wrap justify-center sm:justify-end gap-3">
                        <button
                            v-if="inspection.status === 'draft' || inspection.status === 'pending'"
                            @click="handleAction('assign')"
                            class="btn-primary bg-green-600 hover:bg-green-700"
                        >
                            Tugaskan Inspektur
                        </button>
                        <button
                            v-if="inspection.status === 'pending_review'"
                            @click="handleAction('approve')"
                            class="btn-primary bg-green-600 hover:bg-green-700"
                        >
                            Setujui
                        </button>
                        <button
                            v-if="inspection.status === 'pending_review'"
                            @click="handleAction('revision')"
                            class="btn-primary bg-orange-600 hover:bg-orange-700"
                        >
                            Minta Revisi
                        </button>
                        <button
                            v-if="inspection.status === 'in_progress'"
                            @click="handleAction('complete')"
                            class="btn-primary bg-blue-600 hover:bg-blue-700"
                        >
                            Tandai Selesai
                        </button>
                        <button
                            v-if="inspection.status !== 'rejected' && inspection.status !== 'cancelled' && inspection.status !== 'completed'"
                            @click="handleAction('reject')"
                            class="btn-primary bg-red-600 hover:bg-red-700"
                        >
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            </div>

    </AppLayout>
</template>


<style scoped>
.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 0.75rem;
    line-height: 1;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>

<script setup>
import { ref, defineProps } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    inspection: {
        type: Object,
        required: true
    }
});

const showModal = ref(false);
const modalMessage = ref('');
const modalAction = ref('');

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
        'draft': 'Dibuat',
        'in_progress': 'Dalam Proses',
        'pending': 'Tertunda',
        'pending_review': 'Menunggu Tinjauan',
        'approved': 'Disetujui',
        'rejected': 'Ditolak',
        'revision': 'Revisi',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan'
    };
    return statusMap[status] || status;
};

const statusClass = (status) => {
    const classMap = {
        'draft': 'bg-gray-100 text-gray-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'pending_review': 'bg-purple-100 text-purple-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'revision': 'bg-orange-100 text-orange-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
};

const getItemStatusText = (status) => {
    const statusMap = {
        'good': 'Baik',
        'fair': 'Wajar',
        'poor': 'Buruk',
        'damaged': 'Rusak',
        'not_checked': 'Belum Diperiksa'
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
    let message = '';
    if (action === 'assign') {
        message = 'Apakah Anda yakin ingin menugaskan inspeksi ini?';
    } else if (action === 'complete') {
        message = 'Apakah Anda yakin ingin menyelesaikan inspeksi ini?';
    } else if (action === 'approve') {
        message = 'Apakah Anda yakin ingin menyetujui inspeksi ini?';
    } else if (action === 'revision') {
        message = 'Apakah Anda yakin ingin meminta revisi untuk inspeksi ini?';
    } else if (action === 'reject') {
        message = 'Apakah Anda yakin ingin menolak inspeksi ini?';
    }
    modalMessage.value = message;
    modalAction.value = action;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    modalMessage.value = '';
    modalAction.value = '';
};

const confirmAction = () => {
    let status = '';
    if (modalAction.value === 'assign') {
        status = 'in_progress';
    } else if (modalAction.value === 'complete') {
        status = 'completed';
    } else if (modalAction.value === 'approve') {
        status = 'approved';
    } else if (modalAction.value === 'revision') {
        status = 'revision';
    } else if (modalAction.value === 'reject') {
        status = 'rejected';
    }

    router.post(route('coordinator.inspections.update-status', props.inspection.id), { status }, {
        onSuccess: () => {
            router.reload();
        },
        preserveScroll: true
    });
    closeModal();
};
</script>