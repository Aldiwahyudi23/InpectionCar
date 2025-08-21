<template>
  <div class="container mx-auto px-4 py-2">
    <div class="sticky top-0 z-10 bg-white shadow-sm mb-2">
      <div class="flex overflow-x-auto scrollbar-hide py-3 px-4 space-x-2">
        <!-- Menu Detail Kendaraan -->
        <button
          @click="changeCategory('vehicle')"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
          :class="{
            'bg-indigo-600 text-white': activeCategory === 'vehicle',
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
            'bg-indigo-600 text-white': activeCategory === menu.id,
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
            'bg-indigo-600 text-white': activeCategory === 'conclusion',
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== 'conclusion'
          }"
        >
          Kesimpulan
        </button>
      </div>
    </div>

    <div class="relative overflow-hidden">
      <transition name="category-slide" mode="out-in">
        <!-- Detail Kendaraan -->
        <VehicleDetails
        v-if="activeCategory === 'vehicle'"
          :inspection="inspection"
          :car="car"
          :brands="brands"
          :car-models="carModels"
          :car-types="carTypes"
          @update-vehicle="updateVehicleDetails"
        />

        <!-- Menu Inspeksi Biasa -->
        <category-section
          v-else-if="activeMenuData && activeCategory !== 'conclusion'"
          :key="activeMenuData.id"
          :category="activeMenuData"
          :inspection-id="inspection.id"
          :form="form"
          @saveResult="saveResult"
          @updateResult="updateResult"
          @removeImage="removeImage"
        />

        <!-- Kesimpulan -->
        <conclusion-section
          v-else-if="activeCategory === 'conclusion'"
          :form="form"
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
        class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>{{ form.processing ? 'Mengirim...' : 'Final Kirim Inspeksi' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import VehicleDetails from '@/Components/InspectionForm/VehicleDetails.vue';
import CategorySection from '@/Components/InspectionForm/CategorySection.vue';
import ConclusionSection from '@/Components/InspectionForm/ConclusionSection.vue';

const props = defineProps({
  inspection: Object,
  appMenu: Array, // Ganti categories menjadi appMenu
  existingResults: Array,
  existingImages: Object,
   car: Object, // Tambahkan prop car
  components: Array, // Terima props baru
});

// Akses inspection dari page props
const page = usePage();
const inspection = page.props.inspection;
const car = page.props.car; // Akses data mobil

// Ubah inisialisasi activeCategory untuk bisa menerima 'summary'
const activeCategory = ref(props.appMenu[0]?.id || 'summary'); // Default ke menu pertama atau 'summary'
const activeIndex = ref(0);
const categoriesWrapper = ref(null); 

// Properti terhitung untuk mendapatkan data menu aktif
const activeMenuData = computed(() => {
  return activeCategory.value !== 'summary' 
    ? props.appMenu.find(m => m.id === activeCategory.value)
    : null;
});

// Properti terhitung untuk mendapatkan daftar komponen unik
const uniqueComponents = computed(() => {
  return props.components || [];
});

// Inisialisasi form
const initializeForm = () => {
  const results = {};
  
  props.appMenu.forEach(menu => {
    (menu.points || []).forEach(point => {
      // Pastikan hanya point dengan input_type selain damage yang diproses
      if (point.input_type !== 'damage') {
        const existing = Array.isArray(props.existingResults)
          ? props.existingResults.find(r => r.point_id === point.id)
          : null;
        
        results[point.id] = {
          status: existing?.status || '',
          note: existing?.note || '',
          images: props.existingImages?.[point.id]?.map(img => ({
            image_path: img.image_path,
            preview: null
          })) || []
        };
      }
    });
  });
  
  return {
    inspection_id: props.inspection.id,
    results,
    overall_note: props.inspection.overall_note || '' 
  };
};

const form = useForm(initializeForm());

// Debounce untuk menyimpan catatan keseluruhan
const debounceSaveOverallNote = debounce(() => {
  router.post(route('inspections.save-overall-note'), {
    inspection_id: props.inspection.id,
    overall_note: form.overall_note,
  }, {
    preserveScroll: true,
    preserveState: true,
    only: ['inspection'],
    onSuccess: () => {
      // console.log('Catatan keseluruhan berhasil disimpan.');
    },
    onError: (errors) => {
      console.error('Error menyimpan catatan keseluruhan:', errors);
    }
  });
}, 800);

// Check if menu is complete
const isMenuComplete = (menu) => {
  return menu.points.every(point => {
    // Skip point dengan input_type damage
    if (point.input_type === 'damage') return true;
    
    const result = form.results[point.id];
    if (!result) return false;
    
    switch(point.input_type) {
      case 'text':
      case 'number':
      case 'date':
      case 'account':
      case 'textarea':
        return !!result.note;
      case 'select':
      case 'radio':
        return !!result.status;
      case 'image':
        return result.images?.length > 0;
      default:
        return !!result.status || !!result.note;
    }
  });
};

// Change active category
const changeCategory = (menuId) => {
  if (menuId === 'summary') {
    activeIndex.value = props.appMenu.length;
  } else {
    const index = props.appMenu.findIndex(m => m.id === menuId);
    if (index >= 0) {
      activeIndex.value = index;
    }
  }
  activeCategory.value = menuId;
};

// Navigate between menus
const navigate = (direction) => {
  let newIndex = activeIndex.value + direction;
  
  const maxIndex = props.appMenu.length;
  
  if (newIndex >= 0 && newIndex <= maxIndex) {
    activeIndex.value = newIndex;
    if (newIndex === maxIndex) {
      activeCategory.value = 'summary';
    } else {
      activeCategory.value = props.appMenu[newIndex].id;
    }
  }
};

// Handle swipe gestures
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
    if (touchEndX < touchStartX - 100) {
      navigate(1);
    } else if (touchEndX > touchStartX + 100) {
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
      onSuccess: () => {},
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


// Fungsi untuk update data kendaraan
const updateVehicleDetails = (vehicleData) => {
  // Kirim update ke server
  router.post(route('inspections.update-vehicle'), {
    inspection_id: inspection.id,
    ...vehicleData
  }, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Data kendaraan berhasil diupdate');
    }
  });
};

// Fungsi untuk update kesimpulan
const updateConclusion = (conclusionData) => {
  Object.assign(form.conclusion, conclusionData);
  saveConclusion();
};

// Simpan kesimpulan
const saveConclusion = debounce(() => {
  router.post(route('inspections.save-conclusion'), {
    inspection_id: inspection.id,
    ...form.conclusion
  }, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Kesimpulan berhasil disimpan');
    }
  });
}, 500);

// Final submit all
const submitAll = () => {
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


onMounted(() => {
  setupSwipe();
});

// Watcher untuk perubahan activeCategory untuk menggulir navigasi horizontal
watch(activeCategory, (newVal) => {
  const menuButton = document.querySelector(`.flex-shrink-0[data-category-id="${newVal}"]`);
  if (menuButton) {
    menuButton.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
  }
});

const allMenusComplete = computed(() => {
  return props.appMenu.every(menu => isMenuComplete(menu));
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

/* Transisi untuk conditional rendering (v-if) */
.category-slide-enter-active,
.category-slide-leave-active {
  transition: all 0.3s ease-out;
  position: absolute; 
  width: 100%; 
  top: 0;
  left: 0;
}
.category-slide-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.category-slide-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}
</style>