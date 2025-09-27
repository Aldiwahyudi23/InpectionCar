<template>
  <!-- Header dengan Menu Navigasi -->
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
     <!-- overlay redup -->
  <div
    v-if="damageDragging || menuDragging"
    class="fixed inset-0 bg-black bg-opacity-40 z-30 pointer-events-none transition-opacity duration-200"
  ></div>
   
    <!-- Tombol toggle HANYA muncul kalau vertical -->
     <button
    v-if="menuMode === 'vertical'"
    @mousedown="startToggleLongPress"
    @mouseup="stopToggleDrag"
    @mouseleave="cancelToggleLongPress"
    @mousemove="onToggleDrag"
    @touchstart="startToggleLongPress"
    @touchend="stopToggleDrag"
    @touchmove="onToggleDrag"
    @click="handleToggleClick"
    class="fixed z-40 p-4 bg-gradient-to-r from-indigo-700 to-sky-600 text-white border shadow-lg rounded-full hover:shadow-xl transition"
    :style="{ left: togglePos.x + 'px', top: togglePos.y + 'px' }"
    aria-label="Toggle vertical menu"
  >
    <template v-if="!isMenuOpen">
      <!-- icon buka -->
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" stroke-width="2">
        <path fill-rule="evenodd" d="M3 5h14v2H3V5zm0 4h10v2H3V9zm0 4h6v2H3v-2z" clip-rule="evenodd" />
      </svg>
    </template>
    <template v-else>
      <!-- icon tutup -->
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" stroke-width="2">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
      </svg>
    </template>
      </button>

    <!-- Horizontal menu -->
    <div
      v-if="menuMode === 'horizontal'"
      class="z-10 bg-white shadow-sm"
      :class="{
        'sticky top-0 mb-2': menuPosition === 'top',
        'fixed bottom-0 left-0 right-0': menuPosition === 'bottom',
      }"
    >
      <div class="flex overflow-x-auto scrollbar-hide py-3 px-4">
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
              <span 
                v-if="isVehicleFormComplete"
                class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white"
              >
                ✓
              </span>
            </button>
              
            <!-- Menu Inspeksi -->
            <button
              v-for="menu in appMenus"
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

    <!-- Vertical menu overlay -->
    <div v-if="menuMode === 'vertical' && isMenuOpen" class="fixed inset-0 z-30">
      <!-- overlay -->
      <div class="absolute inset-0 bg-black bg-opacity-30" @click="closeMenu"></div>

      <!-- panel -->
      <div
        class="absolute z-40 w-60 bg-white rounded-xl shadow-xl p-2 max-h-[75vh] overflow-y-auto"
        :class="{
          'top-16 right-4': menuPosition === 'top-right',
          'top-16 left-4': menuPosition === 'top-left',
          'bottom-16 right-4': menuPosition === 'bottom-right',
          'bottom-16 left-4': menuPosition === 'bottom-left',
        }"
      >
        <!-- header -->
        <div class="flex items-center justify-between px-2 py-1 border-b">
          <div class="text-sm font-semibold">Pilih Menu</div>
          <button @click="closeMenu" class="text-xs text-gray-500 hover:text-gray-700">Tutup</button>
        </div>

          <div class="mt-2 space-y-2">
          <!-- Vehicle -->
          <button
            @click="changeCategory('vehicle'); closeMenu()"
            :class="menuButtonClass(activeCategory === 'vehicle')"
            class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm"
          >
            <span>Detail Kendaraan</span>
            <span v-if="isVehicleFormComplete" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white">✓</span>
          </button>

          <!-- regular menus -->
          <button
            v-for="menu in appMenus"
            :key="`vmenu-${menu.id}`"
            @click="() => { changeCategory(menu.id); closeMenu(); }"
            :class="[menuButtonClass(activeCategory === menu.id), 'w-full flex items-center justify-between px-3 py-2 rounded-md text-sm']"
          >
            <span>{{ menu.name }}</span>
            <span v-if="isMenuComplete(menu)" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white">✓</span>
          </button>

          <!-- conclusion -->
          <button
            @click="changeCategory('conclusion'); closeMenu()"
            :class="menuButtonClass(activeCategory === 'conclusion')"
            class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm"
          >
            <span>Kesimpulan</span>
            <span v-if="conclusionStatus.isComplete" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white">✓</span>
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 py-1"
      :class="{
              'pb-4 pt-1': menuPosition === 'top',
              'pb-20 pt-4': menuPosition === 'bottom', // ✅ Padding bottom ketika menu di bawah
                'pb-4 pt-4'   : menuMode === 'vertical'
            }"
      >
      <!-- Pesan sukses -->
      <transition name="fade">
        <div
          v-if="successMessage"
          class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-400 text-white px-4 py-2 rounded shadow-lg z-50"
        >
          {{ successMessage }}
        </div>
      </transition>

      <!-- Konten Utama -->
      <div class="relative overflow-hidden">
        <transition name="category-slide" mode="out-in">
          <!-- Detail Kendaraan -->
          <VehicleDetails
            v-if="activeCategory === 'vehicle'"
            :inspection="inspection"
            :CarDetail="CarDetail"
            :allInspections="props.allInspections" 
            @update-vehicle="updateVehicleDetails"
            @save-car-details="saveNewCarDetails"
            @update:validation="handleVehicleValidation"
          @update:hasUnsavedChanges="handleUnsavedChanges"
          />

          <!-- Menu Inspeksi Biasa -->
          <!-- <category-section -->
           <CategoryCadangan
            v-else-if="activeMenuData && activeCategory !== 'conclusion'"
            :key="activeMenuData.id"
            :category="activeMenuData"
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




      <!-- Tombol Simpan Final
      <div v-if="activeCategory === 'conclusion'" class="flex justify-end gap-4 mt-2 p-4 bg-white rounded-xl shadow-md">
        <Link
              :href="route('inspections.pending', inspectionId)"
              :disabled="isLoading"
              @click="handleAction(route('inspections.pending', inspectionId), 'pending')"
              class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600"
              :class="{ 'opacity-50 cursor-not-allowed': isLoading && currentAction === 'pending' }"
            >
              <span v-if="isLoading && currentAction === 'pending'" class="flex items-center">
                <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
              </span>
              <span v-else>Tunda</span>
            </Link>
      
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
      </div> -->

      <!-- Tombol Simpan Final -->
    <div
      v-if="activeCategory === 'conclusion'"
      class="flex justify-end gap-4 mt-2 p-4 bg-white rounded-xl shadow-md"
        :class="{
          'mb-4': menuPosition === 'top',
          'mb-20': menuPosition === 'bottom' // ✅ Extra margin bottom ketika menu di bawah
        }"
    >
      <!-- Tombol Pending -->
      <Link
        :href="route('inspections.pending', inspectionId)"
        as="button"
        type="button"
        :disabled="isLoading && currentAction === 'pending'"
        @click.prevent="handleAction(route('inspections.pending', inspectionId), 'pending')"
        class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md shadow-sm bg-yellow-500 text-white hover:bg-yellow-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <template v-if="isLoading && currentAction === 'pending'">
          <svg
            class="animate-spin h-4 w-4 text-white mr-2"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 
                5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 
                5.824 3 7.938l3-2.647z"
            />
          </svg>
          <span>Memproses...</span>
        </template>
        <template v-else>
          <span>Tunda</span>
        </template>
      </Link>

      <!-- Tombol Final -->
      <button
        type="button"
        @click="submitAll"
        :disabled="!allMenusComplete || form.processing"
        class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg
          v-if="form.processing"
          class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 
              5.373 0 12h4zm2 5.291A7.962 7.962 0 
              014 12H0c0 3.042 1.135 5.824 3 
              7.938l3-2.647z"
          />
        </svg>
        <span>{{ form.processing ? 'Mengirim...' : 'Final Kirim Inspeksi' }}</span>
      </button>
    </div>

   <!-- Floating Button untuk Damage Points -->
 <button
    v-if="hasDamage"
    @mousedown="startDamageLongPress"
    @mouseup="stopDamageDrag"
    @mouseleave="cancelDamageLongPress"
    @mousemove="onDamageDrag"
    @touchstart="startDamageLongPress"
    @touchend="stopDamageDrag"
    @touchmove="onDamageDrag"
      @click="handleDamageClick"
    class="fixed z-40 p-4 bg-gradient-to-r from-indigo-700 to-sky-600 text-white rounded-full shadow-lg"
    :style="{ left: damagePos.x + 'px', top: damagePos.y + 'px' }"
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
              <div class="font-medium text-gray-900">{{ point.inspection_point?.name }}</div>
              <div class="text-sm text-gray-500">{{ point.inspection_point?.description }}</div>
              <div class="text-xs text-gray-400 mt-1">
                {{ getComponentName(point) }}
              </div>
              <div v-if="hasPointData(point.inspection_point_id)" class="text-xs text-green-600 mt-1">
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
        :title="selectedPoint?.inspection_point?.name || 'Detail Point'"
        :subtitle="selectedPoint?.inspection_point?.description"
        :name="`point-${selectedPoint?.id}`"
        :inspection-id="inspection.id"
        :point-id="selectedPoint?.inspection_point_id"
        :settings="selectedPoint?.settings || {}"
        :options="selectedPoint?.settings?.radios || []"
        :selected-Point="selectedPoint"
        :selected-value="tempRadioValue"
        :images-value="form.images[selectedPoint?.inspection_point_id]"
        :notes-value="tempNotes"
        :point="selectedPoint"
        :existing-data="getExistingPointData(selectedPoint?.inspection_point_id)"
        @update:selectedValue="tempRadioValue = $event"
        @update:notesValue="tempNotes = $event"
        @update:imagesValue="updateImagesValue"
        @save="saveAllData"
        @close="closeRadioModal"
        @hapus="hapusData"
      />

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, usePage, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import VehicleDetails from '@/Components/InspectionForm/VehicleDetails.vue';
import CategorySection from '@/Components/InspectionForm/CategorySection.vue';
import CategoryCadangan from '@/Components/InspectionForm/CategoryCadangan.vue';
import ConclusionSection from '@/Components/InspectionForm/ConclusionSection.vue';
import RadioOptionModal from '@/Components/InspectionForm/RadioOptionModal.vue';
import BottomSheetModal from '@/Components/InspectionForm/BottomSheetModal.vue';
import { useDraggableButton } from '@/Composables/useDraggableButton';

const props = defineProps({
  inspection: Object,
  inspectionId: Object,
  category: Object,
  appMenus: Array,
  damagePoints: Array,
  existingResults: Object,
  existingImages: Object,
  CarDetail: Array,
  allInspections: Array,
});

//================Untuk mengatur posisi menu 
const menuMode = ref(props.category?.settings?.menu_model || 'horizontal')
const menuPosition = ref(props.category?.settings?.position || 'top')

// state buka/tutup khusus vertical
const isMenuOpen = ref(false)

// draggable button untuk Toggle Menu
const {
  pos: togglePos,
  dragging: menuDragging,
  startLongPress: startToggleLongPress,
  cancelLongPress: cancelToggleLongPress,
  onDrag: onToggleDrag,
  stopDrag: stopToggleDrag,
  handleClick: handleToggleClickFunc,
} = useDraggableButton('toggleMenuButtonPos', {
  x: window.innerWidth - 80,
  y: 20,
});

// Handle click biasa (bukan drag)
const handleToggleClick = (e) => {
  if (handleToggleClickFunc(e)) {
    // Ini adalah click biasa, bukan hasil drag
    toggleMenu();
  }
  // Jika return false, berarti ini bagian dari drag, tidak perlu toggle menu
};

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
  isMenuOpen.value = false
}

const menuButtonClass = (isActive) => {
  return isActive
    ? 'bg-gradient-to-r from-indigo-700 to-sky-600 text-white'
    : 'bg-gray-50 hover:bg-gray-100 text-gray-700'
}

// ===================================================
// draggable button untuk Damage
const {
  pos: damagePos,
  dragging: damageDragging,
  startLongPress: startDamageLongPress,
  cancelLongPress: cancelDamageLongPress,
  onDrag: onDamageDrag,
  stopDrag: stopDamageDrag,
  handleClick: handleDamageClickFunc,
} = useDraggableButton('damageButtonPos', {
  x: window.innerWidth - 80,
  y: window.innerHeight - 80,
});

// Handle click biasa (bukan drag)
const handleDamageClick = (e) => {
  if (handleDamageClickFunc(e)) {
    // Ini adalah click biasa, buka modal
    showSearchModal.value = true;
  }
  // Jika return false, berarti ini bagian dari drag, tidak buka modal
};

// State untuk modal
const showSearchModal = ref(false);
const showRadioModal = ref(false);
const searchQuery = ref('');
const selectedPoint = ref(null);
const tempRadioValue = ref('');
const tempNotes = ref('');
const successMessage = ref('');

const isLoading = ref(false)
const currentAction = ref(null)

// Fungsi untuk menangani aksi dengan status loading
const handleAction = (route, actionType) => {
  isLoading.value = true;
  currentAction.value = actionType;

  router.visit(route, {
    onFinish: () => {
      isLoading.value = false;
      currentAction.value = null;
    }
  });
};

// cek apakah ada appMenu dengan input_type = 'damage'
const hasDamage = computed(() => {
  return props.appMenus.some(menu => menu.input_type === 'damage')
})

// Inisialisasi daftar kategori lengkap (termasuk 'vehicle' dan 'conclusion')
const allCategories = computed(() => {
  return ['vehicle', ...props.appMenus.map(menu => menu.id), 'conclusion'];
});

// State untuk navigasi kategori
const activeCategory = ref(allCategories.value[0]);
const activeIndex = ref(0);

// Watcher untuk sinkronisasi activeIndex dengan activeCategory
watch(activeCategory, (newVal) => {
  activeIndex.value = allCategories.value.indexOf(newVal);
});

// Properti terhitung untuk mendapatkan data menu aktif
const activeMenuData = computed(() => {
  if (activeCategory.value === 'vehicle' || activeCategory.value === 'conclusion') {
    return null;
  }
  const menu = props.appMenus.find(m => m.id === activeCategory.value);
  if (!menu) return null;

  return {
    ...menu,
    points: getVisiblePoints(menu.menu_point, menu.input_type === 'damage')
  };
});

// Filter points berdasarkan kondisi
const getVisiblePoints = (menuPoints, isDamageMenu) => {
  if (!isDamageMenu) {
    return menuPoints || [];
  }
  
  return (menuPoints || []).filter(point => {
    const pointId = point.inspection_point_id;
    return hasPointData(pointId);
  });
};


// State untuk menyimpan data dari komponen anak
const vehicleDetails = ref({
    plate_number: props.inspection.plate_number,
    car_id: props.inspection.car_id,
    car_name: props.inspection.car_name
});

// Definisikan state untuk melacak perubahan
const hasUnsavedChanges = ref(false);

// Function untuk handle emit dari child
const handleUnsavedChanges = (hasChanges) => {
  hasUnsavedChanges.value = hasChanges;
};
// State baru untuk menyimpan status validasi dari komponen anak
const isVehicleDetailsInvalid = ref(false);

// Fungsi yang dipanggil saat `update:validation` dari anak di-emit
const handleVehicleValidation = (isInvalid) => {
    isVehicleDetailsInvalid.value = isInvalid;
};


// --- Logika utama untuk memeriksa kelengkapan form kendaraan ---
const isVehicleFormComplete = computed(() => {
    // 1. Cek apakah nomor plat sudah terisi dan valid
    const isPlateValid = vehicleDetails.value.plate_number && /^[A-Z]{1,2}\d{1,4}[A-Z]{0,3}$/.test(vehicleDetails.value.plate_number);
    
    // 2. Cek apakah nama mobil sudah terisi
    const isCarNameFilled = !!vehicleDetails.value.car_name?.trim();
    
    // 3. Cek apakah ada validasi negatif dari komponen anak
    const hasNoBlockingValidation = !isVehicleDetailsInvalid.value;

    // 3. Cek apakah ada validasi negatif dari komponen anak
    const hasUnsavedChangesValidation = !hasUnsavedChanges.value;
    
    // 4. Gabungkan semua validasi
    return isPlateValid && isCarNameFilled && hasNoBlockingValidation && hasUnsavedChangesValidation;
});






// Filter damage points berdasarkan pencarian
const filteredDamagePoints = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.damagePoints || [];
  }
  
  const query = searchQuery.value.toLowerCase().trim();
  return (props.damagePoints || []).filter(point => 
    point.inspection_point?.name?.toLowerCase().includes(query) ||
    point.inspection_point?.description?.toLowerCase().includes(query) ||
    getComponentName(point)?.toLowerCase().includes(query)
  );
});

// Cek apakah point sudah memiliki data
const hasPointData = (pointId) => {
  const hasServerResult = props.existingResults && props.existingResults[pointId] !== undefined;
  const hasServerImages = props.existingImages && props.existingImages[pointId] && props.existingImages[pointId].length > 0;
  
  const hasLocalResult = form.results[pointId] && 
                         (form.results[pointId].status || form.results[pointId].note);
  
  const hasLocalImages = form.images[pointId] && form.images[pointId].length > 0;
  
  return hasServerResult || hasServerImages || hasLocalResult || hasLocalImages;
};

// Ambil nama component
const getComponentName = (point) => {
  return point.inspection_point.component?.name || 'Komponen Tidak Diketahui';
};

// Get existing data untuk point
const getExistingPointData = (pointId) => {
  return props.existingResults[pointId] || null;
};

// Pilih point dan buka modal
const selectPoint = (point) => {
  selectedPoint.value = point;
  const pointId = point.inspection_point_id;
  
  const existingResult = form.results[pointId] || {};
  tempRadioValue.value = existingResult.status || '';
  tempNotes.value = existingResult.note || '';

  showSearchModal.value = false;
  showRadioModal.value = true;
};

// Update images value
const updateImagesValue = (images) => {
  if (selectedPoint.value) {
    form.images[selectedPoint.value.inspection_point_id] = images;
  }
};

// Handle save data dari modal
const saveAllData = () => {
  if (selectedPoint.value) {
    const pointId = selectedPoint.value.inspection_point_id;
    
    form.results[pointId] = {
      ...form.results[pointId],
      status: tempRadioValue.value,
      note: tempNotes.value,
    };
    
    saveResult(pointId);
    successMessage.value = "Data berhasil disimpan!";
    setTimeout(() => successMessage.value = "", 1000);
  }
  
  closeRadioModal();
};

// Close modals
const closeSearchModal = () => {
  showSearchModal.value = false;
  searchQuery.value = '';
};

const closeRadioModal = () => {
  showRadioModal.value = false;
  selectedPoint.value = null;
  tempRadioValue.value = '';
  tempNotes.value = '';
};

// Inisialisasi form
const initializeForm = () => {
  const results = {};
  const images = {};

  const allPoints = [
    ...props.appMenus.flatMap(menu => menu.menu_point || []),
    ...(props.damagePoints || [])
  ];

  allPoints.forEach(point => {
    const pointId = point.inspection_point_id;
    const existingResult = props.existingResults[pointId];
    
    results[pointId] = {
      status: existingResult?.status || '',
      note: existingResult?.note || '',
    };
    
    images[pointId] = props.existingImages[pointId] || [];
  });
  
  return {
    inspection_id: props.inspection.id,
    results,
    images,
    overall_note: props.inspection.overall_note || ''
  };
};

const form = useForm(initializeForm());

// Check if menu is complete
const isMenuComplete = (menu) => {
  if (menu.input_type === 'damage') {
    const pointsWithData = getVisiblePoints(menu.menu_point, true);
    return pointsWithData.length > 0;
  }

  if (menu.id === 'conclusion') {
    return isConclusionComplete();
  }
  if (menu.id === 'vehicle') {
    return isVehicleFormComplete();
  }

  // 🔑 hanya ambil point yang default (is_default = true)
  const requiredPoints = (getVisiblePoints(menu.menu_point, false) || [])
    .filter(point => point.is_default);

  return requiredPoints.every(point => {
    const result = form.results[point.inspection_point_id];
    const image = form.images[point.inspection_point_id];
    if (!result) return false;
    
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
        
        const selectedOption = settings.radios?.find(opt => opt.value === result.status);
        if (selectedOption?.settings) {
          if (selectedOption.settings.show_textarea && !result.note?.trim()) {
            return false;
          }
          if (selectedOption.settings.show_image_upload && image?.length === 0) {
            return false;
          }
        }
        return true;
      
      case 'imageTOradio':
        if (image?.length === 0 || !result.status) return false;
        
        const selectedOptionImage = settings.radios?.find(opt => opt.value === result.status);
        if (selectedOptionImage?.settings) {
          if (selectedOptionImage.settings.show_textarea && selectedOptionImage.settings.required && !result.note?.trim()) {
            return false;
          }
        }
        return true;
      
      case 'image':
        return image?.length > 0;
      
      default:
        return !!result.status || !!result.note?.trim();
    }
  });
};


// Parse settings
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

// Check if conclusion is complete
const isConclusionComplete = () => {
  const settings = parseSettings(props.inspection.settings);
  const conclusionData = settings.conclusion || {};
  
  const hasFlooded = !!conclusionData.flooded;
  const hasCollision = !!conclusionData.collision;
  
  const hasValidCollision = conclusionData.collision === 'yes' 
    ? !!conclusionData.collision_severity 
    : true;
  
  const hasConclusionNote = !!props.inspection.notes?.trim();
  
  return hasFlooded && hasCollision && hasValidCollision && hasConclusionNote;
};

// Conclusion status
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

// Check if all menus are complete
const allMenusComplete = computed(() => {
  const vehicleComplete = isVehicleFormComplete.value;
  const regularMenusComplete = props.appMenus.every(menu => isMenuComplete(menu));
  const conclusionComplete = isConclusionComplete();
  return vehicleComplete && regularMenusComplete && conclusionComplete ;
});

// Change active category
const changeCategory = (menuId) => {
  activeCategory.value = menuId;
};

// Navigasi dengan swipe
const navigate = (direction) => {
  let newIndex = activeIndex.value + direction;
  
  if (newIndex >= 0 && newIndex < allCategories.value.length) {
    activeCategory.value = allCategories.value[newIndex];
  }
};

// Save single result
const saveResult = debounce(async (pointId) => {
  try {
    await router.post(route('inspections.save-result'), {
      inspection_id: props.inspection.id,
      point_id: pointId,
      status: form.results[pointId]?.status,
      note: form.results[pointId]?.note,
      images: form.images[pointId],
    }, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        router.reload({ only: ['existingResults', 'existingImages'] });
      }
    });
  } catch (error) {
    console.error('Error menyimpan hasil:', error);
  }
}, 500);

// Update result
const updateResult = ({ pointId, type, value }) => {
  if (form.results[pointId]?.hasOwnProperty(type)) {
    form.results[pointId][type] = value;
  }
  saveResult(pointId);
};

// Delete data
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
        onSuccess: () => {
          // RESET STATE SECARA MANUAL sebelum reload
          if (form.results[pointId]) {
            form.results[pointId] = {
              status: '',
              note: ''
            };
          }
          if (form.images[pointId]) {
            form.images[pointId] = [];
          }
          
          successMessage.value = "Data berhasil dihapus!";
          
          // Gunakan setTimeout untuk memberi waktu reset state sebelum reload
          setTimeout(() => {
            router.reload({ 
              only: ['existingResults', 'existingImages'],
              onSuccess: () => {
                // Force reset form setelah reload
                Object.assign(form, initializeForm());
              }
            });
          }, 100);
        },
        onError: (errors) => {
          console.error('Error menghapus hasil:', errors);
        }
      });
    } catch (error) {
      console.error('Error menghapus hasil:', error);
    }
    closeRadioModal();
  }
};

// Update vehicle details
const updateVehicleDetails = (vehicleData) => {
  console.log('Update vehicle:', vehicleData);
};

// Save new car details
const saveNewCarDetails = (carDetails) => {
  console.log('Save car details:', carDetails);
};

// Update conclusion
const updateConclusion = (conclusionData) => {
  Object.assign(form.conclusion, conclusionData);
  saveConclusion();
};

// Save conclusion
const saveConclusion = debounce(async () => {
  try {
    await router.post(route('inspections.save-conclusion'), {
      inspection_id: props.inspection.id,
      ...form.conclusion
    }, {
      preserveScroll: true,
      preserveState: true,
    });
  } catch (error) {
    console.error('Error menyimpan kesimpulan:', error);
  }
}, 500);

// Final submit
const submitAll = () => {
  if (!allMenusComplete.value) {
    alert('Harap lengkapi semua menu inspeksi termasuk kesimpulan sebelum submit final');
    return;
  }

  form.post(route('inspections.final-submit', { 
    id: props.inspection.id 
  }), {
    preserveScroll: true,
    onSuccess: () => {},
    onError: (errors) => {
      console.error('Kesalahan pengiriman:', errors);
    }
  });
};

// Remove image
const removeImage = ({ pointId, imageId }) => {
  if (form.images[pointId]) {
    form.images[pointId] = form.images[pointId].filter(img => img.id !== imageId);
    saveResult(pointId);
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
    const swipeThreshold = 150;
    if (touchEndX < touchStartX - swipeThreshold) {
      navigate(1); // Geser kiri -> kategori berikutnya
    } else if (touchEndX > touchStartX + swipeThreshold) {
      navigate(-1); // Geser kanan -> kategori sebelumnya
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

onMounted(() => {
  // setupSwipe();
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

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
