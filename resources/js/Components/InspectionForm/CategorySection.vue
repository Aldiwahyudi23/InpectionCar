<template>
  <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100">
    <div class="bg-indigo-50 px-6 py-2 border-b border-indigo-100">
      <h2 class="text-xl font-semibold text-indigo-700">{{ category.name }} </h2>
    </div>
    
    <div class="p-4 space-y-4"> 
      <div v-for="point in category.points" :key="point.id" 
        class="space-y-2 pb-4 border-b border-gray-100 last:border-0 last:pb-0" >
        <div class="flex items-start justify-between">
          <label class="block text-sm font-medium text-gray-700">
            {{ point.name }}
            <span v-if="point.settings?.is_required" class="text-red-500">*</span>
          </label>
          <span 
            v-if="isPointComplete(point)"
            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
          >
            ✓
          </span>
        </div>
        
        <input-text
          v-if="point.input_type === 'text'"
          v-model="form.results[point.id].note"
          :required="point.settings?.is_required"
          :min-length="point.settings?.min_length"
          :max-length="point.settings?.max_length"
          :pattern="point.settings?.pattern"
          :placeholder="point.settings?.placeholder || 'Masukan text'"
          :error="form.errors[`results.${point.id}.note`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
        />
        
        <input-number
          v-if="point.input_type === 'number'"
          v-model="form.results[point.id].note"
          :required="point.settings?.is_required"
          :min="point.settings?.min"
          :max="point.settings?.max"
          :step="point.settings?.step || 1"
          :placeholder="point.settings?.placeholder || 'Masukan number'"
          :error="form.errors[`results.${point.id}.note`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
        />

      <input-account
        v-if="point.input_type === 'account'"
        v-model="form.results[point.id].note"
        :required="point.settings?.is_required"
        :placeholder="point.settings?.placeholder || 'Masukkan nilai'"
        :error="form.errors[`results.${point.id}.note`]"
        :point-id="point.id"
        :settings="point.settings"
        @update:modelValue="updateResult(point.id, $event)"
        @save="saveResult(point.id)"
      />
       
        
        <input-date
          v-if="point.input_type === 'date'"
          v-model="form.results[point.id].note"
          :required="point.settings?.is_required"
          :min-date="point.settings?.min_date"
          :max-date="point.settings?.max_date"
          :error="form.errors[`results.${point.id}.note`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
        />
        
        <input-textarea
          v-if="point.input_type === 'textarea'"
          v-model="form.results[point.id].note"
          :required="point.settings?.is_required"
          :min-length="point.settings?.min_length"
          :max-length="point.settings?.max_length"
          :placeholder="point.settings?.placeholder || 'Masukkan teks di sini'"
          :settings="point.settings"  
          :error="form.errors[`results.${point.id}.note`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
        />

        <input-radio
          v-if="point.input_type === 'radio'"
          v-model="form.results[point.id].status"
          :notes="form.results[point.id].note"
          :images="form.images[point.id]"
          :required="point.settings?.is_required"
          :point-id="point.id"  
          :point="point"
          :inspection-id="inspectionId" 
          :settings="point.settings"  
          :point-name="point.name"
          :selected-point="point.app_menu.input_type === 'damage' ? point : null"
          :options="point.settings?.radios || defaultRadioOptions"
          :error="form.errors[`results.${point.id}.status`]"
          @update:modelValue="updateResult(point.id, $event)"
          @update:notes="val => form.results[point.id].note = val"
          @update:images="val => form.images[point.id] = val"
          @save="saveResult(point.id)"
          @hapus="HapusPoint(point.id)"
        />

        <InputImageToRadio
          v-if="point.input_type === 'imageTOradio'"
          v-model="form.results[point.id].status"
          :notes="form.results[point.id].note"
          :images="form.images[point.id]"
          :required="point.settings?.is_required"
          :point-id="point.id"  
          :inspection-id="inspectionId" 
          :settings="point.settings"  
          :point-name="point.name"
          :point = "point"
          :selected-point="point.app_menu.input_type === 'damage' ? point : null"
          :options="point.settings?.radios || defaultRadioOptions"
          :error="form.errors[`results.${point.id}.status`]"
          @update:modelValue="updateResult(point.id, $event)"
          @update:notes="val => form.results[point.id].note = val"
          @update:images="val => form.images[point.id] = val"
          @save="saveResult(point.id)"
          @hapus="HapusPoint(point.id)"
        />

        
       <input-image
          v-if="point.input_type === 'image'"
          v-model="form.images[point.id]"
          :error="form.errors[`images.${point.id}`]"
          :inspection-id="inspectionId"
          :point-id="point.id"
          :point="point"
          :point-name="point.name"
          :settings="point.settings"
          @update:notes="val => form.results[point.id].note = val"
          @update:status="val => form.results[point.id].status = val"
        />

         <input-select
          v-if="point.input_type === 'select'"
          v-model="form.results[point.id].status"
          :required="point.settings?.is_required"
          :error="form.errors[`results.${point.id}.status`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
        />
        
      </div>
    </div>
  </div>
</template>

<script setup>
import InputText from './InputText.vue';
import InputNumber from './InputNumber.vue';
import InputDate from './InputDate.vue';
import InputAccount from './InputAccount.vue';
import InputTextarea from './InputTextarea.vue';
import InputSelect from './InputSelect.vue';
import InputRadio from './InputRadio.vue';
import InputImage from './InputImage.vue';
import InputImageToRadio from './InputImageToRadio.vue';
import { Ham } from 'lucide-vue-next';

const props = defineProps({
  category: Object,
  form: Object,
  inspectionId: String,
  selectedPoint: Object,
});

const emit = defineEmits(['saveResult', 'updateResult', 'removeImage']);

const defaultRadioOptions = [
  { value: 'good', label: 'Good' },
  { value: 'bad', label: 'Bad' },
  { value: 'na', label: 'N/A' }
];

const isPointComplete = (point) => {
  const result = props.form.results[point.id];
  const image = props.form.images[point.id];

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
      if (!result.status) return false;
        
        // Cek jika opsi yang dipilih memiliki requirements tambahan
        const selectedOption = point.settings.radios?.find(opt => opt.value === result.status);
        if (selectedOption?.settings) {
          // Validasi textarea jika required
          if (selectedOption.settings.show_textarea && !result.note?.trim()) {
            return false;
          }
          // Validasi image jika required
          if (selectedOption.settings.show_image_upload && image?.length === 0) {
            return false;
          }
        }
        return true;

     case 'imageTOradio':
        // Harus ada gambar DAN pilihan radio
        if (image?.length === 0 || !result.status) return false;
        
        // Cek requirements tambahan dari opsi yang dipilih
        const selectedOptionImage = point.settings.radios?.find(opt => opt.value === result.status);
        if (selectedOptionImage?.settings) {
          // Validasi textarea jika required
          if (selectedOptionImage.settings.show_textarea && !result.note?.trim()) {
            return false;
          }
        }
        return true;

    case 'image':
      return image.length > 0;

    default:
        return !!result.status || !!result.note?.trim();
  }
};


const updateResult = (pointId, value) => {
  emit('updateResult', { pointId, value });
};

const saveResult = (pointId) => {
  emit('saveResult', pointId);
};

const removeImage = (pointId, imageIndex) => {
  emit('removeImage', { pointId, imageIndex });
};

const HapusPoint = (pointId) => {
    emit("hapusPoint", pointId);
};
</script>

<style scoped>
/* Mobile-first styles (tetap relevan, namun tidak langsung mempengaruhi spacing ini) */
@media (min-width: 640px) {
  .point-card {
    padding: 1.25rem;
  }
}
</style>