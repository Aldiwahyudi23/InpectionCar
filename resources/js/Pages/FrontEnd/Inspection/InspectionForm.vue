<template>
  <div class="container mx-auto px-4 py-2">
    <div class="sticky top-0 z-10 bg-white shadow-sm mb-2">
      <div class="flex overflow-x-auto scrollbar-hide py-3 px-4 space-x-2">
        <!-- Menu Detail Kendaraan -->
        <button
          @click="changeCategory('vehicle')"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
          :class="{
            'bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white': activeCategory === 'vehicle',
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== 'vehicle'
          }"
        >
          Detail Kendaraan 
        </button>

        <!-- Menu Inspeksi -->
        <button
          v-for="menu in appMenu"
          :key="menu.id"
          @click="changeCategory(menu.id)"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
          :class="{
            'bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white': activeCategory === menu.id,
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== menu.id
          }"
        >
          {{ menu.name }}
          <span 
            v-if="isMenuComplete(menu)"
            class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white"
          >
            ✓
          </span>
        </button>

        <!-- Menu Kesimpulan -->
        <button
          @click="changeCategory('conclusion')"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
          :class="{
            'bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white': activeCategory === 'conclusion',
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== 'conclusion'
          }"
        >
          Kesimpulan
          <span 
            v-if="conclusionStatus.isComplete"
            class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white"
          >
            ✓
          </span>
        </button>
      </div>
    </div>

    <!-- Pesan sukses -->
    <transition name="fade">
      <div
        v-if="successMessage"
        class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-400 text-white px-4 py-2 rounded shadow-lg z-50"
      >
        {{ successMessage }}
      </div>
    </transition>

    <div class="relative overflow-hidden">
      <transition name="category-slide" mode="out-in">
        <!-- Detail Kendaraan -->
        <VehicleDetails
          v-if="activeCategory === 'vehicle'"
          :inspection="inspection"
          :CarDetail="CarDetail"
          @update-vehicle="updateVehicleDetails"
          @save-car-details="saveNewCarDetails"
        />


        <!-- Menu Inspeksi Biasa -->
        <category-section
          v-else-if="activeMenuData && activeCategory !== 'conclusion'"
          :key="activeMenuData.id"
          :category="{
            ...activeMenuData,
            points: getVisiblePoints(activeMenuData.points || [], activeMenuData.input_type === 'damage')
          }"
          :inspection-id="inspection.id"
          :form="form"
          @saveResult="saveResult"
          @updateResult="updateResult"
          @hapusPoint="hapusData"
          @removeImage="removeImage"
        />

        <!-- Kesimpulan -->
        <conclusion-section
          v-else-if="activeCategory === 'conclusion'"
          :form="form"
          :inspection-id="inspection.id"
          :inspection="inspection"  
          :settings="inspection.settings || {}"
          @updateConclusion="updateConclusion"
        />
      </transition>
    </div>

    <!-- Tombol Simpan Final (hanya tampil di kesimpulan) -->
    <div v-if="activeCategory === 'conclusion'" class="flex justify-end gap-4 mt-8 p-6 bg-white rounded-xl shadow-md">
      <button
        type="button"
        @click="submitAll"
        :disabled="!allMenusComplete || form.processing"
        class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>{{ form.processing ? 'Mengirim...' : 'Final Kirim Inspeksi' }}</span>
      </button>
      
    </div>

    <!-- Floating Button untuk Akses Damage Points -->
    <button
      v-if="!showSearchModal && !showRadioModal"
      @click="showSearchModal = true"
      class="fixed bottom-4 right-4 z-20 p-4 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white rounded-full shadow-lg hover:bg-indigo-700 transition-colors"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
      </svg>
    </button>

    <!-- Modal Pencarian Damage Points -->
    <BottomSheetModal
      :show="showSearchModal"
      title="Cari Point Inspeksi"
      subtitle="Pilih point inspeksi untuk ditambahkan"
      @close="closeSearchModal"
    >
      <div class="space-y-4">
        <!-- Search Input -->
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari point inspeksi..."
            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>

        <!-- List Hasil Pencarian -->
        <div class="max-h-64 overflow-y-auto">
          <div v-if="filteredDamagePoints.length === 0" class="text-center py-4 text-gray-500">
            Tidak ada point yang ditemukan
          </div>
          
          <button
            v-for="point in filteredDamagePoints"
            :key="point.id"
            @click="selectPoint(point)"
            class="w-full text-left p-3 border-b border-gray-200 hover:bg-gray-50 transition-colors"
          >
            <div class="font-medium text-gray-900">{{ point.name }}</div>
            <div class="text-sm text-gray-500">{{ point.description }}</div>
            <div class="text-xs text-gray-400 mt-1">
              {{ getComponentName(point) }}
            </div>
            <!-- Tampilkan status jika sudah ada data -->
            <div v-if="hasPointData(point.id)" class="text-xs text-green-600 mt-1">
              ✓ Sudah ada data
            </div>
          </button>
        </div>
      </div>
    </BottomSheetModal>

    <!-- Modal Radio Option untuk Damage Points -->
    <RadioOptionModal
      v-if="showRadioModal"
      :key="selectedPoint?.id"
      :show="showRadioModal"
      :title="selectedPoint?.name || 'Detail Point'"
      :subtitle="selectedPoint?.description"
      :name="`point-${selectedPoint?.id}`"
      :inspection-id="inspection.id"
      :point-id="selectedPoint?.id"
      :settings="selectedPoint?.settings || {}"
      :options="selectedPoint?.settings?.radios || []"
      :selected-Point="selectedPoint"
      :selected-value="tempRadioValue"
      :images-value="tempImages"
      :notes-value="tempNotes"
      :point="selectedPoint"
      :existing-data="getExistingPointData(selectedPoint?.id)"
      @update:selectedValue="tempRadioValue = $event"
      @update:notesValue="tempNotes = $event"
      @update:imagesValue="tempImages = $event"
      @save="saveAllData"
      @close="closeRadioModal"
      @hapus="hapusData"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import VehicleDetails from '@/Components/InspectionForm/VehicleDetails.vue';
import CategorySection from '@/Components/InspectionForm/CategorySection.vue';
import ConclusionSection from '@/Components/InspectionForm/ConclusionSection.vue';
import RadioOptionModal from '@/Components/InspectionForm/RadioOptionModal.vue';
import BottomSheetModal from '@/Components/InspectionForm/BottomSheetModal.vue';

const props = defineProps({
  inspection: Object,
  appMenu: Array,
  existingResults: Array,
  existingImages: Object,
  CarDetail: Array,
  components: Array,
  damagePoints: Array,
});

// State untuk modal
const showSearchModal = ref(false);
const showRadioModal = ref(false);
const searchQuery = ref('');
const selectedPoint = ref(null);
const tempRadioValue = ref('');
const tempNotes = ref('');
const tempImages = ref([]);
const successMessage = ref('');

// Filter points untuk tampilan normal: sembunyikan damage points kecuali ada data
const getVisiblePoints = (points, isDamageMenu) => {
  return points.filter(point => {
    // Kalau menu bukan damage → semua point tampil
    if (!isDamageMenu) return true;

    // Kalau menu damage → point hanya tampil kalau ada data
    return hasPointData(point.id);
  });
};

// Gunakan damagePoints dari props
const allDamagePoints = computed(() => {
  return props.damagePoints || [];
});

// Filter damage points berdasarkan pencarian
const filteredDamagePoints = computed(() => {
  if (!searchQuery.value.trim()) {
    return allDamagePoints.value;
  }
  
  const query = searchQuery.value.toLowerCase().trim();
  return allDamagePoints.value.filter(point => 
    point.name?.toLowerCase().includes(query) ||
    point.description?.toLowerCase().includes(query) ||
    getComponentName(point)?.toLowerCase().includes(query)
  );
});

// Cek apakah point sudah memiliki data
const hasPointData = (pointId) => {
  const result = form.results[pointId];
  return result && (result.status || result.note || result.images?.length > 0);
};

// Ambil nama component berdasarkan point.component_id
const getComponentName = (point) => {
  const component = props.components.find(c => c.id === point.component_id);
  return component ? component.name : 'Komponen Tidak Diketahui';
};

// Get existing data untuk point
const getExistingPointData = (pointId) => {
  if (!pointId) return null;
  
  return Array.isArray(props.existingResults) 
    ? props.existingResults.find(r => r.point_id === pointId)
    : null;
};

// Pilih point dan buka modal
const selectPoint = (point) => {
  selectedPoint.value = point;

    // ambil nilai awal dari form.results untuk point tsb
  const existing = form.results[point.id] || {};
  tempRadioValue.value = existing.status || '';
  tempNotes.value = existing.note || '';
  tempImages.value = Array.isArray(existing.images) ? [...existing.images] : [];

  showSearchModal.value = false;
  showRadioModal.value = true;
};

// Handle save data dari modal
const saveAllData = () => {
  // Update form results
  if (selectedPoint.value) {
    form.results[selectedPoint.value.id] = {
      ...form.results[selectedPoint.value.id],
      status: tempRadioValue.value,
      note: tempNotes.value,
      images: Array.isArray(tempImages.value) ? [...tempImages.value] : []
    };
    
    // Kirim ke server
    saveResult(selectedPoint.value.id);
     // Tampilkan pesan sukses
    successMessage.value = "Data berhasil disimpan!";
    setTimeout(() => {
      successMessage.value = "";
    }, 1000);
  }
  
  closeRadioModal();
}; 

// Close modal pencarian
const closeSearchModal = () => {
  showSearchModal.value = false;
  searchQuery.value = '';
};

// Close modal radio
const closeRadioModal = () => {
  showRadioModal.value = false;
  selectedPoint.value = null;
  tempRadioValue.value = '';
  tempNotes.value = '';
  tempImages.value = [];
};

// Akses inspection dari page props
const page = usePage();
const inspection = page.props.inspection;

// Ubah inisialisasi activeCategory
const activeCategory = ref(props.appMenu[0]?.id || 'conclusion');
const activeIndex = ref(0);

// Properti terhitung untuk mendapatkan data menu aktif
const activeMenuData = computed(() => {
  return activeCategory.value !== 'conclusion' 
    ? props.appMenu.find(m => m.id === activeCategory.value)
    : null;
});

// Inisialisasi form

function initializeForm() {
  const results = {};
  const images = {}; // Objek terpisah untuk menyimpan data gambar
  
  // Proses semua points termasuk damage points
  props.appMenu.forEach(menu => {
    (menu.points || []).forEach(point => {
      // 1. Inisialisasi data untuk inspection_result
      const existingResult = Array.isArray(props.existingResults)
        ? props.existingResults.find(r => r.point_id === point.id)
        : null;
      
      results[point.id] = {
        status: existingResult?.status || '',
        note: existingResult?.note || '',
      };
      
      // 2. Inisialisasi data untuk inspection_image
      // Periksa apakah ada gambar yang sudah ada untuk point ini
      const existingImagesForPoint = props.existingImages?.[point.id] || [];
      images[point.id] = existingImagesForPoint.map(img => ({
        id: img.id, // Pastikan ID gambar disertakan jika ada
        image_path: img.image_path,
        // Properti lain yang diperlukan oleh komponen anak
        preview: `/${img.image_path}`, // Gunakan image_path untuk preview
        rotation: img.rotation || 0,
        isNew: false
      }));
    });
  });
  
  return {
    inspection_id: props.inspection.id,
    results,
    images, // Tambahkan objek 'images' ke dalam form
    overall_note: props.inspection.overall_note || ''
  };
}

const form = useForm(initializeForm());

// PERBAIKAN: Fungsi isMenuComplete yang benar
const isMenuComplete = (menu) => {
  // Kalau menu ini tipe damage → selalu dianggap complete
  if (menu.input_type === 'damage') {
    return true;
  }

  // Khusus untuk menu kesimpulan
  if (menu.id === 'conclusion') {
    return isConclusionComplete();
  }

  return menu.points.every(point => {
    const result = form.results[point.id];
    if (!result) return false;
    
    // Parse settings untuk point
    const settings = parseSettings(point.settings);
    
    switch(point.input_type) {
      case 'text':
      case 'number':
      case 'date':
      case 'account':
      case 'textarea':
        return !!result.note?.trim();
      
      case 'select':
      case 'radio':
        if (!result.status) return false;
        
        // Cek jika opsi yang dipilih memiliki requirements tambahan
        const selectedOption = settings.radios?.find(opt => opt.value === result.status);
        if (selectedOption?.settings) {
          // Validasi textarea jika required
          if (selectedOption.settings.show_textarea && selectedOption.settings.required && !result.note?.trim()) {
            return false;
          }
          // Validasi image jika required
          if (selectedOption.settings.show_image_upload && selectedOption.settings.required && result.images?.length === 0) {
            return false;
          }
        }
        return true;
      
      case 'imageTOradio':
        // Harus ada gambar DAN pilihan radio
        if (result.images?.length === 0 || !result.status) return false;
        
        // Cek requirements tambahan dari opsi yang dipilih
        const selectedOptionImage = settings.radios?.find(opt => opt.value === result.status);
        if (selectedOptionImage?.settings) {
          // Validasi textarea jika required
          if (selectedOptionImage.settings.show_textarea && selectedOptionImage.settings.required && !result.note?.trim()) {
            return false;
          }
        }
        return true;
      
      case 'image':
        return result.images?.length > 0;
      
      default:
        return !!result.status || !!result.note?.trim();
    }
  });  
};

// Fungsi parse settings
const parseSettings = (settings) => {
  if (!settings) return {};
  
  if (typeof settings === 'string') {
    try {
      return JSON.parse(settings) || {};
    } catch (e) {
      console.error('Error parsing settings JSON:', e);
      return {};
    }
  }
  
  if (typeof settings === 'object' && settings !== null) {
    return settings;
  }
  
  return {};
};

// Fungsi khusus untuk mengecek kelengkapan kesimpulan
const isConclusionComplete = () => {
  const settings = parseSettings(props.inspection.settings);
  const conclusionData = settings.conclusion || {};
  
  // Cek apakah semua field kesimpulan sudah diisi
  const hasFlooded = !!conclusionData.flooded;
  const hasCollision = !!conclusionData.collision;
  
  // Jika collision = 'yes', pastikan severity juga diisi
  const hasValidCollision = conclusionData.collision === 'yes' 
    ? !!conclusionData.collision_severity 
    : true;
  
  // Pastikan catatan kesimpulan juga diisi
  const hasConclusionNote = !!props.inspection.notes?.trim();
  
  return hasFlooded && hasCollision && hasValidCollision && hasConclusionNote;
};

// Computed property untuk status kesimpulan
const conclusionStatus = computed(() => {
  const settings = parseSettings(props.inspection.settings);
  const conclusionData = settings.conclusion || {};
  
  return {
    flooded: conclusionData.flooded || null,
    collision: conclusionData.collision || null,
    collision_severity: conclusionData.collision_severity || null,
    note: props.inspection.notes || null,
    isComplete: isConclusionComplete()
  };
});

// Change active category
const changeCategory = (menuId) => {
  activeCategory.value = menuId;
};

// Navigate between menus
const navigate = (direction) => {
  let newIndex = activeIndex.value + direction;
  
  const maxIndex = props.appMenu.length;
  
  if (newIndex >= 0 && newIndex <= maxIndex) {
    activeIndex.value = newIndex;
    activeCategory.value = props.appMenu[newIndex]?.id || 'conclusion';
  }
};

// Setup swipe gestures
const setupSwipe = () => {
  let touchStartX = 0;
  let touchEndX = 0;
  
  const handleTouchStart = (e) => {
    touchStartX = e.changedTouches[0].screenX;
  };
  
  const handleTouchEnd = (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  };
  
  const handleSwipe = () => {
    if (touchEndX < touchStartX - 150) {
      navigate(1);
    } else if (touchEndX > touchStartX + 150) {
      navigate(-1);
    }
  };
  
  const mainContentArea = document.querySelector('.relative.overflow-hidden'); 
  if (mainContentArea) {
    mainContentArea.addEventListener('touchstart', handleTouchStart, false);
    mainContentArea.addEventListener('touchend', handleTouchEnd, false);
  }
  
  return () => {
    if (mainContentArea) {
      mainContentArea.removeEventListener('touchstart', handleTouchStart);
      mainContentArea.removeEventListener('touchend', handleTouchEnd);
    }
  };
};

// Save single result
const saveResult = debounce(async (pointId) => {
  try {
    await router.post(route('inspections.save-result'), {
      inspection_id: props.inspection.id,
      point_id: pointId,
      status: form.results[pointId].status,
      note: form.results[pointId].note,
    }, {
      preserveScroll: true,
      preserveState: true,
      only: ['existingResults'], 
    });
  } catch (error) {
    console.error('Error menyimpan hasil:', error);
  }
}, 500); 

// Update result data
const updateResult = ({ pointId, type, value }) => {
  if (form.results[pointId].hasOwnProperty(type)) {
    form.results[pointId][type] = value;
  }
  saveResult(pointId);
};

const hapusData = async (pointId) => {
  if (!pointId) return;

  if (confirm("Apakah kamu yakin ingin menghapus data ini?")) {
    try {
      await router.post(route('inspections.delete-result'), {
        inspection_id: props.inspection.id,
        point_id: pointId,
      }, {
        preserveScroll: true,
        preserveState: true,
        only: ['existingResults'], 
        onSuccess: () => {
          if (form.results[pointId]) {
            delete form.results[pointId];
          }
          successMessage.value = "Data berhasil dihapus!";
          setTimeout(() => {
            successMessage.value = "";
          }, 2000);
        },
      });
    } catch (error) {
      console.error('Error menghapus hasil:', error);
    }

    closeRadioModal();
  }
};

// Fungsi untuk update kesimpulan
const updateConclusion = (conclusionData) => {
  Object.assign(form.conclusion, conclusionData);
  saveConclusion();
};


// Final submit all
const submitAll = () => {
  if (!allMenusComplete.value) {
    alert('Harap lengkapi semua menu inspeksi termasuk kesimpulan sebelum submit final');
    return;
  }

  form.post(route('inspections.final-submit', { 
    id: props.inspection.id 
  }), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect akan ditangani oleh controller
    },
    onError: (errors) => {
      console.error('Kesalahan pengiriman:', errors);
    }
  });
};

// Watcher untuk perubahan activeCategory
watch(activeCategory, (newVal) => {
  const menuButton = document.querySelector(`.flex-shrink-0[data-category-id="${newVal}"]`);
  if (menuButton) {
    menuButton.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
  }
});

const allMenusComplete = computed(() => {
  // 1. Cek semua menu regular dari appMenu
  const regularMenusComplete = props.appMenu.every(menu => isMenuComplete(menu));
  
  // 2. Cek kesimpulan
  const conclusionComplete = isConclusionComplete();
  
  return regularMenusComplete && conclusionComplete;
});

// Watcher untuk search query
watch(searchQuery, debounce(() => {
  // Filter akan dihandle oleh computed property
}, 300));

onMounted(() => {
  setupSwipe();
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Transisi untuk conditional rendering */
.category-slide-enter-active,
.category-slide-leave-active {
  transition: all 0.3s ease-out;
  position: absolute; 
  width: 100%; 
  top: 0;
  left: 0;
}
.category-slide-enter-from {
  transform: translateX(150%);
  opacity: 0;
}
.category-slide-leave-to {
  transform: translateX(-150%);
  opacity: 0;
}

/* Animasi fade */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>