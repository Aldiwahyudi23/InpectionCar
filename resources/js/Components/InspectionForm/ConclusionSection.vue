<template>
  <div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Kesimpulan Inspeksi</h2>
    
    <!-- Banjir -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-3">Apakah kendaraan pernah terkena banjir?</label>
      <div class="flex space-x-4">
        <label class="inline-flex items-center">
          <input
            type="radio"
            v-model="form.flooded"
            value="yes"
            @change="updateForm"
            class="text-indigo-600 focus:ring-indigo-500"
          >
          <span class="ml-2">Ya</span>
        </label>
        <label class="inline-flex items-center">
          <input
            type="radio"
            v-model="form.flooded"
            value="no"
            @change="updateForm"
            class="text-indigo-600 focus:ring-indigo-500"
          >
          <span class="ml-2">Tidak</span>
        </label>
      </div>
    </div>

    <!-- Tabrakan -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-3">Apakah kendaraan pernah mengalami tabrakan?</label>
      <div class="flex space-x-4">
        <label class="inline-flex items-center">
          <input
            type="radio"
            v-model="form.collision"
            value="yes"
            @change="updateForm"
            class="text-indigo-600 focus:ring-indigo-500"
          >
          <span class="ml-2">Ya</span>
        </label>
        <label class="inline-flex items-center">
          <input
            type="radio"
            v-model="form.collision"
            value="no"
            @change="updateForm"
            class="text-indigo-600 focus:ring-indigo-500"
          >
          <span class="ml-2">Tidak</span>
        </label>
      </div>

      <!-- Tingkat Kerusakan (jika ya) -->
      <div v-if="form.collision === 'yes'" class="mt-4 ml-6">
        <label class="block text-sm font-medium text-gray-700 mb-3">Tingkat kerusakan:</label>
        <div class="flex space-x-4">
          <label class="inline-flex items-center">
            <input
              type="radio"
              v-model="form.collision_severity"
              value="light"
              @change="updateForm"
              class="text-indigo-600 focus:ring-indigo-500"
            >
            <span class="ml-2">Ringan</span>
          </label>
          <label class="inline-flex items-center">
            <input
              type="radio"
              v-model="form.collision_severity"
              value="heavy"
              @change="updateForm"
              class="text-indigo-600 focus:ring-indigo-500"
            >
            <span class="ml-2">Berat</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Catatan Kesimpulan -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Kesimpulan</label>
      <textarea
        v-model="form.conclusion_note"
        @input="updateForm"
        rows="4"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        placeholder="Tambahkan catatan kesimpulan inspeksi di sini..."
      ></textarea>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  form: Object
});

const emit = defineEmits(['updateConclusion']);

// Local form data
const form = ref({
  flooded: props.form.conclusion?.flooded || 'no',
  collision: props.form.conclusion?.collision || 'no',
  collision_severity: props.form.conclusion?.collision_severity || 'light',
  conclusion_note: props.form.conclusion?.conclusion_note || ''
});

// Update ketika props berubah
watch(() => props.form.conclusion, (newConclusion) => {
  if (newConclusion) {
    form.value = { ...form.value, ...newConclusion };
  }
}, { deep: true });

const updateForm = () => {
  emit('updateConclusion', form.value);
};
</script>
