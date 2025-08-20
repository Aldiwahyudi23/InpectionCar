<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { CalendarDaysIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'
import { CarIcon } from 'lucide-vue-next'

// props
defineProps({
  inspection: {
    type: Object,
    required: true
  }
})

// Mapping status ke label bahasa Indonesia
const statusLabel = (status) => {
  switch (status) {
    case 'draft':
      return 'Draft'
    case 'in_progress':
      return 'Sedang Berjalan'
    case 'pending_review':
      return 'Menunggu Review'
    case 'revisi':
      return 'Perlu Revisi'
    case 'jeda':
      return 'Ditunda'
    default:
      return status
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Review Inspeksi" />

    <div class="py-6 md:py-10">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6 text-center">
          Review Pemeriksaan
        </h1>

        <div
          class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200"
        >
          <!-- Jadwal -->
          <div class="p-4">
            <div class="flex items-center mb-1">
              <CalendarDaysIcon class="h-5 w-5 text-blue-500 mr-2" />
              <span class="text-sm font-medium text-gray-600">Jadwal</span>
            </div>
            <p class="text-sm font-semibold text-blue-700 ml-7 -mt-1">
              {{
                new Date(inspection.inspection_date).toLocaleDateString('id-ID', {
                  weekday: 'short',
                  year: 'numeric',
                  month: 'short',
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                })
              }}
            </p>
          </div>

          <!-- Mobil -->
          <div
            v-if="inspection.car"
            class="px-4 py-3 bg-gray-50 border-t border-gray-100"
          >
            <div class="flex items-center">
              <CarIcon class="h-5 w-5 text-gray-500 mr-2" />
              <div class="text-sm font-medium text-gray-800">
                {{
                  `${inspection.car.brand?.name} ${inspection.car.model?.name} ${inspection.car.type?.name} ${inspection.car.cc} ${inspection.car.transmission} ${inspection.car.year}`
                }}
                <span class="text-gray-600">
                  ({{ inspection.car.fuel_type }})
                </span>
              </div>
            </div>
          </div>

          <!-- Kategori -->
          <div
            v-if="inspection.category"
            class="px-4 py-2 bg-white border-t border-gray-100"
          >
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
              Kategori
            </p>
            <p class="text-sm text-gray-800">{{ inspection.category.name }}</p>
          </div>

          <!-- Status & Catatan -->
          <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center mb-1">
              <ClipboardDocumentListIcon class="h-5 w-5 text-green-500 mr-2" />
              <span class="text-sm font-medium text-gray-600">Status</span>
            </div>
            <p class="text-sm font-semibold text-gray-800 ml-7 -mt-1">
              {{ statusLabel(inspection.status) }}
            </p>
            <div class="mt-3">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                Catatan
              </p>
              <p class="text-sm text-gray-800">
                {{ inspection.notes ?? '-' }}
              </p>
            </div>
          </div>

          <!-- Tombol PDF -->
          <div class="p-4">
            <Link
              :href="route('inspections.review.pdf', inspection.id)"
              class="inline-flex items-center justify-center w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-sm transition-colors"
            >
              Lihat Laporan PDF
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
