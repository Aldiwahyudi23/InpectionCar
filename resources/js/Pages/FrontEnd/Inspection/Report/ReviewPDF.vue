<template>
  <div class="p-6 bg-white rounded-lg shadow-md">
    <!-- Tombol Persetujuan Laporan -->
    <div v-if="inspection.status === 'pending_review'" class="mt-2 mb-6">
      <button 
        @click="showConfirmationModal = true"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
      >
        Setujui Laporan
      </button>
      <p class="text-sm text-gray-500 mt-2 italic">
        Catatan: Halaman ini hanya simulasi dan mungkin berbeda dengan tampilan file PDF. Silakan periksa lebih detail untuk memastikan tidak ada kesalahan. Jika sudah yakin, silakan setujui untuk proses selanjutnya.
      </p>
    </div>

    <!-- Konten Laporan -->
    <div v-if="hasDataOrImages">
      <div class="header flex flex-col gap-5 mb-6 mt-6">
        <div class="flex items-center gap-5">
          <!-- Gambar Utama -->
          <img 
            v-if="coverImage && imageExists(coverImage.image_path)"
            :src="getImageUrl(coverImage.image_path)" 
            alt="Foto Utama"
            class="w-40 h-40 object-cover rounded-lg border border-gray-300"
          >
          <div v-else class="w-40 h-40 border border-gray-300 rounded-lg flex items-center justify-center bg-gray-100 text-gray-400">
            <span>Gambar tidak tersedia</span>
          </div>
          <div class="mt-8">
            <h2 class="text-3xl font-bold m-0 text-gray-800">{{ inspection.car_name }}</h2>
          </div>
        </div>

        <!-- Tabel Informasi Mobil -->
        <div v-if="inspection.car_id" class="car-info">
          <table class="w-full border-collapse border border-gray-300 rounded-lg overflow-hidden">
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold w-1/3 text-gray-700">Nomor Polisi</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.plate_number }}</td>
            </tr>
            <tr>
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Merek</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.brand?.name }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Model</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.model?.name }}</td>
            </tr>
            <tr>
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Tipe</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.type?.name }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">CC</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.cc }}</td>
            </tr>
            <tr>
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Bahan Bakar</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.fuel_type }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Transmisi</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.transmission }}</td>
            </tr>
            <tr>
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Periode Model</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.production_period || '-' }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Tahun Pembuatan</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.year }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Warna</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.color }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">No Rangka</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.noka }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">No Mesin</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.nosin }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">KM</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.km }}</td>
            </tr>
          </table>
        </div>
        
        <!-- Kesimpulan Inspeksi -->
        <div v-if="inspection.notes" class="conclusion p-4 bg-gray-50 border-l-4 border-gray-800 rounded-lg">
          <h3 class="text-lg font-bold mb-2 text-gray-800">Kesimpulan Inspeksi:</h3>
          <p class="m-0 text-gray-600">
            <div v-html="inspection.notes || '-'"></div>

          </p>
        </div>
      </div>

      <h2 class="text-2xl font-bold border-b-2 border-gray-800 pb-2 mb-6">Hasil Inspeksi</h2>

      <!-- Loop untuk setiap komponen inspeksi -->
      <div v-for="(points, componentName) in groupedPoints" :key="componentName" 
          :class="['section mb-6', componentName === 'Foto Kendaraan' ? 'photo-component' : '']">
        
        <!-- Judul Komponen -->
        <div class="component-title bg-gray-100 px-3 py-2 border-l-4 border-gray-800 font-bold rounded-l-lg">
          {{ componentName || 'Tanpa Komponen' }}
        </div>

        <!-- Bagian Foto Kendaraan -->
        <div v-if="componentName === 'Foto Kendaraan'" class="images flex flex-wrap gap-2 mt-4">
          <div v-for="point in points" :key="point.id">
            <div v-for="img in point.inspection_point?.images" :key="img.id" class="image-container">
              <img 
                v-if="imageExists(img.image_path)"
                :src="getImageUrl(img.image_path)" 
                alt="Foto Kendaraan"
                class="w-28 h-28 object-cover border border-gray-300 rounded-lg"
              >
              <div v-else class="w-28 h-28 border border-gray-300 rounded-lg flex items-center justify-center bg-gray-100 text-xs text-gray-400">
                <span>Gambar tidak ditemukan</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Bagian Poin Inspeksi non-foto -->
        <template v-else>
          <div v-for="point in points" :key="point.id" class="point ml-4 py-3 border-b border-gray-100">
            <template v-if="hasResult(point) || hasImage(point)">
              <!-- Nama Poin Inspeksi -->
              <span class="point-name inline-block min-w-[160px] font-bold align-top text-gray-700">
                {{ point.inspection_point?.name || '-' }}
              </span>
              
              <div class="point-content inline-block w-[calc(100%-170px)] align-top">
                <template v-if="hasResult(point)">
                  <!-- Badge Status -->
                  <span v-if="shouldShowStatusBadge(point)" 
                    :class="['status-badge', getStatusClass(point.inspection_point?.results[0].status)]">
                    {{ point.inspection_point?.results[0].status }}
                  </span>
                  
                  <!-- Catatan (Note) -->
                  <div v-if="shouldShowNote(point)" class="point-note italic text-gray-600 my-1">
                    {{ formatNote(point) }}
                  </div>
                </template>
              </div>

              <!-- Gambar Poin Inspeksi -->
              <div v-if="shouldShowImages(point)" class="inspection-images flex flex-wrap gap-2 mt-4">
                <div v-for="img in point.inspection_point?.images" :key="img.id" class="image-container">
                  <img 
                    v-if="imageExists(img.image_path)"
                    :src="getImageUrl(img.image_path)" 
                    alt="image"
                    class="w-20 h-20 object-cover border border-gray-300 rounded-lg"
                  >
                  <div v-else class="w-20 h-20 border border-gray-300 rounded-lg flex items-center justify-center bg-gray-100 text-xs text-gray-400">
                    <span>Gambar tidak ditemukan</span>
                  </div>
                </div>
              </div>

              <!-- Catatan Textarea -->
              <div v-if="shouldShowTextarea(point) && hasNote(point)" class="textarea-note italic text-gray-600 my-2">
                {{ point.inspection_point?.results[0].note }}
              </div>
            </template>
          </div>
        </template>
      </div>
    </div>
    
    <!-- Tampilan Jika Data Tidak Ada -->
    <div v-else class="flex flex-col items-center justify-center h-96 text-center">
      <p class="text-lg font-semibold text-gray-700">
        Data sudah tidak ada, karena sudah dijadikan PDF.
      </p>
      <a 
        :href="route('inspections.download.pdf', encryptedIds)"
        class="mt-4 inline-block bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      >
        Download PDF
      </a>
    </div>

    <!-- Modal Konfirmasi -->
    <div v-if="showConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl w-96 max-w-sm">
        <h3 class="text-lg font-bold mb-4">Konfirmasi Laporan</h3>
        <p class="text-gray-700 mb-6">
          Apakah Anda yakin semua data sudah sesuai? Setelah disetujui, laporan akan diproses menjadi file PDF.
        </p>
        <div class="flex justify-end space-x-4">
          <button @click="showConfirmationModal = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">
            Batal
          </button>
          <button 
            v-if="!inspection.file" 
            @click="approveReport" 
            class="bg-sky-600 text-white px-4 py-2 rounded-lg hover:bg-sky-700 transition-colors duration-200" 
            :disabled="isLoading"
          >
            <span v-if="isLoading">Membuat file PDF...</span>
            <span v-else>Setujui Laporan</span>
          </button>
          <button v-else class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 cursor-not-allowed">
            <span>Laporan sudah di buat</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  inspection: {
    type: Object,
    required: true
  },
  menu_points: {
    type: Array,
    default: () => []
  },
  coverImage: {
    type: Object,
    default: null
  },
  encryptedIds: Object,
})

const showConfirmationModal = ref(false)
const isLoading = ref(false)

// reactive local state biar bisa diupdate saat polling
const inspection = ref(props.inspection)
const menuPoints = ref(props.menu_points)
const coverImage = ref(props.coverImage)

let pollingInterval = null

onMounted(() => {
  if (inspection.value.status === 'in_progress') {
    pollingInterval = setInterval(async () => {
      try {
        const response = await axios.get(
          route('inspections.review.pdf', props.encryptedIds) // pastikan ada route show detail inspection
        )
        inspection.value = response.data.inspection
        menuPoints.value = response.data.menu_points
        coverImage.value = response.data.coverImage
      } catch (error) {
        console.error('Gagal polling inspection:', error)
      }
    }, 5000) // 5 detik
  }
})

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval)
})

const groupedPoints = computed(() => {
  const groups = {}
  menuPoints.value.forEach(point => {
    const componentName = point.inspection_point?.component?.name || 'Tanpa Komponen'
    if (!groups[componentName]) {
      groups[componentName] = []
    }
    groups[componentName].push(point)
  })
  return groups
})


const approveReport = () => {
  isLoading.value = true;
  // Ini adalah simulasi proses
  setTimeout(() => {
    // Arahkan ke route untuk download PDF
    window.location.href = route('inspections.download.pdf', props.encryptedIds);
    // Setelah proses selesai (biasanya di-handle oleh redirect), atur ulang status
    isLoading.value = false;
    showConfirmationModal.value = false;
  }, 2000); // Simulasi loading 2 detik
};

const hasDataOrImages = computed(() => {
    // if (props.inspection.notes) {
    //     return true;
    // }
    // Menggunakan fungsi helper untuk memeriksa apakah ada data atau gambar di salah satu poin.
    const hasContent = props.menu_points.some(point => hasResult(point) || hasImage(point));
    
    // Pengecekan gambar utama
    const hasCoverImage = props.coverImage && props.coverImage.image_path;

    return hasContent || hasCoverImage;
});


// Helper functions
const imageExists = (imagePath) => imagePath && imagePath.length > 0;
const getImageUrl = (imagePath) => `/${imagePath}`;

// Mengakses data hasil dan gambar dari 'inspection_point' dengan aman
const hasResult = (point) => point.inspection_point?.results && point.inspection_point.results.length > 0;
const hasImage = (point) => point.inspection_point?.images && point.inspection_point.images.length > 0;
const hasNote = (point) => hasResult(point) && point.inspection_point.results[0].note;

const shouldShowStatusBadge = (point) => {
  const inputType = point.input_type || '';
  // Perbaikan: Menggunakan 'point.inspection_point'
  const status = point.inspection_point?.results?.[0]?.status;
  return ['radio', 'imageTOradio'].includes(inputType) && status;
};

const shouldShowNote = (point) => {
  const inputType = point.input_type || '';
  return ['text', 'number', 'date', 'textarea'].includes(inputType) && hasNote(point);
};

const shouldShowImages = (point) => {
  const inputType = point.input_type || '';
  const settings = point.settings || {};
  const result = point.inspection_point?.results?.[0] || {};
  const selectedOption = settings.radios?.find(radio => radio.value === result.status) || {};
  const showImageUpload = selectedOption.settings?.show_image_upload || false;

  return (
    (inputType === 'image' || 
      inputType === 'imageTOradio' || 
      (inputType === 'radio' && showImageUpload)) && 
    hasImage(point)
  );
};

const shouldShowTextarea = (point) => {
  const settings = point.settings || {};
  const result = point.inspection_point?.results?.[0] || {};
  const selectedOption = settings.radios?.find(radio => radio.value === result.status) || {};
  return selectedOption.settings?.show_textarea || false;
};

const getStatusClass = (status) => {
  if (!status) return 'status-warning';
  
  const statusLower = status.toLowerCase();
  if (['normal', 'ada', 'baik', 'good', 'ok'].includes(statusLower)) {
    return 'status-good';
  } else if (['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok'].includes(statusLower)) {
    return 'status-bad';
  }
  return 'status-warning';
};

const formatNote = (point) => {
  const inputType = point.input_type || '';
  const result = point.inspection_point?.results?.[0] || {};
  const settings = point.settings || {};

  if (inputType === 'account' && result.note) {
    // 1. Bersihkan string dari karakter non-angka
    const cleanedNote = result.note.replace(/[^\d.-]/g, '');
    
    // 2. Pastikan nilai adalah angka yang valid
    const value = parseFloat(cleanedNote);
    
    // Jika bukan angka, kembalikan nilai asli
    if (isNaN(value)) {
      return result.note;
    }

    // 3. Gunakan Intl.NumberFormat untuk format mata uang yang lebih baik
    const symbol = settings.currency_symbol || 'Rp';
    const formatter = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    });

    // Ambil string hasil format, lalu ganti simbol default
    const formattedValue = formatter.format(value).replace('Rp', symbol);
    return formattedValue;
  }

  // Jika inputType bukan 'account' atau tidak ada note, kembalikan note asli
  return result.note;
};
</script>

<style scoped>
.point-name {
  min-width: 160px;
}
.point-content {
  width: calc(100% - 170px);
}
.status-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 9999px; /* rounded-full */
  font-size: 12px;
  margin-right: 8px;
  margin-bottom: 10px;
}
.status-good {
  background-color: #d4edda;
  color: #155724;
}
.status-bad {
  background-color: #f8d7da;
  color: #721c24;
}
.status-warning {
  background-color: #fff3cd;
  color: #856404;
}
.photo-component .point {
  display: none;
}
.conclusion {
  margin-top: 20px;
  padding: 15px;
  background-color: #f9f9f9;
  border-left: 4px solid #333;
  border-radius: 4px;
}
.textarea-note {
  margin: 5px 0;
  font-style: italic;
  color: #555;
}
.car-info table {
  margin-top: 10px;
  border-collapse: collapse;
  width: 100%;
}
.car-info td {
  padding: 3px 6px;
  vertical-align: top;
  border: 1px solid #ddd;
}
.car-info td:first-child {
  width: 30%;
  font-weight: bold;
}
.component-title {
  font-weight: bold;
  font-size: 14px;
  margin-top: 15px;
  background-color: #f5f5f5;
  padding: 5px 10px;
  border-left: 3px solid #333;
}
.point {
  margin-left: 15px;
  margin-bottom: 10px;
  padding: 5px 0;
  border-bottom: 1px dotted #eee;
}
</style>
