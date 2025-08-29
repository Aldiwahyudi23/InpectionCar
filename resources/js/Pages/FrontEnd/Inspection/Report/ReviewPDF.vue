<template>
  <div class="p-6 bg-white">
    <div v-if="inspection.status === 'pending_review'" class="mt-2 mb-6">
      <button 
        @click="showConfirmationModal = true"
        class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      >
        Setujui Laporan
      </button>
      <p class="text-sm text-gray-500 mt-2 italic">
        Catatan: Halaman ini hanya simulasi dan mungkin berbeda dengan tampilan file PDF. Silakan periksa lebih detail untuk memastikan tidak ada kesalahan. Jika sudah yakin, silakan setujui untuk proses selanjutnya.
      </p>
    </div>

    <div class="header flex flex-col gap-5 mb-6 mt-6">
      <div class="flex items-center gap-5">
        <img 
          v-if="coverImage && imageExists(coverImage.image_path)"
          :src="getImageUrl(coverImage.image_path)" 
          alt="Foto Utama"
          class="w-40 h-40 object-cover border border-gray-300"
        >
        <div v-else class="w-40 h-40 border border-gray-300 flex items-center justify-center">
          <span>Gambar tidak tersedia</span>
        </div>

        <div class="mt-8">
          <h2 class="text-xl font-bold m-0">{{ inspection.car_name }}</h2>
        </div>
      </div>

      <div v-if="inspection.car_id" class="car-info">
        <table class="w-full border-collapse border border-gray-300">
          <tr>
            <td class="p-2 border border-gray-300 font-bold w-1/3">Nomor Polisi</td>
            <td class="p-2 border border-gray-300">{{ inspection.plate_number }}</td>
          </tr>
          <tr>
            <td class="p-2 border border-gray-300 font-bold">Merek</td>
            <td class="p-2 border border-gray-300">{{ inspection.car?.brand?.name }}</td>
          </tr>
          <tr>
            <td class="p-2 border border-gray-300 font-bold">Model</td>
            <td class="p-2 border border-gray-300">{{ inspection.car?.model?.name }}</td>
          </tr>
          <tr>
            <td class="p-2 border border-gray-300 font-bold">Tipe</td>
            <td class="p-2 border border-gray-300">{{ inspection.car?.type?.name }}</td>
          </tr>
          <tr>
            <td class="p-2 border border-gray-300 font-bold">CC</td>
            <td class="p-2 border border-gray-300">{{ inspection.car?.cc }}</td>
          </tr>
          <tr>
            <td class="p-2 border border-gray-300 font-bold">Bahan Bakar</td>
            <td class="p-2 border border-gray-300">{{ inspection.car?.fuel_type }}</td>
          </tr>
          <tr>
            <td class="p-2 border border-gray-300 font-bold">Transmisi</td>
            <td class="p-2 border border-gray-300">{{ inspection.car?.transmission }}</td>
          </tr>
          <tr>
            <td class="p-2 border border-gray-300 font-bold">Periode Model</td>
            <td class="p-2 border border-gray-300">{{ inspection.car?.production_period || '-' }}</td>
          </tr>
          <tr>
            <td class="p-2 border border-gray-300 font-bold">Tahun Pembuatan</td>
            <td class="p-2 border border-gray-300">{{ inspection.car?.year }}</td>
          </tr>
        </table>
      </div>
      
      <div v-if="inspection.notes" class="conclusion p-4 bg-gray-50 border-l-4 border-gray-800 rounded">
        <h3 class="text-lg font-bold mb-2">Kesimpulan Inspeksi:</h3>
        <p class="m-0">{{ inspection.notes }}</p>
      </div>
    </div>

    <h2 class="text-xl font-bold border-b-2 border-gray-800 pb-2 mb-6">Hasil Inspeksi</h2>

    <div v-for="(points, componentName) in groupedPoints" :key="componentName" 
        :class="['section mb-6', componentName === 'Foto Kendaraan' ? 'photo-component' : '']">
      
      <div class="component-title bg-gray-100 px-3 py-2 border-l-4 border-gray-800 font-bold">
        {{ componentName || 'Tanpa Komponen' }}
      </div>

      <div v-if="componentName === 'Foto Kendaraan'" class="images flex flex-wrap gap-2 mt-4">
        <div v-for="point in points" :key="point.id">
          <div v-for="img in point.images" :key="img.id" class="image-container">
            <img 
              v-if="imageExists(img.image_path)"
              :src="getImageUrl(img.image_path)" 
              alt="Foto Kendaraan"
              class="w-28 h-28 object-cover border border-gray-300 rounded"
              
            >
            <div v-else class="w-28 h-28 border border-gray-300 rounded flex items-center justify-center">
              <span class="text-xs">Gambar tidak ditemukan</span>
            </div>
          </div>
        </div>
      </div>

      <template v-else>
        <div v-for="point in points" :key="point.id" class="point ml-4 py-3 border-b border-gray-100">
          
          <template v-if="!hasResult(point) && !hasImage(point)">
            </template>
          
          <template v-else>
            <span class="point-name inline-block min-w-40 font-bold align-top">
              {{ point.name || '-' }}
            </span>
            
            <div class="point-content inline-block w-calc-[100%-170px] align-top">
              
              <template v-if="hasResult(point)">
                <span v-if="shouldShowStatusBadge(point)" 
                      :class="['status-badge', getStatusClass(point.results[0].status)]">
                  {{ point.results[0].status }}
                </span>
                
                <div v-if="shouldShowNote(point)" class="point-note italic text-gray-600 my-1">
                  {{ formatNote(point) }}
                </div>
              </template>
            </div>

            <div v-if="shouldShowImages(point)" class="inspection-images flex flex-wrap gap-2 mt-4">
              <div v-for="img in point.images" :key="img.id" class="image-container">
                <img 
                  v-if="imageExists(img.image_path)"
                  :src="getImageUrl(img.image_path)" 
                  alt="image"
                  class="w-20 h-20 object-cover border border-gray-300 rounded"
                >
                <div v-else class="w-20 h-20 border border-gray-300 rounded flex items-center justify-center">
                  <span class="text-xs">Gambar tidak ditemukan</span>
                </div>
              </div>
            </div>

            <div v-if="shouldShowTextarea(point) && hasNote(point)" class="textarea-note italic text-gray-600 my-2">
              {{ point.results[0].note }}
            </div>
          </template>
        </div>
      </template>
    </div>

    <div v-if="showConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <h3 class="text-lg font-bold mb-4">Konfirmasi Laporan</h3>
        <p class="text-gray-700 mb-6">
          Apakah Anda yakin semua data sudah sesuai? Setelah disetujui, laporan akan diproses menjadi file PDF.
        </p>
        <div class="flex justify-end space-x-4">
          <button @click="showConfirmationModal = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Batal
          </button>

          <button v-if="!inspection.file" @click="approveReport" class="bg-sky-600 text-white px-4 py-2 rounded hover:bg-sky-700" :disabled="isLoading">
            <span v-if="isLoading">Membuat file PDF...</span>
            <span v-else>Setujui Laporan</span>
          </button>
          <button v-else class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            <span >Laporan sudah di buat</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  inspection: {
    type: Object,
    required: true
  },
  inspection_points: {
    type: Array,
    default: () => []
  },
  coverImage: {
    type: Object,
    default: null
  },
  encryptedIds: Object,
});

const showConfirmationModal = ref(false);
const isLoading = ref(false);

const groupedPoints = computed(() => {
  const groups = {};
  props.inspection_points.forEach(point => {
    const componentName = point.component?.name || 'Tanpa Komponen';
    if (!groups[componentName]) {
      groups[componentName] = [];
    }
    groups[componentName].push(point);
  });
  return groups;
});

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

// Helper functions (tetap sama)
const imageExists = (imagePath) => imagePath && imagePath.length > 0;
const getImageUrl = (imagePath) => `/${imagePath}`;
const hasResult = (point) => point.results && point.results.length > 0;
const hasImage = (point) => point.images && point.images.length > 0;
const hasNote = (point) => hasResult(point) && point.results[0].note;

const shouldShowStatusBadge = (point) => {
  const inputType = point.input_type || '';
  return ['radio', 'imageTOradio'].includes(inputType) && hasResult(point) && point.results[0].status;
};

const shouldShowNote = (point) => {
  const inputType = point.input_type || '';
  return ['text', 'number', 'date', 'textarea'].includes(inputType) && hasNote(point);
};

const shouldShowImages = (point) => {
  const inputType = point.input_type || '';
  const settings = point.settings || {};
  const result = point.results?.[0] || {};
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
  const result = point.results?.[0] || {};
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
  const result = point.results?.[0] || {};
  const settings = point.settings || {};
  
  if (inputType === 'account' && result.note) {
    const symbol = settings.currency_symbol || 'Rp';
    const thousand = settings.thousands_separator || '.';
    const decimal = settings.decimal_separator || ',';
    
    return `${symbol} ${Number(result.note).toLocaleString('id-ID', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).replace(/,/g, decimal).replace(/\./g, thousand)}`;
  }
  
  return result.note;
};
</script>

<style scoped>
/* Pastikan style CSS sesuai dengan Tailwind CSS */
.point-name {
  min-width: 160px;
}
.point-content {
  width: calc(100% - 170px);
}
.status-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 3px;
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
.images {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 15px;
}
.images img {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border: 1px solid #ddd;
  border-radius: 3px;
}
.photo-component .point {
  display: none;
}
.inspection-images {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 15px;
}
.inspection-images img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border: 1px solid #ddd;
  border-radius: 3px;
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