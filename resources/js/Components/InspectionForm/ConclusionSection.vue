<template>
  <div class="bg-white rounded-xl shadow-md p-4">
    <h4 class="text-2xl font-semibold text-gray-800 mb-4">Kesimpulan Inspeksi</h4>

    <!-- Banjir -->
    <div class="mb-6">
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

    <!-- Tabrakan -->
    <div class="mb-6">
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

      <!-- Tingkat Kerusakan -->
      <div v-if="form.collision === 'yes'" class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-3">Tingkat kerusakan:</label>
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

    <!-- Catatan -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Kesimpulan</label>
      <textarea
        v-model="form.conclusion_note"
        rows="4"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        placeholder="Tambahkan catatan kesimpulan inspeksi di sini..."
        @input="debouncedSave"
      ></textarea>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash'

const props = defineProps({
  inspectionId: { type: Number, required: true },
  inspection: {
    type: Object,
    required: true
  },
})

// Helper function untuk parse settings dengan aman
const parseSettings = (settings) => {
  if (!settings) return {};
  
  // Jika settings adalah string, coba parse sebagai JSON
  if (typeof settings === 'string') {
    try {
      return JSON.parse(settings) || {};
    } catch (e) {
      console.error('Error parsing settings JSON:', e);
      return {};
    }
  }
  
  // Jika settings sudah object, return langsung
  if (typeof settings === 'object' && settings !== null) {
    return settings;
  }
  
  return {};
}

// Ambil data conclusion dari settings yang sudah di-parse
const conclusionSettings = computed(() => {
  const settings = parseSettings(props.inspection.settings);
  return settings.conclusion || {};
});

// Gunakan ref untuk reactive state
const form = ref({
  flooded: conclusionSettings.value.flooded || '',
  collision: conclusionSettings.value.collision || '',
  collision_severity: conclusionSettings.value.collision_severity || '',
  conclusion_note: props.inspection.notes || '' // Ambil dari inspection.note, bukan settings
})

// Options untuk radio
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

// Fungsi untuk menyimpan data ke server
const saveToServer = () => {
  // Siapkan data untuk dikirim
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
      onError: (errors) => {
        console.error('Error saving conclusion:', errors);
      }
    }
  )
}

// Debounce untuk textarea
const debouncedSave = debounce(saveToServer, 500)

// Watch untuk perubahan pada radio buttons
watch([
  () => form.value.flooded,
  () => form.value.collision,
  () => form.value.collision_severity
], () => {
  saveToServer()
})

// Inisialisasi form saat component mounted
onMounted(() => {
  initializeForm();
});

// Watch untuk perubahan props.inspection
watch(() => props.inspection, () => {
  initializeForm();
}, { deep: true });

// Fungsi untuk inisialisasi form
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
</script>