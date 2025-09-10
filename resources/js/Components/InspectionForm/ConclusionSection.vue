<template>
  <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100">
    <div class="bg-indigo-200 px-6 py-2 border-b">
      <h3 class="text-xl font-semibold text-indigo-700">Kesimpulan Inspeksi</h3>
    </div>
    <div class="p-4 space-y-4-6">
      <label class="block text-sm font-medium text-gray-700 mb-3">
        Apakah kendaraan pernah terkena banjir?
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
        <label class="block text-sm font-medium text-gray-700 mb-3">
          Tingkat kerusakan: <span class="text-red-500">*</span>
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

    <div class="p-4 space-y-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Kesimpulan</label>
      
      <div class="flex gap-1 mb-2 p-2 bg-gray-100 rounded-md">
        <button
          type="button"
          @click="formatText('bold')"
          class="p-2 rounded hover:bg-gray-200 transition-colors"
          title="Bold"
        >
          <strong>B</strong>
        </button>
        <button
          type="button"
          @click="formatText('italic')"
          class="p-2 rounded hover:bg-gray-200 transition-colors"
          title="Italic"
        >
          <em>I</em>
        </button>
        <button
          type="button"
          @click="formatText('underline')"
          class="p-2 rounded hover:bg-gray-200 transition-colors"
          title="Underline"
        >
          <u>U</u>
        </button>
        <button
          type="button"
          @click="insertBulletList()"
          class="p-2 rounded hover:bg-gray-200 transition-colors"
          title="Bullet List"
        >
          • List
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

      <div
        ref="editorRef"
        contenteditable="true"
        class="w-full min-h-[120px] p-3 rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 prose max-w-none"
        :class="{ 'bg-white': !isEditing, 'bg-blue-50': isEditing }"
        @input="handleEditorInput"
        @blur="saveContent"
        @focus="isEditing = true"
        @keydown.enter="handleEnterKey"
        @paste="handlePaste"
        placeholder="Tambahkan catatan kesimpulan inspeksi di sini..."
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick, computed } from 'vue'
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

// Helper function untuk parse settings dengan aman
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

const conclusionSettings = computed(() => {
  const settings = parseSettings(props.inspection.settings);
  return settings.conclusion || {};
});

const form = ref({
  flooded: conclusionSettings.value.flooded || '',
  collision: conclusionSettings.value.collision || '',
  collision_severity: conclusionSettings.value.collision_severity || '',
  conclusion_note: props.inspection.notes || ''
})

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
  { value: 'heavy', label: 'Berat' },
]

onMounted(() => {
  initializeForm();
  nextTick(() => {
    initializeEditor();
  });
});

const initializeEditor = () => {
  if (editorRef.value && form.value.conclusion_note) {
    editorRef.value.innerHTML = form.value.conclusion_note;
  }
}

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

const formatText = (format) => {
  if (!editorRef.value) return;
  
  editorRef.value.focus();
  
  switch (format) {
    case 'bold':
      document.execCommand('bold', false, null);
      break;
    case 'italic':
      document.execCommand('italic', false, null);
      break;
    case 'underline':
      document.execCommand('underline', false, null);
      break;
  }
  
  handleEditorInput();
}

const insertBulletList = () => {
  if (!editorRef.value) return;
  
  editorRef.value.focus();
  document.execCommand('insertUnorderedList', false, null);
  handleEditorInput();
}

const clearFormatting = () => {
  if (!editorRef.value) return;
  
  editorRef.value.focus();
  document.execCommand('removeFormat', false, null);
  document.execCommand('unlink', false, null);
  handleEditorInput();
}

const saveContent = () => {
  isEditing.value = false;
  if (editorRef.value) {
    form.value.conclusion_note = editorRef.value.innerHTML;
    saveToServer();
  }
}

const saveToServer = debounce(() => {
  const selection = window.getSelection();
  const savedRange = selection.rangeCount > 0 ? selection.getRangeAt(0) : null;
  
  const dataToSend = {
    flooded: form.value.flooded,
    collision: form.value.collision,
    collision_severity: form.value.collision === 'yes' ? form.value.collision_severity : null,
    conclusion_note: form.value.conclusion_note
  }

  router.post(
    route('inspections.updateConclusion', props.inspectionId),
    dataToSend,
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        nextTick(() => {
          if (savedRange && editorRef.value) {
            const newSelection = window.getSelection();
            newSelection.removeAllRanges();
            newSelection.addRange(savedRange);
            editorRef.value.focus();
            
            if (savedRange.collapsed) {
              const range = document.createRange();
              range.selectNodeContents(editorRef.value);
              range.collapse(false);
              newSelection.removeAllRanges();
              newSelection.addRange(range);
            }
          }
        });
      },
      onError: (errors) => {
        console.error('Error saving conclusion:', errors);
      }
    }
  )
}, 500)

watch([
  () => form.value.flooded,
  () => form.value.collision,
  () => form.value.collision_severity
], () => {
  saveToServer()
})

const initializeForm = () => {
  const settings = parseSettings(props.inspection.settings);
  const conclusion = settings.conclusion || {};
  
  form.value = {
    flooded: conclusion.flooded || '',
    collision: conclusion.collision || '',
    collision_severity: conclusion.collision_severity || '',
    conclusion_note: props.inspection.notes || ''
  };
};

watch(() => props.inspection, () => {
  initializeForm();
  nextTick(() => {
    initializeEditor();
  });
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
</style>