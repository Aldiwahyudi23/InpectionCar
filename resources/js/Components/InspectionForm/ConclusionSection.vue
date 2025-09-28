<template>
  <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100">
    <div class="bg-indigo-200 px-6 py-2 border-b">
      <h3 class="text-xl font-semibold text-indigo-700">Kesimpulan Inspeksi</h3>
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
            v-model="form.flooded"
            :value="option.value"
            class="hidden peer"
            @change="saveToServer"
          />
          <div
            class="w-full px-4 py-3 border rounded-lg text-center transition-colors whitespace-nowrap text-sm font-medium"
            :class="{
              'border-indigo-500 bg-indigo-50 text-indigo-700': form.flooded === option.value,
              'border-gray-300 text-gray-700 hover:bg-gray-50': form.flooded !== option.value
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
            v-model="form.collision"
            :value="option.value"
            class="hidden peer"
            @change="saveToServer"
          />
          <div
            class="w-full px-4 py-3 border rounded-lg text-center transition-colors whitespace-nowrap text-sm font-medium"
            :class="{
              'border-indigo-500 bg-indigo-50 text-indigo-700': form.collision === option.value,
              'border-gray-300 text-gray-700 hover:bg-gray-50': form.collision !== option.value
            }"
          >
            {{ option.label }}
          </div>
        </label>
      </div>

      <div v-if="form.collision === 'yes'" class="p-4 space-y-4">
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
              v-model="form.collision_severity"
              :value="option.value"
              class="hidden peer"
              @change="saveToServer"
            />
            <div
              class="w-full px-4 py-3 border rounded-lg text-center transition-colors whitespace-nowrap text-sm font-medium"
              :class="{
                'border-indigo-500 bg-indigo-50 text-indigo-700': form.collision_severity === option.value,
                'border-gray-300 text-gray-700 hover:bg-gray-50': form.collision_severity !== option.value
              }"
            >
              {{ option.label }}
            </div>
          </label>
        </div>
      </div>
    </div>

    <div class="p-4 space-y-2">
      <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Kesimpulan</label>
      
      <!-- Formatting Toolbar dengan Status Active -->
      <div class="flex gap-1 mb-2 p-2 bg-gray-100 rounded-md">
        <button
          type="button"
          @click="toggleFormat('bold')"
          class="p-2 rounded transition-colors font-medium"
          :class="activeFormats.bold ? 'bg-indigo-500 text-white' : 'hover:bg-gray-200'"
          title="Bold"
        >
          <strong>B</strong>
        </button>
        <button
          type="button"
          @click="toggleFormat('italic')"
          class="p-2 rounded transition-colors font-medium"
          :class="activeFormats.italic ? 'bg-indigo-500 text-white' : 'hover:bg-gray-200'"
          title="Italic"
        >
          <em>I</em>
        </button>
        <button
          type="button"
          @click="toggleFormat('underline')"
          class="p-2 rounded transition-colors font-medium"
          :class="activeFormats.underline ? 'bg-indigo-500 text-white' : 'hover:bg-gray-200'"
          title="Underline"
        >
          <u>U</u>
        </button>
        <button
          type="button"
          @click="clearFormatting()"
          class="p-2 rounded hover:bg-gray-200 transition-colors ml-2"
          title="Clear Formatting"
        >
          🗑️ Clear
        </button>
      </div>

      <!-- Status Format Active -->
      <div v-if="Object.values(activeFormats).some(val => val)" class="text-xs text-gray-500 mb-2">
        Format aktif: 
        <span v-if="activeFormats.bold" class="font-bold mx-1">Bold</span>
        <span v-if="activeFormats.italic" class="italic mx-1">Italic</span>
        <span v-if="activeFormats.underline" class="underline mx-1">Underline</span>
      </div>

      <!-- Text Editor -->
      <div
        ref="editorRef"
        contenteditable="true"
        class="w-full min-h-[120px] p-2 rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 prose max-w-none"
        :class="{ 'bg-white': !isEditing, 'bg-blue-50': isEditing }"
        @input="handleEditorInput"
        @blur="saveContent"
        @focus="isEditing = true"
        @keydown.enter="handleEnterKey"
        @paste="handlePaste"
        @mouseup="checkActiveFormats"
        @keyup="checkActiveFormats"
        placeholder="Tambahkan catatan kesimpulan inspeksi di sini..."
      ></div>
      
      <p class="text-xs mt-1 text-gray-500">{{ status }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash'

const props = defineProps({
  inspectionId: { type: Number, required: true },
  inspection: {
    type: Object,
    required: true
  },
})

const editorRef = ref(null)
const isEditing = ref(false)
const status = ref('')
const STORAGE_KEY = `inspection-${props.inspectionId}-conclusion`

// Track active formats dengan reactive object
const activeFormats = reactive({
  bold: false,
  italic: false,
  underline: false
})

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

// Form data dengan reactive object
const form = ref({
  flooded: conclusionSettings.value.flooded || '',
  collision: conclusionSettings.value.collision || '',
  collision_severity: conclusionSettings.value.collision_severity || '',
  conclusion_note: ''
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
  loadFromLocalStorage();
  
  nextTick(() => {
    initializeEditor();
    checkActiveFormats(); // Check format status awal
  });
})

// Load dari localStorage
const loadFromLocalStorage = () => {
  const savedData = localStorage.getItem(STORAGE_KEY);
  if (savedData) {
    try {
      const parsedData = JSON.parse(savedData);
      // Merge dengan data dari props, prioritaskan localStorage
      form.value = { ...form.value, ...parsedData };
      status.value = '📱 Memuat draft tersimpan...';
    } catch (e) {
      console.error('Error loading from localStorage:', e);
    }
  } else if (props.inspection.notes) {
    form.value.conclusion_note = props.inspection.notes;
  }
}

// Initialize editor content
const initializeEditor = () => {
  if (editorRef.value && form.value.conclusion_note) {
    editorRef.value.innerHTML = form.value.conclusion_note;
  }
}

// Initialize form data
const initializeForm = () => {
  const settings = parseSettings(props.inspection.settings);
  const conclusion = settings.conclusion || {};

  form.value.flooded = conclusion.flooded || '';
  form.value.collision = conclusion.collision || '';
  form.value.collision_severity = conclusion.collision_severity || '';
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
  
  // Jika format sudah aktif, matikan - jika tidak, aktifkan
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

// Clear semua formatting
const clearFormatting = () => {
  if (!editorRef.value) return;
  
  editorRef.value.focus();
  document.execCommand('removeFormat', false, null);
  
  // Reset semua status format
  Object.keys(activeFormats).forEach(key => {
    activeFormats[key] = false;
  });
  
  handleEditorInput();
}

// Handle editor events
const handleEditorInput = debounce(() => {
  if (editorRef.value) {
    form.value.conclusion_note = editorRef.value.innerHTML;
    saveToServer();
  }
}, 500)

const handleEnterKey = (event) => {
  event.preventDefault();
  document.execCommand('insertParagraph', false, null);
}

const handlePaste = (event) => {
  event.preventDefault();
  const text = (event.clipboardData || window.clipboardData).getData('text/plain');
  document.execCommand('insertText', false, text);
}

// Save ketika blur editor
const saveContent = () => {
  isEditing.value = false;
  if (editorRef.value) {
    form.value.conclusion_note = editorRef.value.innerHTML;
    saveToServer();
  }
}

// Simpan ke server dengan optimistic update
const saveToServer = debounce(() => {
  const dataToSend = {
    flooded: form.value.flooded,
    collision: form.value.collision,
    collision_severity: form.value.collision === 'yes' ? form.value.collision_severity : null,
    conclusion_note: form.value.conclusion_note
  }

  // 1. Simpan backup ke localStorage
  localStorage.setItem(STORAGE_KEY, JSON.stringify(dataToSend));
  status.value = '💾 Menyimpan...';

  // 2. Kirim ke server
  router.post(route('inspections.updateConclusion', props.inspectionId), dataToSend, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      localStorage.removeItem(STORAGE_KEY);
      status.value = '✅ Tersimpan';
    },
    onError: () => {
      status.value = '❌ Gagal menyimpan - data tersimpan secara lokal';
      // Auto-retry setelah 3 detik
      setTimeout(() => saveToServer(), 3000);
    }
  })
}, 1000);

// Watch untuk perubahan radio buttons
watch([
  () => form.value.flooded,
  () => form.value.collision,
  () => form.value.collision_severity
], () => {
  saveToServer();
})

// Cleanup localStorage ketika component unmount
import { onBeforeUnmount } from 'vue';
onBeforeUnmount(() => {
  // Hanya hapus jika data sudah tersimpan di server (status success)
  if (status.value === '✅ Tersimpan') {
    localStorage.removeItem(STORAGE_KEY);
  }
});
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

/* Style untuk toolbar button active */
.bg-indigo-500 {
  background-color: rgb(99 102 241);
}
.bg-indigo-500:hover {
  background-color: rgb(79 70 229);
}
</style>