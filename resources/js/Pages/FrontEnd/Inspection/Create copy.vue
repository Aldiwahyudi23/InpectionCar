<!-- resources/js/Pages/Inspections/Create.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Vehicle Inspection: {{ inspection.car?.brand_id || 'Unknown Vehicle' }}</h1>
    
    <form @submit.prevent="submitForm" class="space-y-8">
      <div v-for="category in categories" :key="category.id" class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">{{ category.name }}</h2>
        
        <div v-for="point in category.points" :key="point.id" class="mb-6">
          <div class="flex items-start justify-between mb-2">
            <label class="block text-sm font-medium text-gray-700">
              {{ point.name }}
              <span v-if="point.settings?.is_required" class="text-red-500">*</span>
            </label>
          </div>
          
          <!-- Text Input -->
          <div v-if="point.input_type === 'text'" class="mt-1">
            <input
              v-model="form.results[point.id].value"
              :type="point.input_type"
              :required="point.settings?.is_required"
              :minlength="point.settings?.min_length"
              :maxlength="point.settings?.max_length"
              :pattern="point.settings?.pattern"
              :placeholder="point.settings?.placeholder"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
          
          <!-- Number Input -->
          <div v-if="point.input_type === 'number'" class="mt-1">
            <input
              v-model="form.results[point.id].value"
              :type="point.input_type"
              :required="point.settings?.is_required"
              :min="point.settings?.min"
              :max="point.settings?.max"
              :step="point.settings?.step"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
          
          <!-- Date Input -->
          <div v-if="point.input_type === 'date'" class="mt-1">
            <input
              v-model="form.results[point.id].value"
              type="date"
              :required="point.settings?.is_required"
              :min="point.settings?.min_date"
              :max="point.settings?.max_date"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
          
          <!-- Textarea -->
          <div v-if="point.input_type === 'textarea'" class="mt-1">
            <textarea
              v-model="form.results[point.id].value"
              :required="point.settings?.is_required"
              :minlength="point.settings?.min_length"
              :maxlength="point.settings?.max_length"
              :placeholder="point.settings?.placeholder"
              rows="3"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
          </div>
          
          <!-- Checkbox (single or multiple) -->
          <div v-if="point.input_type === 'checkbox'" class="mt-2 space-y-2">
            <div v-for="option in point.settings?.options" :key="option.value" class="flex items-center">
              <input
                v-model="form.results[point.id].value"
                type="checkbox"
                :value="option.value"
                :required="point.settings?.is_required && form.results[point.id].value.length === 0"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              <label class="ml-2 block text-sm text-gray-700">{{ option.label }}</label>
            </div>
          </div>
          
          <!-- Select Dropdown -->
          <div v-if="point.input_type === 'select'" class="mt-1">
            <select
              v-model="form.results[point.id].value"
              :required="point.settings?.is_required"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">-- Select an option --</option>
              <option v-for="option in point.settings?.options" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
          
          <!-- Option with conditional fields -->
          <div v-if="point.input_type === 'option'" class="space-y-4">
            <div class="mt-1">
              <select
                v-model="form.results[point.id].value"
                :required="point.settings?.is_required"
                @change="handleOptionChange(point)"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">-- Select an option --</option>
                <option v-for="option in point.settings?.options" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>
            
            <!-- Conditional textarea for option -->
            <div v-if="showTextareaForOption(point)" class="mt-2">
              <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
              <textarea
                v-model="form.results[point.id].notes"
                :required="isTextareaRequiredForOption(point)"
                :minlength="point.settings?.textarea_min_length"
                :maxlength="point.settings?.textarea_max_length"
                :placeholder="point.settings?.textarea_placeholder"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              ></textarea>
            </div>
            
            <!-- Conditional image upload for option -->
            <div v-if="showImageUploadForOption(point)" class="mt-2">
              <label class="block text-sm font-medium text-gray-700">Upload Images</label>
              <input
                type="file"
                multiple
                @change="handleImageUpload($event, point.id)"
                :required="isImageUploadRequiredForOption(point)"
                accept="image/*"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
              />
              <div v-if="form.results[point.id].images?.length" class="mt-2 flex flex-wrap gap-2">
                <div v-for="(image, index) in form.results[point.id].images" :key="index" class="relative">
                  <img :src="image.preview" class="h-20 w-20 object-cover rounded" />
                  <button
                    type="button"
                    @click="removeImage(point.id, index)"
                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs"
                  >
                    ×
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Image Upload -->
          <div v-if="point.input_type === 'image'" class="mt-2">
            <input
              type="file"
              multiple
              @change="handleImageUpload($event, point.id)"
              :required="point.settings?.is_required"
              accept="image/*"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
            />
            <div v-if="form.results[point.id].images?.length" class="mt-2 flex flex-wrap gap-2">
              <div v-for="(image, index) in form.results[point.id].images" :key="index" class="relative">
                <img :src="image.preview" class="h-20 w-20 object-cover rounded" />
                <button
                  type="button"
                  @click="removeImage(point.id, index)"
                  class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs"
                >
                  ×
                </button>
              </div>
            </div>
          </div>
          
          <!-- Notes field for all input types -->
          <div class="mt-2">
            <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
            <textarea
              v-model="form.results[point.id].notes"
              rows="2"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
          </div>
        </div>
      </div>
      
      <div class="flex justify-end">
        <button
          type="submit"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"
        >
          Submit Inspection
        </button>
      </div>
    </form>
  </div>
</template>


<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
  inspection: Object,
  categories: Array,
});

// Initialize form structure safely
const initializeFormResults = () => {
  const results = {};
  
  // Ensure categories is an array
  if (Array.isArray(props.categories)) {
    props.categories.forEach(category => {
      // Ensure inspection_points exists and is an array
      if (category.inspection_points && Array.isArray(category.inspection_points)) {
        category.inspection_points.forEach(point => {
          results[point.id] = {
            inspection_point_id: point.id,
            value: point.input_type === 'checkbox' ? [] : '',
            notes: '',
            images: [],
          };
        });
      }
    });
  }
  
  return results;
};

const form = useForm({
  inspection_id: props.inspection.id,
  results: initializeFormResults(),
});

// Handle option type changes
const handleOptionChange = (point) => {
  if (!point.settings?.options || !form.results[point.id]) return;
  
  const selectedOption = point.settings.options.find(
    opt => opt.value === form.results[point.id].value
  );
  
  // Reset notes and images when option changes
  if (selectedOption) {
    form.results[point.id].notes = '';
    form.results[point.id].images = [];
  }
};

// Check if textarea should be shown for option
const showTextareaForOption = (point) => {
  if (point.input_type !== 'option') return false;
  if (!point.settings?.show_textarea) return false;
  if (!form.results[point.id]) return false;
  
  const selectedOption = point.settings.options?.find(
    opt => opt.value === form.results[point.id].value
  );
  
  return selectedOption && point.settings.show_textarea;
};

// Check if image upload should be shown for option
const showImageUploadForOption = (point) => {
  if (point.input_type !== 'option') return false;
  if (!point.settings?.show_image_upload) return false;
  if (!form.results[point.id]) return false;
  
  const selectedOption = point.settings.options?.find(
    opt => opt.value === form.results[point.id].value
  );
  
  return selectedOption && point.settings.show_image_upload;
};

// Handle image upload with safety checks
const handleImageUpload = (event, pointId) => {
  if (!event.target.files || !form.results[pointId]) return;
  
  const files = Array.from(event.target.files);
  
  files.forEach(file => {
    const reader = new FileReader();
    reader.onload = (e) => {
      if (!form.results[pointId].images) {
        form.results[pointId].images = [];
      }
      form.results[pointId].images.push({
        file,
        preview: e.target.result,
      });
    };
    reader.readAsDataURL(file);
  });
  
  event.target.value = '';
};

// Remove image with safety check
const removeImage = (pointId, index) => {
  if (form.results[pointId]?.images) {
    form.results[pointId].images.splice(index, 1);
  }
};

// Submit form with validation
const submitForm = () => {
  if (!form.results) {
    console.error('No results data available');
    return;
  }

  const formData = new FormData();
  formData.append('inspection_id', form.inspection_id);
  
  // Prepare results data with safety checks
  const results = Object.values(form.results)
    .filter(result => result !== null && typeof result === 'object')
    .map(result => {
      const resultData = { ...result };
      
      if (resultData.images && Array.isArray(resultData.images)) {
        resultData.images = resultData.images
          .filter(img => img && img.file)
          .map(img => img.file);
      } else {
        delete resultData.images;
      }
      
      return resultData;
    });
  
  formData.append('results', JSON.stringify(results));
  
  // Append image files with safety checks
  Object.values(form.results)
    .filter(result => result && result.images && Array.isArray(result.images))
    .forEach(result => {
      result.images.forEach((img, index) => {
        if (img && img.file) {
          formData.append(`images[${result.inspection_point_id}][${index}]`, img.file);
        }
      });
    });
  
  form.post(route('inspections.store-results', props.inspection.id), {
    forceFormData: true,
    onSuccess: () => form.reset(),
  });
};
</script>