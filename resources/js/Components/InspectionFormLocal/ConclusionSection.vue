<template>
  <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100">
    <div class="bg-indigo-200 px-6 py-2 border-b flex items-center justify-between">
        <h4 class="text-base font-semibold text-indigo-700">
        Detail Kendaraan
        </h4>
    </div>

    <div class="p-4 space-y-4-6">
      <label class="block text-sm font-medium text-gray-700 mb-3">
        Apakah kendaraan pernah terkena banjir?
        <span class="text-red-500">*</span>
      </label>
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 w-full">
        <label
          v-for="option in floodOptions"
          :key="option.value"
          class="cursor-pointer"
        >
          <input
            type="radio"
            v-model="conclusionData.flooded"
            :value="option.value"
            class="hidden peer"
            @change="handleRadioChange"
          />
          <div
            class="w-full px-4 py-3 border rounded-lg text-center transition-all duration-200 ease-in-out whitespace-nowrap text-sm font-medium"
            :class="{
              'border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm scale-[0.98]': conclusionData.flooded === option.value,
              'border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400': conclusionData.flooded !== option.value
            }"
          >
            {{ option.label }}
          </div>
        </label>
      </div>
    </div>

    <div class="p-4 space-y-4-6">
      <label class="block text-sm font-medium text-gray-700 mb-3">
        Apakah kendaraan pernah mengalami tabrakan?
        <span class="text-red-500">*</span>
      </label>
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 w-full">
        <label
          v-for="option in collisionOptions"
          :key="option.value"
          class="cursor-pointer"
        >
          <input
            type="radio"
            v-model="conclusionData.collision"
            :value="option.value"
            class="hidden peer"
            @change="handleRadioChange"
          />
          <div
            class="w-full px-4 py-3 border rounded-lg text-center transition-all duration-200 ease-in-out whitespace-nowrap text-sm font-medium"
            :class="{
              'border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm scale-[0.98]': conclusionData.collision === option.value,
              'border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400': conclusionData.collision !== option.value
            }"
          >
            {{ option.label }}
          </div>
        </label>
      </div>

      <!-- Collision severity - smooth transition -->
      <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 transform -translate-y-2"
        enter-to-class="opacity-100 transform translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 transform translate-y-0"
        leave-to-class="opacity-0 transform -translate-y-2"
      >
        <div v-if="conclusionData.collision === 'yes'" class="p-4 space-y-4 bg-blue-50 rounded-lg border border-blue-200">
          <label class="block text-sm font-medium text-gray-700 mb-3">Tingkat kerusakan:
            <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 w-full">
            <label
              v-for="option in severityOptions"
              :key="option.value"
              class="cursor-pointer"
            >
              <input
                type="radio"
                v-model="conclusionData.collision_severity"
                :value="option.value"
                class="hidden peer"
                @change="handleRadioChange"
              />
              <div
                class="w-full px-4 py-3 border rounded-lg text-center transition-all duration-200 ease-in-out whitespace-nowrap text-sm font-medium"
                :class="{
                  'border-blue-500 bg-blue-100 text-blue-700 shadow-sm scale-[0.98]': conclusionData.collision_severity === option.value,
                  'border-gray-300 text-gray-700 hover:bg-white hover:border-gray-400': conclusionData.collision_severity !== option.value
                }"
              >
                {{ option.label }}
              </div>
            </label>
          </div>
        </div>
      </transition>
    </div>

    <div class="p-4 space-y-2">
      <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-medium text-gray-700">Catatan Kesimpulan</label>
        
        <!-- Template Selector Button -->
        <button
          type="button"
          @click="showTemplates = !showTemplates"
          class="flex items-center gap-2 px-3 py-2 text-sm bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-all duration-200"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
          </svg>
          Template
        </button>
      </div>

      <!-- Template Selection Modal -->
      <transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 transform scale-95"
        enter-to-class="opacity-100 transform scale-100"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 transform scale-100"
        leave-to-class="opacity-0 transform scale-95"
      >
        <div v-if="showTemplates" class="mb-4 p-4 bg-white border border-gray-200 rounded-lg shadow-lg">
          <div class="flex items-center justify-between mb-3">
            <h5 class="text-sm font-semibold text-gray-700">Pilih Template Kesimpulan</h5>
            <button @click="showTemplates = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto">
            <button
              v-for="template in conclusionTemplates"
              :key="template.id"
              @click="applyTemplate(template)"
              class="p-3 text-left border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition-all duration-150"
            >
              <div class="text-sm font-medium text-gray-800 mb-1">{{ template.name }}</div>
              <div class="text-xs text-gray-600 line-clamp-2">{{ template.description }}</div>
            </button>
          </div>
        </div>
      </transition>

      <!-- Formatting Toolbar dengan Status Active -->
      <div class="flex flex-wrap gap-1 mb-2 p-2 bg-gray-100 rounded-md">
        <button
          type="button"
          @click="toggleFormat('bold')"
          class="p-2 rounded transition-all duration-150 ease-in-out font-medium"
          :class="activeFormats.bold ? 'bg-indigo-500 text-white shadow-sm' : 'hover:bg-gray-200 hover:shadow-sm'"
          title="Bold"
        >
          <strong>B</strong>
        </button>
        <button
          type="button"
          @click="toggleFormat('italic')"
          class="p-2 rounded transition-all duration-150 ease-in-out font-medium"
          :class="activeFormats.italic ? 'bg-indigo-500 text-white shadow-sm' : 'hover:bg-gray-200 hover:shadow-sm'"
          title="Italic"
        >
          <em>I</em>
        </button>
        <button
          type="button"
          @click="toggleFormat('underline')"
          class="p-2 rounded transition-all duration-150 ease-in-out font-medium"
          :class="activeFormats.underline ? 'bg-indigo-500 text-white shadow-sm' : 'hover:bg-gray-200 hover:shadow-sm'"
          title="Underline"
        >
          <u>U</u>
        </button>
        
        <!-- Clear Template Button -->
        <button
          type="button"
          @click="clearTemplate()"
          class="p-2 rounded transition-all duration-150 ease-in-out hover:bg-gray-200 hover:shadow-sm ml-2"
          title="Clear Template"
        >
          🗑️ Clear
        </button>
      </div>

      <!-- Status Format Active dengan animasi -->
      <transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 transform -translate-y-1"
        enter-to-class="opacity-100 transform translate-y-0"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 transform translate-y-0"
        leave-to-class="opacity-0 transform -translate-y-1"
      >
        <div v-if="Object.values(activeFormats).some(val => val)" class="text-xs text-gray-500 mb-2 bg-gray-50 p-2 rounded border">
          Format aktif: 
          <span v-if="activeFormats.bold" class="font-bold mx-1 bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Bold</span>
          <span v-if="activeFormats.italic" class="italic mx-1 bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Italic</span>
          <span v-if="activeFormats.underline" class="underline mx-1 bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Underline</span>
        </div>
      </transition>

      <!-- Text Editor dengan focus state yang lebih smooth -->
      <div
        ref="editorRef"
        contenteditable="true"
        class="w-full min-h-[120px] p-3 rounded-md border border-gray-300 shadow-sm transition-all duration-200 ease-in-out prose max-w-none"
        :class="{
          'bg-white border-gray-300': !isEditing,
          'bg-blue-50 border-indigo-500 ring-2 ring-indigo-200': isEditing
        }"
        @input="handleEditorInput"
        @blur="handleEditorBlur"
        @focus="handleEditorFocus"
        @keydown.enter="handleEnterKey"
        @paste="handlePaste"
        @mouseup="checkActiveFormats"
        @keyup="checkActiveFormats"
        placeholder="Tambahkan catatan kesimpulan inspeksi di sini..."
      ></div>
      
      <!-- Template Variables Helper -->
      <!-- <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="text-xs font-medium text-yellow-800 mb-2">Variabel yang tersedia:</div>
        <div class="flex flex-wrap gap-2">
          <span 
            v-for="variable in availableVariables" 
            :key="variable"
            class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded border border-yellow-300"
          >
            {{ variable }}
          </span>
        </div>
        <div class="text-xs text-yellow-600 mt-2">
          *Variabel akan otomatis terisi berdasarkan data inspeksi
        </div>
      </div> -->
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick, computed, reactive } from 'vue'
import { debounce } from 'lodash'

const props = defineProps({
  inspectionId: { type: Number, required: true },
  inspection: {
    type: Object,
    required: true
  },
  form: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['updateConclusion'])

const editorRef = ref(null)
const isEditing = ref(false)
const showTemplates = ref(false)

// Track active formats dengan reactive object
const activeFormats = reactive({
  bold: false,
  italic: false,
  underline: false
})

// Gunakan reactive conclusionData yang terintegrasi dengan parent
const conclusionData = reactive({
  flooded: '',
  collision: '',
  collision_severity: '',
  notes: ''
})

const conclusionTemplates = ref([
  {
    id: 1,
    name: 'Kendaraan Normal',
    description: 'Template untuk kendaraan dalam kondisi normal tanpa masalah signifikan (Ringkas)',
    content: `<p><strong>A. HASIL INSPEKSI:</strong></p>
- Kondisi kendaraan secara umum: <b>BAIK</b><br>
- Tidak ditemukan indikasi kerusakan signifikan<br>
- Semua sistem berfungsi dengan normal<br>

<p><strong>B. HAL PENTING SEBELUM PEMBAYARAN:</strong></p>
- Untuk menghindari kemungkinan yang terjadi di luar inspeksi (misalnya bunyi tidak wajar, fungsi elektrikal, lampu indikator, dan fitur penunjang ADAS/Cruise Control/dll.), <b>DIWAJIBKAN</b> untuk melakukan <b>Test Drive Terlebih Dahulu</b> sebelum pembayaran/transaksi.<br>
- Pastikan knalpot <b>Tidak Berasap</b> (khusus untuk mobil bensin).`
  },
  {
    id: 2,
    name: 'Kendaraan Bekas Tabrakan Ringan',
    description: 'Template untuk kendaraan dengan riwayat tabrakan ringan (Lengkap A, C, B)',
    content: `<p><strong>A. HASIL INSPEKSI:</strong></p>
- Kendaraan memiliki riwayat tabrakan dengan tingkat kerusakan: <b>RINGAN</b><br>
- Area yang terdampak: PERLU DIISI<br>
- Perbaikan yang dilakukan: PERLU DIISI<br>

<p><strong>C. ESTIMASI PERBAIKAN DAN PERAWATAN:</strong></p>
<p>* PERBAIKAN & PENGGANTIAN PART DALAM WAKTU DEKAT/ SEGERA:</p>
- PERLU DIISI<br>
- Estimasi Biaya: PERLU DIISI
<p>* PERBAIKAN & PENGGANTIAN PART TENGGANG WAKTU MINIMAL 6 BULAN S/D 1 TAHUN KE DEPAN:</p>
- PERLU DIISI<br>
- Estimasi Biaya: PERLU DIISI
<p><em>Note: Estimasi perbaikan bisa lebih murah/lebih mahal. Opsional tergantung lokasi/daerah dan ketersediaan spare part.</em></p>

<p><strong>B. HAL PENTING SEBELUM PEMBAYARAN:</strong></p>
- Untuk menghindari kemungkinan yang terjadi di luar inspeksi (misalnya bunyi tidak wajar, fungsi elektrikal, lampu indikator, dan fitur penunjang ADAS/Cruise Control/dll.), <b>DIWAJIBKAN</b> untuk melakukan <b>Test Drive Terlebih Dahulu</b> sebelum pembayaran/transaksi.<br>
- Pastikan knalpot <b>Tidak Berasap</b> (khusus untuk mobil bensin).`
  },
  {
    id: 3,
    name: 'Kendaraan Bekas Banjir',
    description: 'Template untuk kendaraan dengan riwayat terkena banjir (Lengkap A, C, B)',
    content: `<p><strong>A. HASIL INSPEKSI:</strong></p>
- Kendaraan memiliki riwayat terkena banjir<br>
- Tingkat paparan air: PERLU DIISI<br>
- Komponen yang terdampak: PERLU DIISI<br>

<p><strong>C. ESTIMASI PERBAIKAN DAN PERAWATAN:</strong></p>
<p>* PERBAIKAN & PENGGANTIAN PART DALAM WAKTU DEKAT/ SEGERA:</p>
- PERLU DIISI<br>
- Estimasi Biaya: PERLU DIISI
<p>* PERBAIKAN & PENGGANTIAN PART TENGGANG WAKTU MINIMAL 6 BULAN S/D 1 TAHUN KE DEPAN:</p>
- PERLU DIISI<br>
- Estimasi Biaya: PERLU DIISI
<p><em>Note: Estimasi perbaikan bisa lebih murah/lebih mahal. Opsional tergantung lokasi/daerah dan ketersediaan spare part.</em></p>

<p><strong>B. HAL PENTING SEBELUM PEMBAYARAN:</strong></p>
- Untuk menghindari kemungkinan yang terjadi di luar inspeksi (misalnya bunyi tidak wajar, fungsi elektrikal, lampu indikator, dan fitur penunjang ADAS/Cruise Control/dll.), <b>DIWAJIBKAN</b> untuk melakukan <b>Test Drive Terlebih Dahulu</b> sebelum pembayaran/transaksi.<br>
- Pastikan knalpot <b>Tidak Berasap</b> (khusus untuk mobil bensin).`
  },
  {
    id: 4,
    name: 'Kendaraan dengan Multiple Issues',
    description: 'Template untuk kendaraan dengan beberapa masalah umum (Lengkap A, C, B)',
    content: `<p><strong>A. HASIL INSPEKSI:</strong></p>
- Kondisi umum kendaraan: PERLU DIISI<br>
- Masalah utama yang ditemukan: PERLU DIISI<br>
- Komponen yang perlu perhatian: PERLU DIISI<br>
- Status Tabrak/Banjir: PERLU DIISI<br>

<p><strong>C. ESTIMASI PERBAIKAN DAN PERAWATAN:</strong></p>
<p>* PERBAIKAN & PENGGANTIAN PART DALAM WAKTU DEKAT/ SEGERA:</p>
- PERLU DIISI<br>
- Estimasi Biaya: PERLU DIISI
<p>* PERBAIKAN & PENGGANTIAN PART TENGGANG WAKTU MINIMAL 6 BULAN S/D 1 TAHUN KE DEPAN:</p>
- PERLU DIISI<br>
- Estimasi Biaya: PERLU DIISI
<p><em>Note: Estimasi perbaikan bisa lebih murah/lebih mahal. Opsional tergantung lokasi/daerah dan ketersediaan spare part.</em></p>

<p><strong>B. HAL PENTING SEBELUM PEMBAYARAN:</strong></p>
- Untuk menghindari kemungkinan yang terjadi di luar inspeksi (misalnya bunyi tidak wajar, fungsi elektrikal, lampu indikator, dan fitur penunjang ADAS/Cruise Control/dll.), <b>DIWAJIBKAN</b> untuk melakukan <b>Test Drive Terlebih Dahulu</b> sebelum pembayaran/transaksi.<br>
- Pastikan knalpot <b>Tidak Berasap</b> (khusus untuk mobil bensin).`
  },
  {
    id: 5,
    name: 'Template Singkat',
    description: 'Template ringkas untuk kesimpulan cepat (Ringkas)',
    content: `<p><strong>A. HASIL INSPEKSI:</strong></p>
- Status Keseluruhan Kendaraan: PERLU DIISI<br>
- Catatan Utama: PERLU DIISI<br>
- Status Tabrak/Banjir: PERLU DIISI

<p><strong>B. HAL PENTING SEBELUM PEMBAYARAN:</strong></p>
- Untuk menghindari kemungkinan yang terjadi di luar inspeksi (misalnya bunyi tidak wajar, fungsi elektrikal, lampu indikator, dan fitur penunjang ADAS/Cruise Control/dll.), <b>DIWAJIBKAN</b> untuk melakukan <b>Test Drive Terlebih Dahulu</b> sebelum pembayaran/transaksi.<br>
- Pastikan knalpot <b>Tidak Berasap</b> (khusus untuk mobil bensin).`
  },
  {
    id: 6,
    name: 'Template Teknis Detail',
    description: 'Template detail untuk laporan teknis lengkap (Lengkap A, C, B)',
    content: `<p><strong>A. LAPORAN HASIL INSPEKSI TEKNIS:</strong></p>
- Tanggal Inspeksi: PERLU DIISI<br>
- Kondisi Mesin: PERLU DIISI<br>
- Kondisi Transmisi: PERLU DIISI<br>
- Kondisi Suspensi: PERLU DIISI<br>
- Kondisi Rem: PERLU DIISI<br>
- Kondisi Body: PERLU DIISI<br>
- Kondisi Interior: PERLU DIISI<br>
- Kesimpulan Akhir: PERLU DIISI

<p><strong>C. ESTIMASI PERBAIKAN DAN PERAWATAN:</strong></p>
<p>* PERBAIKAN & PENGGANTIAN PART DALAM WAKTU DEKAT/ SEGERA:</p>
- PERLU DIISI<br>
- Estimasi Biaya: PERLU DIISI
<p>* PERBAIKAN & PENGGANTIAN PART TENGGANG WAKTU MINIMAL 6 BULAN S/D 1 TAHUN KE DEPAN:</p>
- PERLU DIISI<br>
- Estimasi Biaya: PERLU DIISI
<p><em>Note: Estimasi perbaikan bisa lebih murah/lebih mahal. Opsional tergantung lokasi/daerah dan ketersediaan spare part.</em></p>

<p><strong>B. HAL PENTING SEBELUM PEMBAYARAN:</strong></p>
- Untuk menghindari kemungkinan yang terjadi di luar inspeksi (misalnya bunyi tidak wajar, fungsi elektrikal, lampu indikator, dan fitur penunjang ADAS/Cruise Control/dll.), <b>DIWAJIBKAN</b> untuk melakukan <b>Test Drive Terlebih Dahulu</b> sebelum pembayaran/transaksi.<br>
- Pastikan knalpot <b>Tidak Berasap</b> (khusus untuk mobil bensin).`
  }
])


// Variabel yang tersedia untuk template
const availableVariables = ref([
  '{PLATE_NUMBER}',
  '{CAR_NAME}',
  '{INSPECTION_DATE}',
  '{DAMAGED_AREA}',
  '{REPAIR_DETAILS}',
  '{WATER_LEVEL}',
  '{AFFECTED_COMPONENTS}',
  '{OVERALL_CONDITION}',
  '{MAIN_ISSUES}',
  '{ATTENTION_NEEDED}',
  '{REPAIR_COST_ESTIMATE}',
  '{RECOMMENDATION}',
  '{OVERALL_STATUS}',
  '{ADDITIONAL_NOTES}',
  '{ENGINE_CONDITION}',
  '{TRANSMISSION_CONDITION}',
  '{SUSPENSION_CONDITION}',
  '{BRAKE_CONDITION}',
  '{BODY_CONDITION}',
  '{INTERIOR_CONDITION}',
  '{FINAL_CONCLUSION}'
])

// Helper parse settings
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
}

// Ambil data conclusion dari settings
const conclusionSettings = computed(() => {
  const settings = parseSettings(props.inspection.settings);
  return settings.conclusion || {};
})

// Options
const floodOptions = [
  { value: 'yes', label: 'Ya' },
  { value: 'no', label: 'Tidak' }
]

const collisionOptions = [
  { value: 'yes', label: 'Ya' },
  { value: 'no', label: 'Tidak' }
]

const severityOptions = [
  { value: 'light', label: 'Ringan' },
  { value: 'heavy', label: 'Berat' }
]

// Initialize form dan editor
onMounted(() => {
  initializeForm();
  
  nextTick(() => {
    initializeEditor();
    checkActiveFormats();
  });
})

// Initialize editor content
const initializeEditor = () => {
  if (editorRef.value && conclusionData.notes) {
    editorRef.value.innerHTML = conclusionData.notes;
  }
}

// Initialize form data dengan data dari parent
const initializeForm = () => {
  const settings = parseSettings(props.inspection.settings);
  const conclusion = settings.conclusion || {};

  // Prioritaskan data dari localStorage (via parent form) terlebih dahulu
  const localConclusion = props.form.conclusion || {};
  
  conclusionData.flooded = localConclusion.flooded || conclusion.flooded || '';
  conclusionData.collision = localConclusion.collision || conclusion.collision || '';
  conclusionData.collision_severity = localConclusion.collision_severity || conclusion.collision_severity || '';
  conclusionData.notes = localConclusion.notes || props.inspection.notes || '';
}

// Apply template ke editor
const applyTemplate = (template) => {
  if (!editorRef.value) return;
  
  let processedContent = template.content;
  
  // Replace variables dengan data aktual jika tersedia
  processedContent = processedContent
    .replace(/{PLATE_NUMBER}/g, props.inspection.plate_number || '[NOMOR_PLAT]')
    .replace(/{CAR_NAME}/g, props.inspection.car_name || '[NAMA_MOBIL]')
    .replace(/{INSPECTION_DATE}/g, new Date().toLocaleDateString('id-ID'))
    .replace(/{FLOOD_STATUS}/g, conclusionData.flooded === 'yes' ? 'Pernah terkena banjir' : 'Tidak pernah terkena banjir')
    .replace(/{COLLISION_STATUS}/g, conclusionData.collision === 'yes' ? 'Pernah mengalami tabrakan' : 'Tidak pernah mengalami tabrakan');
  
  editorRef.value.innerHTML = processedContent;
  conclusionData.notes = processedContent;
  showTemplates.value = false;
  
  // Focus ke editor setelah apply template
  nextTick(() => {
    editorRef.value.focus();
    updateConclusion();
  });
}

// Clear template dan reset ke default
const clearTemplate = () => {
  if (editorRef.value) {
    editorRef.value.innerHTML = '';
    conclusionData.notes = '';
    updateConclusion();
  }
}

// Check active formats pada selection
const checkActiveFormats = () => {
  if (!editorRef.value) return;
  
  const selection = window.getSelection();
  if (selection.rangeCount === 0 || selection.isCollapsed) return;

  activeFormats.bold = document.queryCommandState('bold');
  activeFormats.italic = document.queryCommandState('italic');
  activeFormats.underline = document.queryCommandState('underline');
}

// Toggle format dengan status yang jelas
const toggleFormat = (format) => {
  if (!editorRef.value) return;
  
  editorRef.value.focus();
  
  const isCurrentlyActive = activeFormats[format];
  
  switch (format) {
    case 'bold': 
      document.execCommand('bold', false, !isCurrentlyActive); 
      break;
    case 'italic': 
      document.execCommand('italic', false, !isCurrentlyActive); 
      break;
    case 'underline': 
      document.execCommand('underline', false, !isCurrentlyActive); 
      break;
  }
  
  // Update status format setelah toggle
  setTimeout(() => {
    checkActiveFormats();
    handleEditorInput();
  }, 10);
}

// Handle editor events dengan debounce yang lebih cepat
const handleEditorInput = debounce(() => {
  if (editorRef.value) {
    conclusionData.notes = editorRef.value.innerHTML;
    updateConclusion();
  }
}, 200)

const handleEnterKey = (event) => {
  event.preventDefault();
  document.execCommand('insertParagraph', false, null);
}

const handlePaste = (event) => {
  event.preventDefault();
  const text = (event.clipboardData || window.clipboardData).getData('text/plain');
  document.execCommand('insertText', false, text);
}

// Handle editor focus dengan smooth transition
const handleEditorFocus = () => {
  isEditing.value = true;
}

// Handle editor blur dengan smooth transition
const handleEditorBlur = () => {
  isEditing.value = false;
  if (editorRef.value) {
    conclusionData.notes = editorRef.value.innerHTML;
    updateConclusion();
  }
}

// Handle radio change dengan immediate response
const handleRadioChange = () => {
  updateConclusion();
}

// Update conclusion data dan emit ke parent
const updateConclusion = () => {
  const conclusionToEmit = {
    flooded: conclusionData.flooded,
    collision: conclusionData.collision,
    collision_severity: conclusionData.collision === 'yes' ? conclusionData.collision_severity : null,
    notes: conclusionData.notes
  };
  
  emit('updateConclusion', conclusionToEmit);
}

// Watch untuk perubahan dari parent form
watch(() => props.form.conclusion, (newConclusion) => {
  if (newConclusion) {
    conclusionData.flooded = newConclusion.flooded !== undefined ? newConclusion.flooded : conclusionData.flooded;
    conclusionData.collision = newConclusion.collision !== undefined ? newConclusion.collision : conclusionData.collision;
    conclusionData.collision_severity = newConclusion.collision_severity !== undefined ? newConclusion.collision_severity : conclusionData.collision_severity;
    conclusionData.notes = newConclusion.notes !== undefined ? newConclusion.notes : conclusionData.notes;
    
    // Update editor content jika ada perubahan
    if (editorRef.value && newConclusion.notes !== undefined && editorRef.value.innerHTML !== newConclusion.notes) {
      editorRef.value.innerHTML = newConclusion.notes;
    }
  }
}, { deep: true, immediate: true });

// Watch untuk perubahan individual pada conclusionData
watch([
  () => conclusionData.flooded,
  () => conclusionData.collision,
  () => conclusionData.collision_severity,
  () => conclusionData.notes
], () => {
  updateConclusion();
}, { deep: true });
</script>

<style scoped>
/* Style untuk placeholder contenteditable */
[contenteditable=true]:empty:before {
  content: attr(placeholder);
  color: #9ca3af;
  pointer-events: none;
  display: block;
}

/* Style untuk editor */
.prose :deep(p) {
  margin-bottom: 1em;
  line-height: 1.6;
}

.prose :deep(ul) {
  list-style-type: disc;
  margin-left: 1.5em;
  margin-bottom: 1em;
}

.prose :deep(li) {
  margin-bottom: 0.5em;
}

.prose :deep(strong) {
  font-weight: bold;
}

.prose :deep(em) {
  font-style: italic;
}

.prose :deep(u) {
  text-decoration: underline;
}

/* Style untuk line clamp */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth transitions untuk semua elemen */
* {
  transition-property: color, background-color, border-color, transform, box-shadow;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>