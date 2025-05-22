<template>
  <div class="max-w-4xl mx-auto p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Form Inspeksi Kendaraan</h1>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold mb-4 text-blue-600 border-b pb-2">Detail Inspeksi</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Inspeksi</label>
            <input
              type="datetime-local"
              v-model="form.inspection_date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kendaraan</label>
            <select
              v-model="form.car_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
            >
              <option :value="null">-- Pilih Kendaraan --</option>
              <option v-for="car in cars" :key="car.id" :value="car.id">
                {{ car.brand.name }} {{ car.model.name }} ({{ car.license_plate }})
              </option>
            </select>
          </div>
        </div>
      </div>

      <div v-for="category in categories" :key="category.id" class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold mb-4 text-blue-600 border-b pb-2">{{ category.name }}</h2>

        <div v-for="point in category.points" :key="point.id" class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ point.name }}
            <span v-if="point.is_required" class="text-red-500">*</span>
          </label>

          <input
            v-if="point.input_type === 'text'"
            v-model="form.points[point.id]"
            :placeholder="point.placeholder"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
            @change="saveDraft"
          />

          <input
            v-else-if="point.input_type === 'number'"
            type="number"
            v-model.number="form.points[point.id]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
            @change="saveDraft"
          />

          <input
            v-else-if="point.input_type === 'account'"
            v-model="form.points[point.id]"
            @input="formatCurrency(point.id)"
            :placeholder="point.placeholder"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border text-right"
            @change="saveDraft"
          />

          <input
            v-else-if="point.input_type === 'date'"
            type="date"
            v-model="form.points[point.id]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
            @change="saveDraft"
          />

          <div v-else-if="point.input_type === 'image' || point.input_type === 'checkbox-T/F-Gambar'" class="mt-1">
            <div v-if="point.input_type === 'checkbox-T/F-Gambar'">
              <div class="flex space-x-4 mb-2">
                <label class="inline-flex items-center">
                  <input
                    type="radio"
                    v-model="form.points[point.id].option"
                    value="true"
                    @change="handleTfImageChange(point.id, true)"
                    class="text-blue-600 focus:ring-blue-500"
                  />
                  <span class="ml-2">Ada</span>
                </label>
                <label class="inline-flex items-center">
                  <input
                    type="radio"
                    v-model="form.points[point.id].option"
                    value="false"
                    @change="handleTfImageChange(point.id, false)"
                    class="text-blue-600 focus:ring-blue-500"
                  />
                  <span class="ml-2">Tidak Ada</span>
                </label>
              </div>
            </div>

            <div
              v-if="shouldShowImageUpload(point)"
              @click="triggerFileInput(point.id)"
              class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-blue-500 transition-colors"
            >
              <div class="flex flex-col items-center justify-center py-6">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-12 w-12 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
                <p class="mt-2 text-sm text-gray-600">Klik untuk upload gambar</p>
                <p class="text-xs text-gray-500">Format: JPG, PNG (Max 2MB)</p>
              </div>
              <input
                type="file"
                :id="'file-input-' + point.id"
                @change="handleImageUpload(point.id, $event)"
                accept="image/*"
                capture="camera"
                class="hidden"
              />
            </div>

            <div v-if="form.points[point.id]?.preview" class="mt-4">
              <img
                :src="form.points[point.id].preview"
                class="max-h-40 rounded-md shadow-sm border border-gray-200"
              />
              <button
                type="button"
                @click="removeImage(point.id)"
                class="mt-2 text-sm text-red-600 hover:text-red-800"
              >
                Hapus Gambar
              </button>
            </div>
          </div>

          <div v-else-if="point.input_type === 'checkbox'" class="mt-2">
            <label class="inline-flex items-center">
              <input
                type="checkbox"
                v-model="form.points[point.id]"
                :id="'point_' + point.id"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                @change="saveDraft"
              />
              <span class="ml-2 text-sm text-gray-700">
                {{ point.placeholder || 'Centang jika sesuai' }}
              </span>
            </label>
          </div>

          <div v-else-if="point.input_type === 'checkbox-Y/N-textarea'" class="mt-2 space-y-2">
            <div class="flex space-x-4">
              <label class="inline-flex items-center">
                <input
                  type="radio"
                  v-model="form.points[point.id].option"
                  value="true"
                  class="text-blue-600 focus:ring-blue-500"
                  @change="handleTfChange(point.id)"
                />
                <span class="ml-2">Ya</span>
              </label>
              <label class="inline-flex items-center">
                <input
                  type="radio"
                  v-model="form.points[point.id].option"
                  value="false"
                  class="text-blue-600 focus:ring-blue-500"
                  @change="handleTfChange(point.id)"
                />
                <span class="ml-2">Tidak</span>
              </label>
            </div>

            <textarea
              v-if="form.points[point.id]?.option === 'false'"
              v-model="form.points[point.id].notes"
              placeholder="Alasan ketidaksesuaian"
              rows="2"
              class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
              @change="saveDraft"
            ></textarea>
          </div>

          <textarea
            v-else-if="point.input_type === 'textarea'"
            v-model="form.points[point.id]"
            :placeholder="point.placeholder"
            rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
            @change="saveDraft"
          ></textarea>

          <select
            v-else-if="point.input_type === 'select'"
            v-model="form.points[point.id]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
            @change="saveDraft"
          >
            <option value="">-- Pilih --</option>
            <option
              v-for="(option, index) in point.placeholder.split(',')"
              :key="index"
              :value="option.trim()"
            >
              {{ option.trim() }}
            </option>
          </select>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md">
        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan</label>
        <textarea
          v-model="form.notes"
          rows="3"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
          @change="saveDraft"
        ></textarea>
      </div>

      <div class="flex justify-between items-center">
        <button
          type="button"
          @click="saveDraft"
          class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
        >
          Simpan Draft
        </button>
        <button
          type="submit"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          Simpan Inspeksi
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
  categories: Array,
  cars: Array,
  draftData: Object,
});

const form = useForm({
  car_id: null,
  inspection_date: new Date().toISOString().slice(0, 16),
  points: {},
  notes: null,
});

// Load draft data if exists
onMounted(() => {
  if (props.draftData) {
    form.car_id = props.draftData.car_id;
    form.inspection_date = props.draftData.inspection_date;
    form.notes = props.draftData.notes;
    form.points = props.draftData.points || {};
  }

  // Inisialisasi form.points dengan struktur yang benar
  if (props.categories) {
    props.categories.forEach((category) => {
      category.points.forEach((point) => {
        if (!form.points[point.id]) {
          form.points[point.id] = {}; // Pastikan setiap point.id memiliki objek, bukan undefined.
        }
        if (point.input_type === 'checkbox-T/F-Gambar' && form.points[point.id].option === undefined) {
          form.points[point.id].option = '';
        }
        if (point.input_type === 'checkbox-Y/N-textarea' && form.points[point.id].option === undefined) {
          form.points[point.id].option = '';
        }
      });
    });
  }
});

// Format currency input
const formatCurrency = (pointId) => {
  let value = form.points[pointId].replace(/\D/g, '');
  value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
  form.points[pointId] = value ? 'Rp ' + value : '';
};

// Handle image upload
const handleImageUpload = (pointId, event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    alert('Ukuran file maksimal 2MB');
    return;
  }

  form.points[pointId] = {
    ...form.points[pointId],
    file: file,
    preview: URL.createObjectURL(file),
  };

  saveDraft();
};

// Remove image
const removeImage = (pointId) => {
  form.points[pointId] = {
    ...form.points[pointId],
    file: null,
    preview: null,
  };
  saveDraft();
};

// Trigger file input
const triggerFileInput = (pointId) => {
  document.getElementById(`file-input-${pointId}`).click();
};

// Handle T/F image change
const handleTfImageChange = (pointId, value) => {
  if (!form.points[pointId]) {
    form.points[pointId] = {};
  }
  form.points[pointId].option = value.toString();
  saveDraft();
};
const handleTfChange = (pointId) => {
  if (!form.points[pointId]) {
    form.points[pointId] = {};
  }
  form.points[pointId].option = value.toString();
  saveDraft();
};

// Check if should show image upload
const shouldShowImageUpload = (point) => {
  if (point.input_type === 'checkbox-T/F-Gambar') {
    return form.points[point.id]?.option === 'true';
  }
  return true;
};

// Save draft automatically
const saveDraft = () => {
  router.post(
    route('inspections.save-draft'),
    {
      ...form.data(),
      _method: 'put',
    },
    {
      preserveScroll: true,
      preserveState: true,
    }
  );
};

// Submit final form
const submit = () => {
  const formData = new FormData();

  formData.append('car_id', form.car_id);
  formData.append('inspection_date', form.inspection_date);
  formData.append('notes', form.notes);

  Object.entries(form.points).forEach(([pointId, value]) => {
    if (value && typeof value === 'object') {
      if (value.file) {
        formData.append(`points[${pointId}][file]`, value.file);
      }
      if (value.option) { // Pastikan value.option ada.
        formData.append(`points[${pointId}][option]`, value.option);
      }
      if (value.notes) {
        formData.append(`points[${pointId}][notes]`, value.notes);
      }
    } else {
      formData.append(`points[${pointId}]`, value);
    }
  });

  form.post(route('inspections.store'), {
    forceFormData: true,
    onSuccess: () => {
      // Clear draft after successful submission
      router.delete(route('inspections.clear-draft'));
    },
  });
};
</script>

<style scoped>
/* Custom styling */
input[type="datetime-local"]::-webkit-calendar-picker-indicator {
  filter: invert(0.5);
}

input[type="date"]::-webkit-calendar-picker-indicator {
  filter: invert(0.5);
}

/* Animation for file upload */
.upload-area {
  transition: all 0.3s ease;
}

.upload-area:hover {
  border-color: #3b82f6;
  background-color: #f8fafc;
}
</style>
