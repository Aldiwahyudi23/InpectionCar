<template>
  <AppLayout>
    <Head title="Review Inspeksi" />

    <div class="py-6 md:py-10">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl md:text-3xl font-bold text-indigo-900 mb-6 text-center">
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
          <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center">
              <CarIcon class="h-5 w-5 text-gray-500 mr-2" />
              <div class="text-sm font-medium text-gray-800">
                <div v-if="inspection.car">
                  {{ `${inspection.car.brand.name} ${inspection.car.model.name} ${inspection.car.type.name} ${inspection.car.cc} ${inspection.car.transmission} ${inspection.car.year}` }}
                  <span class="text-gray-600">({{ inspection.car.fuel_type }})</span>
                </div>
                <div v-else>
                  {{ inspection.car_name }}
                </div>
              </div>
            </div>
            <!-- Nomor Plat Mobil -->
            <div class="flex items-center mt-2">
              <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 mr-2">NO POLISI:</span>
              <span class="text-sm font-bold text-gray-900">{{ inspection.plate_number }}</span>
            </div>
          </div>

          <!-- Kategori -->
          <div
            v-if="inspection.category"
            class="px-4 py-2 bg-white border-t border-gray-100"
          >
            <p class="text-xs font-medium text-gray-500 tracking-wide">
              Kategori Inspeksi
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

          <!-- Tombol Aksi -->
          <div class="p-4 flex gap-2">
            <!-- Tampilan tombol untuk status 'pending_review' -->
            <template v-if="inspection.status === 'pending_review'">
              <!-- Tombol Revisi (lebih pendek) -->
              <button 
                @click="showRevisionModal = true"
                class="flex-2 px-3 py-2 bg-yellow-500 text-white font-medium rounded-md text-sm transition-colors hover:bg-yellow-600"
              >
                Revisi
              </button>
              <!-- Tombol Lihat Laporan PDF -->
              <Link
                :href="route('inspections.review.pdf', encryptedIds)"
                class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white font-medium rounded-md text-sm transition-colors hover:from-indigo-800 hover:to-sky-700"
              >
                Lihat Laporan PDF
              </Link>
            </template>

            <!-- Tampilan tombol untuk status 'approved' -->
            <template v-else-if="inspection.status === 'approved' && inspection.file">
               <!-- Tombol Download (jika sudah ada file) -->
            <a
              v-if="inspection.file"
              :href="route('inspections.download.approved.pdf', encryptedIds)"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white font-medium rounded-md text-sm transition-colors hover:bg-blue-700"
                download
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Download PDF
            </a>
              <!-- Tombol Kirim Email -->
              <button
                @click="showEmailModal = true"
                class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-teal-500 text-white font-medium rounded-md text-sm transition-colors hover:bg-teal-600"
              >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                Kirim Via Email
              </button>

              
            </template>

          </div>
          <!-- Tampilan default (tombol lebar penuh) -->
          <template v-if="inspection.status === 'approved' && inspection.file">
            <Link
              :href="route('inspections.review.pdf', encryptedIds)"
              class="inline-flex items-center justify-center w-full px-3 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white font-medium rounded-md text-sm transition-colors hover:from-indigo-800 hover:to-sky-700"
            >
              Lihat Laporan
            </Link>
          </template>
        </div>
      </div>
    </div>
    
    <!-- Modal Konfirmasi Revisi -->
    <div v-if="showRevisionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <h3 class="text-lg font-bold mb-4">Konfirmasi Revisi</h3>
        <p class="text-gray-700 mb2">
          Apakah Anda yakin ingin revisi pada laporan ini?
        </p>
        <p class="text-gray-700 mb-4">Setelah ini akan masuk ke halaman Inspeksi.</p>
        <div class="flex justify-end space-x-4">
          <button @click="showRevisionModal = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Batal
          </button>
          <Link
            :href="route('inspections.revisi', encryptedIds)"
            class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600"
          >
            Ya, Revisi 
          </Link>
        </div>
      </div>
    </div>
    
    <!-- Modal Kirim Email -->
    <div v-if="showEmailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <h3 class="text-lg font-bold mb-4">Kirim Laporan Via Email</h3>
        <p class="text-gray-700 mb-2">
          Masukkan alamat email tujuan:
        </p>
        <input 
          type="email" 
          v-model="emailAddress" 
          class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="contoh@email.com"
        />
        <div class="flex justify-end space-x-4 mt-6">
          <button @click="showEmailModal = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Batal
          </button>
          <button @click="sendEmail" disabled class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-600">
            Masih dalam pengembangan 
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { CalendarDaysIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'
import { CarIcon } from 'lucide-vue-next'
import { ref } from 'vue'

// props
defineProps({
  inspection: {
    type: Object,
    required: true
  },
  encryptedIds: Object
})

// state untuk modal
const showRevisionModal = ref(false)
const showEmailModal = ref(false)
const emailAddress = ref('')

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
    case 'approved':
      return 'Disetujui'
    default:
      return status
  }
}

// Fungsi untuk mengirim email
const sendEmail = () => {
  if (emailAddress.value) {
    // Di sini Anda bisa memanggil route atau API untuk mengirim email
    // Contoh:
    // router.post(route('inspections.send.email', props.encryptedIds), {
    //   email: emailAddress.value
    // }).then(() => {
    //   showEmailModal.value = false
    //   emailAddress.value = ''
    //   alert('Laporan berhasil dikirim via email!')
    // })

    // // Untuk demo, kita tutup modal saja
    // showEmailModal.value = false
    // emailAddress.value = ''
    // // Anda bisa tambahkan notifikasi sukses di sini
    // alert('Laporan berhasil dikirim ke ' + emailAddress.value)
  }
}
</script>