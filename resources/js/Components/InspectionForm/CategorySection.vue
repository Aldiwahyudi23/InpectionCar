<template>
  <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
    <div class="bg-indigo-50 px-6 py-2 border-b border-indigo-100">
      <h2 class="text-xl font-semibold text-indigo-700">{{ category.name }}</h2>
    </div>
    
    <div class="p-6 space-y-8">
      <div 
        v-for="point in category.points" 
        :key="point.id" 
        class="space-y-4 pb-6 border-b border-gray-100 last:border-0 last:pb-0"
      >
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
        
        <!-- Text Input -->
        <input-text
          v-if="point.input_type === 'text'"
          v-model="form.results[point.id].note"
          :required="point.settings?.is_required"
          :min-length="point.settings?.min_length"
          :max-length="point.settings?.max_length"
          :pattern="point.settings?.pattern"
          :placeholder="point.settings?.placeholder || 'Enter text'"
          :error="form.errors[`results.${point.id}.note`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
        />
        
        <!-- Number Input -->
        <input-number
          v-if="point.input_type === 'number'"
          v-model="form.results[point.id].note"
          :required="point.settings?.is_required"
          :min="point.settings?.min"
          :max="point.settings?.max"
          :step="point.settings?.step || 1"
          :placeholder="point.settings?.placeholder || 'Enter number'"
          :error="form.errors[`results.${point.id}.note`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
        />
        
        <!-- Date Input -->
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
        
        <!-- Select Input -->
        <input-select
          v-if="point.input_type === 'select'"
          v-model="form.results[point.id].status"
          :required="point.settings?.is_required"
          :error="form.errors[`results.${point.id}.status`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
        />
        
        <!-- Radio Input -->
        <input-radio
          v-if="point.input_type === 'radio'"
          v-model="form.results[point.id].status"
          :required="point.settings?.is_required"
          :options="point.settings?.radios || defaultRadioOptions"
          :error="form.errors[`results.${point.id}.status`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
        />
        
        <!-- Image Input -->
        <input-image
          v-if="point.input_type === 'image'"
          v-model="form.results[point.id].images"
          :error="form.errors[`results.${point.id}.images`]"
          @update:modelValue="updateResult(point.id, $event)"
          @save="saveResult(point.id)"
          @removeImage="removeImage(point.id, $event)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import InputText from './InputText.vue';
import InputNumber from './InputNumber.vue';
import InputDate from './InputDate.vue';
import InputSelect from './InputSelect.vue';
import InputRadio from './InputRadio.vue';
import InputImage from './InputImage.vue';

const props = defineProps({
  category: Object,
  form: Object
});

const emit = defineEmits(['saveResult', 'updateResult', 'removeImage']);

const defaultRadioOptions = [
  { value: 'good', label: 'Good' },
  { value: 'bad', label: 'Bad' },
  { value: 'na', label: 'N/A' }
];

const isPointComplete = (point) => {
  const result = props.form.results[point.id];
  if (!result) return false;
  
  switch(point.input_type) {
    case 'text':
    case 'number':
    case 'date':
      return !!result.note;
    case 'select':
    case 'radio':
      return !!result.status;
    case 'image':
      return result.images?.length > 0;
    default:
      return false;
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
</script>

<style scoped>
/* Mobile-first styles */
@media (min-width: 640px) {
  .point-card {
    padding: 1.25rem;
  }
}
</style>