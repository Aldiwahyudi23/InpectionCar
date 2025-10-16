<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Overlay Redup -->
    <div
      v-if="damageDragging || menuDragging"
      class="fixed inset-0 bg-black bg-opacity-40 z-30 pointer-events-none transition-opacity duration-200"
    ></div>
    
    <!-- Tombol Toggle Menu Vertikal -->
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

    <!-- Horizontal menu (Sticky/Fixed) -->
    <div
      v-if="menuMode === 'horizontal'"
      class="z-10 bg-white shadow-sm"
      :class="{
        'sticky top-0 mb-2': menuPosition === 'top',
        'fixed bottom-0 left-0 right-0': menuPosition === 'bottom',
      }"
    >
      <div 
        ref="menuContainer"
        class="flex overflow-x-auto scrollbar-hide py-3 px-4 space-x-2 menu-container"
        @scroll="handleMenuScroll"
      >
        <!-- Tombol Toggle Swipe -->
        <div class="flex items-center space-x-2 flex-shrink-0">
          <span class="text-sm text-gray-600">Swipe</span>
          <button
            @click="toggleSwipe"
            type="button"
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            :class="isSwipeEnabled ? 'bg-indigo-600' : 'bg-gray-200'"
            role="switch"
            :aria-checked="isSwipeEnabled"
          >
            <span
              aria-hidden="true"
              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
              :class="isSwipeEnabled ? 'translate-x-5' : 'translate-x-0'"
            />
          </button>
        </div>

        <!-- TAMBAHKAN: Pengaturan Sumber Gambar -->
        <div class="flex items-center space-x-2 flex-shrink-0">
          <span class="text-sm text-gray-600">Sumber</span>
          <select 
            v-model="imageSourceSetting"
            @change="updateImageSourceSetting"
            class="text-sm border border-gray-300 rounded-md px-5 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="all">Tanya</option>
            <option value="camera">Kamera</option>
            <option value="gallery">Galeri</option>
          </select>
        </div>

        <!-- Menu Detail Kendaraan -->
        <button
          ref="menuItems"
          @click="changeCategory('vehicle')"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200 menu-item"
          :class="{
            'bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white': activeCategory === 'vehicle',
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== 'vehicle'
          }"
          :data-category="'vehicle'"
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
          ref="menuItems"
          @click="changeCategory(String(menu.id))"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200 menu-item"
          :class="{
            'bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white': activeCategory === String(menu.id),
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== String(menu.id)
          }"
          :data-category="String(menu.id)"
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
          ref="menuItems"
          @click="changeCategory('conclusion')"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200 menu-item"
          :class="{
            'bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white': activeCategory === 'conclusion',
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== 'conclusion'
          }"
          :data-category="'conclusion'"
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
           <!-- Tombol Toggle Swipe -->
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Swipe Gesture</span>
            <button
              @click="toggleSwipe"
              type="button"
              class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              :class="isSwipeEnabled ? 'bg-indigo-600' : 'bg-gray-200'"
              role="switch"
              :aria-checked="isSwipeEnabled"
            >
              <span
                aria-hidden="true"
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                :class="isSwipeEnabled ? 'translate-x-5' : 'translate-x-0'"
              />
            </button>
          </div>

           <!-- TAMBAHKAN: Pengaturan Sumber Gambar -->
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Sumber</span>
            <select 
              v-model="imageSourceSetting"
              @change="updateImageSourceSetting"
              class="text-sm border border-gray-300 rounded-md px-6 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="all">Tanya</option>
              <option value="camera">Kamera</option>
              <option value="gallery">Galeri</option>
            </select>
          </div>

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
            @click="() => { changeCategory(String(menu.id)); closeMenu(); }"
            :class="[menuButtonClass(activeCategory === String(menu.id)), 'w-full flex items-center justify-between px-3 py-2 rounded-md text-sm']"
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
        'pb-20 pt-4': menuPosition === 'bottom',
        'pb-4 pt-4': menuMode === 'vertical'
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
          <div v-if="activeCategory === 'vehicle'" key="vehicle">
            <VehicleDetails
              :inspection="inspection"
              :CarDetail="CarDetail"
              :allInspections="props.allInspections" 
              @update-vehicle="updateVehicleDetails"
              @save-car-details="saveNewCarDetails"
              @update:validation="handleVehicleValidation"
              @update:hasUnsavedChanges="handleUnsavedChanges"
            />
          </div>

          <!-- Menu Inspeksi Biasa -->
          <div v-else-if="activeMenuData && activeCategory !== 'conclusion'" :key="activeMenuData.id">
            <CategorySection
              :category="activeMenuData"
              :inspection-id="inspection.id"
              :car="props.car"
              :form="form"
              :head = "menuMode"
              @updateResult="updateResult" 
              @hapusPoint="hapusData"
              @removeImage="removeImage"
            />
          </div>

          <!-- Kesimpulan -->
          <div v-else-if="activeCategory === 'conclusion'" key="conclusion">
            <ConclusionSection
              :form="{ conclusion: conclusionState }"
              :inspection-id="inspection.id"
              :inspection="inspection"
              :settings="inspection.settings || {}"
              @updateConclusion="updateConclusion"
            />
          </div>
        </transition>
      </div>

      <!-- Tombol Simpan Final -->
      <div
        v-if="activeCategory === 'conclusion'"
        class="flex justify-end gap-4 mt-2 p-4 bg-white rounded-xl shadow-md"
        :class="{
          'mb-4': menuPosition === 'top',
          'mb-20': menuPosition === 'bottom'
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
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              />
            </svg>
            <span>Memproses...</span>
          </template>
          <template v-else>
            <span>Tunda</span>
          </template>
        </Link>

        <!-- Tombol Final Submit -->
        <button
          type="button"
          @click="submitAll"
          :disabled="!allMenusComplete || isSubmitting"
          class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg
            v-if="isSubmitting"
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
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
          <span>{{ isSubmitting ? 'Mengirim...' : 'Final Kirim Inspeksi' }}</span>
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
        class="fixed z-5 p-4 bg-gradient-to-r from-indigo-700 to-sky-600 text-white rounded-full shadow-lg"
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
        <!-- TAMBAHKAN: Close button di corner -->
      <button 
        @click="closeSearchModal"
        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors z-10"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
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

        <!-- Toast untuk exit message -->
      <div
        v-if="showExitMessage"
        class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg z-50 text-center"
      >
        <p class="text-sm">Tekan sekali lagi untuk keluar dari inspeksi</p>
        <p class="text-xs text-gray-300 mt-1">Data inspeksi telah disimpan secara lokal</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, provide, nextTick, inject  } from 'vue';
import { useForm, usePage, Link, router } from '@inertiajs/vue3';
import VehicleDetails from './components/VehicleDetails.vue';
import CategorySection from './components/CategorySection.vue';
import ConclusionSection from './components/ConclusionSection.vue';
import RadioOptionModal from './components/RadioOptionModal.vue';
import BottomSheetModal from './components/BottomSheetModal.vue';
import { useDraggableButton } from '@/Composables/useDraggableButton';

// =========================================================================
// UTILITY FUNCTIONS
// =========================================================================

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

// =========================================================================
// PROPS
// =========================================================================

const props = defineProps({
  inspection: Object,
  inspectionId: Object,
  category: Object,
  car: Object,
  appMenus: Array,
  damagePoints: Array,
  existingResults: Object,
  existingImages: Object,
  CarDetail: Array,
  allInspections: Array,
});

// =========================================================================
// UTILITY DAN KONFIGURASI LOCAL STORAGE
// =========================================================================

// Key unik untuk Local Storage berdasarkan ID inspeksi
const LOCAL_STORAGE_KEY = computed(() => `inspection_data_${props.inspection.id}`);
const SWIPE_TOGGLE_KEY = computed(() => `swipe_toggle_${props.inspection.id}`);
const IMAGE_SOURCE_KEY = computed(() => `image_source_${props.inspection.id}`);
const ACTIVE_CATEGORY_KEY = computed(() => `active_category_${props.inspection.id}`);

// Utility functions untuk Local Storage
const getLocalData = () => {
  try {
    const data = localStorage.getItem(LOCAL_STORAGE_KEY.value);
    return data ? JSON.parse(data) : {};
  } catch (error) {
    console.error("Error reading local storage:", error);
    return {};
  }
};

const setLocalData = (data) => {
  try {
    localStorage.setItem(LOCAL_STORAGE_KEY.value, JSON.stringify(data));
  } catch (error) {
    console.error("Error writing to local storage:", error);
  }
};

const clearLocalData = () => {
  try {
    localStorage.removeItem(LOCAL_STORAGE_KEY.value);
    localStorage.removeItem(SWIPE_TOGGLE_KEY.value);
    localStorage.removeItem(IMAGE_SOURCE_KEY.value);
    localStorage.removeItem(ACTIVE_CATEGORY_KEY.value); // Baru
  } catch (error) {
    console.error("Error clearing local storage:", error);
  }
};

// TAMBAHKAN FUNGSI UNTUK SWIPE TOGGLE
const getSwipeToggleState = () => {
  try {
    const state = localStorage.getItem(SWIPE_TOGGLE_KEY.value);
    return state ? JSON.parse(state) : true;
  } catch (error) {
    console.error("Error reading swipe toggle state:", error);
    return true;
  }
};

const setSwipeToggleState = (state) => {
  try {
    localStorage.setItem(SWIPE_TOGGLE_KEY.value, JSON.stringify(state));
  } catch (error) {
    console.error("Error writing swipe toggle state:", error);
  }
};

// TAMBAHKAN FUNGSI UNTUK IMAGE SOURCE SETTING
const getImageSourceSetting = () => {
  try {
    const setting = localStorage.getItem(IMAGE_SOURCE_KEY.value);
    return setting || 'all';
  } catch (error) {
    console.error("Error reading image source setting:", error);
    return 'all';
  }
};

const setImageSourceSetting = (setting) => {
  try {
    localStorage.setItem(IMAGE_SOURCE_KEY.value, setting);
  } catch (error) {
    console.error("Error writing image source setting:", error);
  }
};


// Utility functions untuk Active Category
const getActiveCategory = () => {
  try {
    return localStorage.getItem(ACTIVE_CATEGORY_KEY.value) || allCategories.value[0];
  } catch (error) {
    console.error("Error reading active category:", error);
    return allCategories.value[0];
  }
};

const setActiveCategory = (category) => {
  try {
    localStorage.setItem(ACTIVE_CATEGORY_KEY.value, category);
  } catch (error) {
    console.error("Error writing active category:", error);
  }
};

// =========================================================================
// DRAGGABLE MENU & DAMAGE BUTTON
// =========================================================================

const menuMode = ref(props.category?.settings?.menu_model || 'horizontal')
const menuPosition = ref(props.category?.settings?.position || 'top')
const isMenuOpen = ref(false)
const isSubmitting = ref(false);

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

const handleToggleClick = (e) => {
  if (handleToggleClickFunc(e)) {
    toggleMenu();
  }
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

const handleDamageClick = (e) => {
  if (handleDamageClickFunc(e)) {
    showSearchModal.value = true;
  }
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

const hasDamage = computed(() => {
  return props.appMenus.some(menu => menu.input_type === 'damage')
})

const allCategories = computed(() => {
  return ['vehicle', ...props.appMenus.map(menu => String(menu.id)), 'conclusion'];
});

// State untuk navigasi kategori
const activeCategory = ref(allCategories.value[0]);
const activeIndex = ref(0);

// Watcher untuk sinkronisasi activeIndex dengan activeCategory
watch(activeCategory, (newVal) => {
  activeIndex.value = allCategories.value.indexOf(newVal);
});

const activeMenuData = computed(() => {
  if (activeCategory.value === 'vehicle' || activeCategory.value === 'conclusion') {
    return null;
  }
  const menu = props.appMenus.find(m => String(m.id) === activeCategory.value);
  if (!menu) return null;

  return {
    ...menu,
    points: getVisiblePoints(menu.menu_point, menu.input_type === 'damage')
  };
});

const getVisiblePoints = (menuPoints, isDamageMenu) => {
  if (!isDamageMenu) {
    return menuPoints || [];
  }
  
  return (menuPoints || []).filter(point => {
    const pointId = point.inspection_point_id;
   return hasPointData(pointId);
  });
};

// =========================================================================
// FORM & DATA PERSISTENCE
// =========================================================================

// State untuk Detail Kendaraan
const vehicleDetails = ref({
    plate_number: props.inspection.plate_number,
    car_id: props.inspection.car_id,
    car_name: props.inspection.car_name
});

// State lokal untuk Kesimpulan
const conclusionState = ref({
    notes: props.inspection.overall_note || '',
    flooded: parseSettings(props.inspection.settings)?.conclusion?.flooded || null,
    collision: parseSettings(props.inspection.settings)?.conclusion?.collision || null,
    collision_severity: parseSettings(props.inspection.settings)?.conclusion?.collision_severity || null,
});

// Inisialisasi form dengan prioritas Local Storage
const initializeForm = () => {
  const allPoints = [
    ...props.appMenus.flatMap(menu => menu.menu_point || []),
    ...(props.damagePoints || [])
  ];

  const defaultResults = {};
  const defaultImages = {};
  allPoints.forEach(point => {
    const pointId = point.inspection_point_id;
    const existingResult = props.existingResults[pointId];
    defaultResults[pointId] = {
      status: existingResult?.status || '',
      note: existingResult?.note || '',
    };
    defaultImages[pointId] = props.existingImages[pointId] || [];
  });

  const localData = getLocalData();

  const results = { ...defaultResults, ...(localData.results || {}) };
  const images = { ...defaultImages, ...(localData.images || {}) };
  
  const initialVehicleDetails = { ...vehicleDetails.value, ...(localData.vehicleDetails || {}) };
  
  const overall_note = localData.overall_note !== undefined ? localData.overall_note : props.inspection.overall_note || '';

  return {
    inspection_id: props.inspection.id,
    results,
    images,
    overall_note,
    plate_number: initialVehicleDetails.plate_number,
    car_id: initialVehicleDetails.car_id,
    car_name: initialVehicleDetails.car_name,
  };
};

const form = useForm(initializeForm());

// Watcher untuk menyimpan form.results dan form.images ke Local Storage
watch(form, (newForm) => {
  setLocalData({
    results: newForm.results,
    images: newForm.images,
    overall_note: newForm.overall_note,
    vehicleDetails: vehicleDetails.value,
    conclusion: conclusionState.value,
  });
}, { deep: true });

// Watcher untuk vehicleDetails
watch(vehicleDetails, (newDetails) => {
    form.plate_number = newDetails.plate_number;
    form.car_id = newDetails.car_id;
    form.car_name = newDetails.car_name;
}, { deep: true });

// Watcher untuk conclusionState
watch(conclusionState, (newConclusion) => {
    form.overall_note = newConclusion.notes;
}, { deep: true });

// =========================================================================
// VALIDASI & COMPUTED PROPERTIES
// =========================================================================

// Inject dari parent untuk local storage features
const vehicleFeatures = inject('vehicleFeatures', ref(null));

// Setelah data di-load dari localStorage
vehicleFeatures.value = JSON.parse(localStorage.getItem('vehicle_features') || '{}');

provide('vehicleFeatures', vehicleFeatures);

// Fungsi utama untuk kompatibilitas kendaraan
const isPointCompatible = (point) => {
  const vehicleConfig = point.settings;
  
  // Jika tidak ada config kompatibilitas, langsung return true
  if (!hasCompatibilitySettings(point)) {
    return true;
  }

  const carTransmission = props.car?.transmission;
  const carFuelType = props.car?.fuel_type;
  
  // Check transmission - HANYA jika ada pengaturan transmission
  if (vehicleConfig.transmission && Array.isArray(vehicleConfig.transmission) && vehicleConfig.transmission.length > 0) {
    if (carTransmission && !vehicleConfig.transmission.includes(carTransmission)) {
      return false;
    }
  }

  // Check fuel type - HANYA jika ada pengaturan fuel_type
  if (vehicleConfig.fuel_type && vehicleConfig.fuel_type.trim() !== '') {
    if (carFuelType && vehicleConfig.fuel_type !== carFuelType) {
      return false;
    }
  }

    // Check vehicle features dari local storage - HANYA jika ada pengaturannya
    if (vehicleConfig.rear_door) {
      if (!vehicleFeatures.value || !vehicleFeatures.value.rear_door) {
        return false;
      }
    }

    if (vehicleConfig.pick_up) {
      if (!vehicleFeatures.value || !vehicleFeatures.value.pick_up) {
        return false;
      }
    }

    if (vehicleConfig.box) {
      if (!vehicleFeatures.value || !vehicleFeatures.value.box) {
        return false;
      }
    }

  return true;
};

// Cek apakah point memiliki pengaturan kompatibilitas
const hasCompatibilitySettings = (point) => {
  const settings = point.settings;
  return !!(
    (settings?.transmission && Array.isArray(settings.transmission) && settings.transmission.length > 0) ||
    (settings?.fuel_type && settings.fuel_type.trim() !== '') ||
    settings?.rear_door ||
    settings?.pick_up ||
    settings?.box
  );
};

// =========================================================================
// VALIDASI & COMPUTED PROPERTIES
// =========================================================================

const hasUnsavedChanges = ref(false);
const handleUnsavedChanges = (hasChanges) => {
  hasUnsavedChanges.value = hasChanges;
};

const isVehicleDetailsInvalid = ref(false);
const handleVehicleValidation = (isInvalid) => {
    isVehicleDetailsInvalid.value = isInvalid;
};

const isVehicleFormComplete = computed(() => {
    const isPlateValid = vehicleDetails.value.plate_number && /^[A-Z]{1,2}\d{1,4}[A-Z]{0,3}$/.test(vehicleDetails.value.plate_number);
    const isCarNameFilled = !!vehicleDetails.value.car_name?.trim();
    const hasNoBlockingValidation = !isVehicleDetailsInvalid.value;
    const hasUnsavedChangesValidation = !hasUnsavedChanges.value;
    
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
  const hasLocalResult = form.results[pointId] && 
                         (form.results[pointId].status || form.results[pointId].note);
  
  const hasLocalImages = form.images[pointId] && form.images[pointId].length > 0;
  
  return hasLocalResult || hasLocalImages;
};

// Ambil nama component
const getComponentName = (point) => {
  return point.inspection_point.component?.name || 'Komponen Tidak Diketahui';
};

// Get existing data untuk point
const getExistingPointData = (pointId) => {
  return form.results[pointId] || null;
};

// Check if conclusion is complete
const isConclusionComplete = () => {
  const conclusionData = conclusionState.value;
  
  const hasFlooded = !!conclusionData.flooded;
  const hasCollision = !!conclusionData.collision;
  
  const hasValidCollision = conclusionData.collision === 'yes' 
    ? !!conclusionData.collision_severity 
    : true;
  
  const hasConclusionNote = !!conclusionData.notes?.trim();
  
  return hasFlooded && hasCollision && hasValidCollision && hasConclusionNote;
};

// Conclusion status
const conclusionStatus = computed(() => {
  return {
    flooded: conclusionState.value.flooded || null,
    collision: conclusionState.value.collision || null,
    collision_severity: conclusionState.value.collision_severity || null,
    note: conclusionState.value.notes || null,
    isComplete: isConclusionComplete()
  };
});

// Check if all menus are complete
const allMenusComplete = computed(() => {
  const vehicleComplete = isVehicleFormComplete.value;
  const regularMenusComplete = props.appMenus.every(menu => isMenuComplete(menu));
  const conclusionComplete = isConclusionComplete();
  return vehicleComplete && regularMenusComplete && conclusionComplete;
});

// Check if menu is complete
const isMenuComplete = (menu) => {

  if (menu.input_type === 'damage') {
    // const pointsWithData = getVisiblePoints(menu.menu_point, true);
    // return pointsWithData.length > 0;

     // Selalu dianggap complete, regardless of data
    return true;
  }
  
  if (menu.id === 'conclusion') {
    return isConclusionComplete();
  }
  if (menu.id === 'vehicle') {
    return isVehicleFormComplete.value;
  }

 const visiblePoints = (getVisiblePoints(menu.menu_point, false) || []);
  
  // Filter hanya point-point yang aktif/visible, is_default, DAN compatible
  const activeRequiredPoints = visiblePoints.filter(point => {
    // Cek kompatibilitas seperti di komponen contoh
    return point.is_default && isPointCompatible(point);
  });

  return activeRequiredPoints.every(point => {
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

// =========================================================================
// STATE UNTUK SWIPE DAN IMAGE SOURCE SETTING
// =========================================================================

// State untuk swipe toggle
const isSwipeEnabled = ref(true);

// TAMBAHKAN: State untuk image source setting
const imageSourceSetting = ref('all'); // 'all', 'camera', 'gallery'

// TAMBAHKAN: Refs untuk scroll menu
const menuContainer = ref(null);
const menuItems = ref([]);

// TAMBAHKAN: State untuk double tap to exit
const backButtonPressed = ref(0);
const showExitMessage = ref(false);

// =========================================================================
// PROVIDE UNTUK CHILD COMPONENTS
// =========================================================================

// TAMBAHKAN: Provide image source setting ke child components
provide('imageSourceSetting', imageSourceSetting);

// =========================================================================
// ACTION HANDLERS
// =========================================================================

// Toggle swipe gesture
const toggleSwipe = () => {
  isSwipeEnabled.value = !isSwipeEnabled.value;
  setSwipeToggleState(isSwipeEnabled.value);
};

// TAMBAHKAN: Update image source setting
const updateImageSourceSetting = () => {
  setImageSourceSetting(imageSourceSetting.value);
};

// TAMBAHKAN: Fungsi untuk scroll menu ke tengah
const scrollActiveMenuToCenter = () => {
  if (!menuContainer.value) return;
  
  nextTick(() => {
    const activeItem = menuContainer.value.querySelector(`[data-category="${activeCategory.value}"]`);
    if (!activeItem) return;
    
    const container = menuContainer.value;
    const containerWidth = container.clientWidth;
    const itemOffsetLeft = activeItem.offsetLeft;
    const itemWidth = activeItem.clientWidth;
    
    // Hitung posisi scroll agar item aktif di tengah
    const scrollPosition = itemOffsetLeft - (containerWidth / 2) + (itemWidth / 2);
    
    // Smooth scroll ke posisi yang dihitung
    container.scrollTo({
      left: scrollPosition,
      behavior: 'smooth'
    });
  });
};

// TAMBAHKAN: Handler untuk back button yang lebih aman
const handleBackButton = () => {
  // Jika pertama kali tekan
  if (backButtonPressed.value === 0) {
    backButtonPressed.value = 1;
    showExitMessage.value = true;
    
    // Reset setelah 2 detik
    setTimeout(() => {
      backButtonPressed.value = 0;
      showExitMessage.value = false;
    }, 2000);
    
    // Selalu prevent default back behavior
    window.history.pushState(null, null, window.location.href);
    return false;
  } 
  // Jika kedua kali tekan dalam 2 detik
  else {
    backButtonPressed.value = 0;
    showExitMessage.value = false;
    
    // PERBAIKAN: Kembali ke halaman list inspections menggunakan Inertia
    router.visit(route('job.index'));
    return true;
  }
};

// TAMBAHKAN: Setup back button handler yang lebih aman
const setupBackButtonHandler = () => {
  // Inisialisasi history state
  if (window.history.state === null) {
    window.history.replaceState({ isBackButtonControlled: true }, '');
  }
  window.history.pushState({ isBackButtonControlled: true }, '');
  
  // Handle popstate event
  window.addEventListener('popstate', (event) => {
    // Only handle if it's our controlled state
    if (event.state && event.state.isBackButtonControlled) {
      event.preventDefault();
      handleBackButton();
    }
  });
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
    
    successMessage.value = "Data berhasil disimpan di lokal!";
    setTimeout(() => successMessage.value = "", 1000);
  }
  
  closeRadioModal();
};

// Update result
const updateResult = ({ pointId, type, value }) => {
  if (form.results[pointId]?.hasOwnProperty(type)) {
    form.results[pointId][type] = value;
  }
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
          
          setTimeout(() => successMessage.value = "", 1000);
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
  Object.assign(vehicleDetails.value, vehicleData);
  handleUnsavedChanges(true);
};

// Save new car details
const saveNewCarDetails = (carDetails) => {
};

// Update conclusion method
const updateConclusion = (conclusionData) => {
  conclusionState.value = {
    ...conclusionState.value,
    ...conclusionData
  };

  form.overall_note = conclusionData.notes || '';
};

// Final submit
const submitAll = () => {
  if (!allMenusComplete.value) {
    alert('Harap lengkapi semua menu inspeksi termasuk kesimpulan sebelum submit final');
    return;
  }

    if (!confirm('Apakah Anda yakin ingin mengirim semua data inspeksi? Data lokal akan dihapus setelah berhasil dikirim.')) {
    return;
  }

  isSubmitting.value = true;

  const dataToSubmit = {
    ...form.data(),
    overall_note: conclusionState.value.notes,
    conclusion: {
        notes: conclusionState.value.notes,
        flooded: conclusionState.value.flooded,
        collision: conclusionState.value.collision,
        collision_severity: conclusionState.value.collision_severity,
    },
  };

  router.post(route('inspections.final-submit-local', { 
    id: props.inspection.id 
  }), dataToSubmit, {
    preserveScroll: true,
    onSuccess: () => {
      clearLocalData();
      // alert('Inspeksi berhasil disubmit dan data lokal telah dihapus!');
    },
    onError: (errors) => {
      console.error('Kesalahan pengiriman:', errors);
      // alert('Kesalahan pengiriman. Data lokal masih tersimpan.');
       isSubmitting.value = false;
    }
  });
};

// Remove image
const removeImage = ({ pointId, imageId }) => {
  if (form.images[pointId]) {
    form.images[pointId] = form.images[pointId].filter(img => img.id !== imageId);
  }
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

// TAMBAHKAN: Handle menu scroll (optional, untuk tracking)
const handleMenuScroll = () => {
  // Bisa ditambahkan logic untuk handle scroll event jika diperlukan
};
// Navigasi
const changeCategory = (menuId) => {
  activeCategory.value = menuId;
  // Simpan ke localStorage
  setActiveCategory(menuId);
  
  // Scroll ke tengah setelah perubahan kategori
  scrollActiveMenuToCenter();
};

const navigate = (direction) => {
  let newIndex = activeIndex.value + direction;
  
  if (newIndex >= 0 && newIndex < allCategories.value.length) {
    activeCategory.value = allCategories.value[newIndex];
     // Scroll ke tengah setelah navigasi
    scrollActiveMenuToCenter();
  }
};

// Setup swipe gestures
const setupSwipe = () => {
  let touchStartX = 0;
  let touchEndX = 0;
  let cleanupFunction = null;
  
  const handleTouchStart = (e) => {
    if (!isSwipeEnabled.value) return;
    touchStartX = e.changedTouches[0].screenX;
  };
  
  const handleTouchEnd = (e) => {
    if (!isSwipeEnabled.value) return;
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  };
  
  const handleSwipe = () => {
    if (!isSwipeEnabled.value) return;
    
    const swipeThreshold = 250;
    if (touchEndX < touchStartX - swipeThreshold) {
      navigate(1);
    } else if (touchEndX > touchStartX + swipeThreshold) {
      navigate(-1);
    }
  };
  
  const initializeSwipe = () => {
    const mainContentArea = document.querySelector('.relative.overflow-hidden'); 
    if (mainContentArea) {
      mainContentArea.addEventListener('touchstart', handleTouchStart, { passive: true });
      mainContentArea.addEventListener('touchend', handleTouchEnd, { passive: true });
    }
    
    return () => {
      if (mainContentArea) {
        mainContentArea.removeEventListener('touchstart', handleTouchStart);
        mainContentArea.removeEventListener('touchend', handleTouchEnd);
      }
    };
  };
  
  // Inisialisasi pertama kali
  cleanupFunction = initializeSwipe();
  
  // Watch untuk perubahan status swipe
  watch(isSwipeEnabled, (newVal) => {
    if (cleanupFunction) {
      cleanupFunction();
    }
    
    if (newVal) {
      cleanupFunction = initializeSwipe();
    }
  });
  
  return () => {
    if (cleanupFunction) {
      cleanupFunction();
    }
  };
};


// =========================================================================
// WATCHERS
// =========================================================================

// TAMBAHKAN: Watcher untuk active category change
watch(activeCategory, (newCategory, oldCategory) => {
  // Scroll ke tengah ketika category berubah
  scrollActiveMenuToCenter();
});

// Watcher untuk sinkronisasi activeIndex dengan activeCategory
watch(activeCategory, (newVal) => {
  activeIndex.value = allCategories.value.indexOf(newVal);
});


onMounted(() => {
  const localData = getLocalData();
  
  // Inisialisasi swipe toggle
  isSwipeEnabled.value = getSwipeToggleState();
  
  // TAMBAHKAN: Inisialisasi image source setting
  imageSourceSetting.value = getImageSourceSetting();
  
  if (localData.vehicleDetails) {
    Object.assign(vehicleDetails.value, localData.vehicleDetails);
    form.plate_number = localData.vehicleDetails.plate_number;
    form.car_id = localData.vehicleDetails.car_id;
    form.car_name = localData.vehicleDetails.car_name;
  }
  
  // PERBAIKAN: Inisialisasi conclusionState dari localStorage
  if (localData.conclusion) {
    Object.assign(conclusionState.value, localData.conclusion);
    form.overall_note = localData.conclusion.notes || '';
  } else {
    // Jika tidak ada di localStorage, gunakan data dari server
    const settings = parseSettings(props.inspection.settings);
    const conclusion = settings.conclusion || {};
    conclusionState.value = {
      notes: props.inspection.notes || '',
      flooded: conclusion.flooded || '',
      collision: conclusion.collision || '',
      collision_severity: conclusion.collision_severity || '',
    };
    form.overall_note = props.inspection.notes || '';
  }

  if (localData.results) {
      Object.assign(form.results, localData.results);
  }
  if (localData.images) {
      Object.assign(form.images, localData.images);
  }

   // Inisialisasi active category dari localStorage
  const savedActiveCategory = getActiveCategory();
  
  // Validasi: pastikan category yang disimpan masih ada dalam allCategories
  if (allCategories.value.includes(savedActiveCategory)) {
    activeCategory.value = savedActiveCategory;
  } else {
    // Jika category yang disimpan tidak valid, gunakan default
    activeCategory.value = allCategories.value[0];
    setActiveCategory(allCategories.value[0]);
  }

   setupSwipe();

     // TAMBAHKAN: Setup back button handler
  setupBackButtonHandler();

    // TAMBAHKAN: Scroll ke tengah setelah mounted
  setTimeout(() => {
    scrollActiveMenuToCenter();
  }, 100);

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

/* Global scroll smoothness */
html {
  scroll-behavior: smooth;
}

/* Main content scroll optimization */
.main-content {
  scroll-behavior: smooth;
  will-change: transform, scroll-position;
  transform: translateZ(0);
  -webkit-overflow-scrolling: touch;
}

/* ================================================= */
/* MODIFIKASI UNTUK SCROLLING & PERFORMA LEBIH MULUS */
/* ================================================= */

.menu-container {
  /* scroll-behavior: smooth; (Baik untuk scroll programatik, tapi tidak untuk user scroll) */
  scroll-behavior: smooth;
  
  /* PENTING 1: Beri tahu browser bahwa properti ini akan berubah (untuk persiapan rendering) */
  will-change: transform, scroll-position, content; 
  
  /* PENTING 2: Memaksa browser menggunakan akselerasi Hardware (GPU) */
  /* Ini adalah kunci untuk menghilangkan 'patah-patah' saat scrolling */
  transform: translateZ(0); 
  
  /* PENTING 3: Memperbaiki elastisitas scroll pada perangkat iOS/Mobile Webkit */
  -webkit-overflow-scrolling: touch;
}

.menu-item {
  /* Ganti 'all' dengan properti yang diakselerasi (opacity & transform) */
  /* Ini membuat transisi lebih cepat dan tidak membebani layout */
  transition: opacity 0.3s ease, transform 0.3s ease; 
  transform: translateZ(0); /* Akselerasi GPU untuk item itu sendiri */
}


.category-slide-enter-active,
.category-slide-leave-active {
  /* Transisi dioptimalkan untuk Transform */
  transition: transform 0.4s ease-out, opacity 0.4s ease-out; 
  position: absolute; 
  width: 100%; 
  top: 0;
  left: 0;
  height: 100%;
}
.category-slide-enter-from {
  transform: translateX(100%); /* Sedikit dikurangi dari 150% agar lebih cepat */
  opacity: 0;
}
.category-slide-leave-to {
  transform: translateX(-100%); /* Sedikit dikurangi dari 150% agar lebih cepat */
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s; /* Naikkan sedikit dari 0.3s menjadi 0.4s untuk rasa smooth */
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

</style>