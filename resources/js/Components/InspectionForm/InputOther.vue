<template>
  <div class="bg-white rounded-lg p-6 shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Tambahan</h2>
    <p class="text-gray-600 mb-6">Tambahkan catatan atau informasi lain yang relevan untuk inspeksi ini.</p>

    <div class="mb-4">
      <label for="additional_note" class="block text-sm font-medium text-gray-700">Catatan Tambahan</label>
      <textarea
        id="additional_note"
        v-model="internalNote"
        @input="debounceUpdate"
        rows="4"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        placeholder="Masukkan catatan tambahan di sini..."
      ></textarea>
    </div>

    <div class="flex justify-end">
      <button
        @click="updateData"
        class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Simpan Catatan
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
  form: Object, // Menerima objek form dari komponen induk
});

const emit = defineEmits(['updateData']);

// Gunakan ref lokal untuk mengelola data input
const internalNote = ref(props.form.additional_data?.[0]?.value || '');

// Fungsi untuk memancarkan event ke komponen induk
const updateData = () => {
  emit('updateData', {
    pointId: 'additional_note', // ID unik untuk data ini
    value: internalNote.value
  });
};

// Debounce untuk memanggil updateData secara otomatis
const debounceUpdate = debounce(updateData, 500);

// Watcher untuk mengsinkronkan data dari props (jika ada perubahan dari luar)
watch(() => props.form.additional_data, (newValue) => {
  if (newValue && newValue.length > 0) {
    internalNote.value = newValue[0].value;
  }
}, { deep: true });
</script>

<style scoped>
/* Anda bisa menambahkan styling khusus di sini */
</style>