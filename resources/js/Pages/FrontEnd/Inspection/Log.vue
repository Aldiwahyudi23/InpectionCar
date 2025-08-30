<script setup>
import dayjs from 'dayjs'
import AppLayout from '@/Layouts/AppLayout.vue';
defineProps({ inspection: Object })

const formatDate = (date) => dayjs(date).format('DD/MM/YYYY HH:mm')

// Mapping status ke bahasa Indonesia
const statusMap = {
  draft: "Draft",
  in_progress: "Sedang Berjalan",
  pending: "Menunggu",
  pending_review: "Menunggu Review",
  approved: "Disetujui",
  rejected: "Ditolak",
  revision: "Revisi",
  completed: "Selesai",
  cancelled: "Dibatalkan",
}
</script>

<template>
  <AppLayout>
    <Head title="Review Inspeksi" />
    <div class="p-4">
      <h1 class="text-xl font-bold mb-4">Detail Inspection</h1>

      <!-- Detail inspection info -->
      <div class="mb-6 border rounded p-3 bg-white shadow-sm">
        <p><b>Plat Nomor:</b> {{ inspection.plate_number }}</p>
        <p>
          <b>Status:</b>
          <span class="px-2 py-1 rounded text-sm font-medium"
            :class="{
              'bg-yellow-100 text-yellow-800': inspection.status === 'pending' || inspection.status === 'pending_review',
              'bg-blue-100 text-blue-800': inspection.status === 'in_progress',
              'bg-green-100 text-green-800': inspection.status === 'approved' || inspection.status === 'completed',
              'bg-red-100 text-red-800': inspection.status === 'rejected' || inspection.status === 'cancelled',
              'bg-gray-100 text-gray-800': inspection.status === 'draft' || inspection.status === 'revision',
            }"
          >
            {{ statusMap[inspection.status] ?? inspection.status }}
          </span>
        </p>
        <p><b>Dibuat Pada:</b> {{ formatDate(inspection.created_at) }}</p>
      </div>

      <!-- Logs -->
      <h2 class="text-lg font-semibold mb-4">Riwayat Proses</h2>
      <div v-if="inspection.logs.length > 0" class="relative border-l-2 border-gray-300 pl-6">
        <div v-for="log in [...inspection.logs].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))"
             :key="log.id"
             class="mb-6 relative">
          <!-- Titik bulat -->
          <div class="absolute -left-[11px] top-1 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow"></div>

          <div class="bg-white rounded-lg shadow p-3">
            <p class="font-medium">
              {{ log.user?.name ?? 'System' }} {{ log.description }}
            </p>
            <small class="text-gray-500">{{ formatDate(log.created_at) }}</small>
          </div>
        </div>
      </div>
      <div v-else class="text-gray-500">Belum ada log</div>
    </div>
  </AppLayout>
</template>
