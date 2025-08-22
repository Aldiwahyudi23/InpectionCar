<template>
  <div class="container mx-auto px-4 py-2">
    <div class="sticky top-0 z-10 bg-white shadow-sm mb-2">
      <div class="flex overflow-x-auto scrollbar-hide py-3 px-4 space-x-2">
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
        
        <button
          @click="changeCategory('additional')"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
          :class="{
            'bg-indigo-600 text-white': activeCategory === 'additional',
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== 'additional'
          }"
        >
          Tambahan
          <span
            v-if="isOtherComplete"
            class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white"
          >
            ✓
          </span>
        </button>

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
        <VehicleDetails
          v-if="activeCategory === 'vehicle'"
          :inspection="inspection"
          :car="car"
          :brands="brands"
          :car-models="carModels"
          :car-types="carTypes"
          @update-vehicle="updateVehicleDetails"
        />

        <category-section
          v-else-if="activeMenuData && activeCategory !== 'additional' && activeCategory !== 'conclusion'"
          :key="activeMenuData.id"
          :category="activeMenuData"
          :inspection-id="inspection.id"
          :form="form"
          @saveResult="saveResult"
          @updateResult="updateResult"
          @removeImage="removeImage"
        />
        
        <InputOther
            v-else-if="activeCategory === 'additional'"
            :form="form"
            @updateData="updateOtherData"
        />

        <conclusion-section
          v-else-if="activeCategory === 'conclusion'"
          :form="form"
          @updateConclusion="updateConclusion"
        />
      </transition>
    </div>

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
    
 <button
      v-if="!showBottomSheetModal"
      @click="showBottomSheetModal = true"
      class="fixed bottom-4 right-4 z-20 p-4 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition-colors"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
      </svg>
    </button>
    
    <BottomSheetModal 
      :show="showBottomSheetModal"
      @close="showBottomSheetModal = false"
      @trigger-camera="captureFromCamera"
      @trigger-file-input="selectFromGallery"
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
import InputOther from '@/Components/InspectionForm/InputOther.vue'; // Komponen baru
import BottomSheetModal from '@/Components/InspectionForm/BottomSheetModal.vue';

const props = defineProps({
  inspection: Object,
  appMenu: Array,
  existingResults: Array,
  existingImages: Object,
  car: Object,
  components: Array,
});

const page = usePage();
const inspection = page.props.inspection;
const car = page.props.car;

const activeCategory = ref(props.appMenu[0]?.id || 'vehicle');
const activeIndex = ref(0);
const categoriesWrapper = ref(null);

const activeMenuData = computed(() => {
  return activeCategory.value !== 'vehicle' && activeCategory.value !== 'additional' && activeCategory.value !== 'conclusion'
    ? props.appMenu.find(m => m.id === activeCategory.value)
    : null;
});

const uniqueComponents = computed(() => {
  return props.components || [];
});

const initializeForm = () => {
  const results = {};
  props.appMenu.forEach(menu => {
    (menu.points || []).forEach(point => {
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

  // Tambahkan data untuk menu 'Tambahan'
  const additionalData = Array.isArray(props.existingResults)
    ? props.existingResults.filter(r => r.point_type === 'additional')
    : [];

  return {
    inspection_id: props.inspection.id,
    results,
    overall_note: props.inspection.overall_note || '',
    additional_data: additionalData
  };
};

const form = useForm(initializeForm());

const debounceSaveOverallNote = debounce(() => {
  router.post(route('inspections.save-overall-note'), {
    inspection_id: props.inspection.id,
    overall_note: form.overall_note,
  }, {
    preserveScroll: true,
    preserveState: true,
    only: ['inspection'],
    onSuccess: () => {},
    onError: (errors) => { console.error('Error menyimpan catatan keseluruhan:', errors); }
  });
}, 800);

const isMenuComplete = (menu) => {
  return menu.points.every(point => {
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

// Logika baru untuk memeriksa kelengkapan menu 'Tambahan'
const isOtherComplete = computed(() => {
    return form.additional_data && form.additional_data.length > 0;
});

const changeCategory = (menuId) => {
  if (menuId === 'vehicle') {
    activeIndex.value = -1; // Indeks khusus untuk menu kendaraan
  } else if (menuId === 'additional') {
    activeIndex.value = props.appMenu.length;
  } else if (menuId === 'conclusion') {
    activeIndex.value = props.appMenu.length + 1;
  } else {
    const index = props.appMenu.findIndex(m => m.id === menuId);
    if (index >= 0) {
      activeIndex.value = index;
    }
  }
  activeCategory.value = menuId;
};

const navigate = (direction) => {
  // Logic navigasi di sini
};

const setupSwipe = () => {
  // Logic swipe di sini
};

const saveResult = debounce(async (pointId) => {
  // Logic simpan hasil di sini
});

const updateResult = ({ pointId, type, value }) => {
  // Logic update hasil di sini
};

const updateVehicleDetails = (vehicleData) => {
  // Logic update kendaraan di sini
};

const updateConclusion = (conclusionData) => {
  // Logic update kesimpulan di sini
};

const saveConclusion = debounce(() => {
  // Logic simpan kesimpulan di sini
});

const updateOtherData = ({ pointId, value }) => {
  // Logika untuk menambahkan/memperbarui data dari InputOther
  const existingIndex = form.additional_data.findIndex(item => item.point_id === pointId);
  if (existingIndex !== -1) {
    form.additional_data[existingIndex].value = value;
  } else {
    form.additional_data.push({ point_id: pointId, value: value, point_type: 'additional' });
  }
  saveOtherData(form.additional_data[existingIndex] || form.additional_data.at(-1));
};

const saveOtherData = debounce(async (data) => {
  // Logic kirim data tambahan ke server
  try {
    await router.post(route('inspections.save-other-data'), {
      inspection_id: props.inspection.id,
      ...data
    }, {
      preserveScroll: true,
      preserveState: true,
      only: ['existingResults'],
      onSuccess: () => { console.log('Data tambahan berhasil disimpan.'); },
    });
  } catch (error) {
    console.error('Error menyimpan data tambahan:', error);
  }
}, 500);


const submitAll = () => {
  form.post(route('inspections.final-submit', { id: props.inspection.id }), {
    preserveScroll: true,
    onSuccess: () => {},
    onError: (errors) => { console.error('Kesalahan pengiriman:', errors); }
  });
};

const openAddOtherModal = () => {
  // Anda bisa memicu modal di sini. Karena kode ini berada di Parent
  // maka Anda bisa langsung membuka modal jika state modal ada di sini.
  // Contoh: showBottomSheetModal.value = true;
  // Atau, Anda bisa memicu event ke parent yang lebih tinggi:
  // Contoh: emit('openBottomSheet');
};

onMounted(() => {
  setupSwipe();
});

watch(activeCategory, (newVal) => {
  const menuButton = document.querySelector(`.flex-shrink-0[data-category-id="${newVal}"]`);
  if (menuButton) {
    menuButton.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
  }
});

const allMenusComplete = computed(() => {
  return isOtherComplete.value && props.appMenu.every(menu => isMenuComplete(menu));
});

// State untuk mengontrol tampilan modal
const showBottomSheetModal = ref(false);
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