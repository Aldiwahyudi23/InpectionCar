<template>
  <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Vehicle Inspection</h1>
      <h2 class="text-xl font-semibold text-indigo-600">
        {{ inspection.car?.brand_id || 'Unknown Vehicle' }}
      </h2>
    </div>
    
    <form @submit.prevent="submitAll" class="space-y-8">
      <div 
        v-for="category in categories" 
        :key="category.id" 
        class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100"
      >
        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
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
            </div>
            
            <!-- Text Input -->
            <div v-if="point.input_type === 'text'" class="mt-1">
              <input
                v-model="form.results[point.id].note"
                @input="debouncedSave(point.id)"
                type="text"
                :required="point.settings?.is_required"
                :minlength="point.settings?.min_length"
                :maxlength="point.settings?.max_length"
                :pattern="point.settings?.pattern"
                :placeholder="point.settings?.placeholder || 'Enter text'"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
              />
              <p v-if="form.errors[`results.${point.id}.note`]" class="mt-2 text-sm text-red-600">
                {{ form.errors[`results.${point.id}.note`] }}
              </p>
            </div>
            
            <!-- Number Input -->
            <div v-if="point.input_type === 'number'" class="mt-1">
              <input
                v-model.number="form.results[point.id].note"
                @input="debouncedSave(point.id)"
                type="number"
                :required="point.settings?.is_required"
                :min="point.settings?.min"
                :max="point.settings?.max"
                :step="point.settings?.step || 1"
                :placeholder="point.settings?.placeholder || 'Enter number'"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
              />
              <p v-if="form.errors[`results.${point.id}.note`]" class="mt-2 text-sm text-red-600">
                {{ form.errors[`results.${point.id}.note`] }}
              </p>
            </div>
            
            <!-- Date Input -->
            <div v-if="point.input_type === 'date'" class="mt-1">
              <input
                v-model="form.results[point.id].note"
                @change="saveResult(point.id)"
                type="date"
                :required="point.settings?.is_required"
                :min="point.settings?.min_date"
                :max="point.settings?.max_date"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
              />
              <p v-if="form.errors[`results.${point.id}.note`]" class="mt-2 text-sm text-red-600">
                {{ form.errors[`results.${point.id}.note`] }}
              </p>
            </div>
            
            <!-- Select Dropdown -->
            <div v-if="point.input_type === 'select'" class="mt-1">
              <select
                v-model="form.results[point.id].status"
                @change="saveResult(point.id)"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150"
              >
                <option value="" disabled>Select status</option>
                <option value="good">Good</option>
                <option value="bad">Bad</option>
                <option value="na">N/A</option>
              </select>
              <p v-if="form.errors[`results.${point.id}.status`]" class="mt-2 text-sm text-red-600">
                {{ form.errors[`results.${point.id}.status`] }}
              </p>
            </div>
            
            <!-- Radio Buttons -->
            <!-- Radio Buttons -->
            <div v-if="point.input_type === 'radio'" class="mt-2">
              <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <label
                  v-for="option in point.settings?.radios || []"
                  :key="option.value"
                  class="inline-flex items-center space-x-2"
                >
                  <input
                    type="radio"
                    :name="'radio-' + point.id"
                    :value="option.value"
                    v-model="form.results[point.id].status"
                    @change="saveResult(point.id)"
                    :required="point.settings?.is_required"
                    class="text-indigo-600 focus:ring-indigo-500 border-gray-300"
                  />
                  <span class="text-sm text-gray-700">{{ option.label }}</span>
                </label>
              </div>
              <p v-if="form.errors[`results.${point.id}.status`]" class="mt-2 text-sm text-red-600">
                {{ form.errors[`results.${point.id}.status`] }}
              </p>
            </div>

            
            <!-- Enhanced Image Upload -->
            <div v-if="point.input_type === 'image'" class="mt-4">
              <input
                type="file"
                @change="handleImageUpload($event, point.id)"
                accept="image/*"
                class="hidden"
                :id="'image-upload-'+point.id"
                :ref="'imageUpload-'+point.id"
              />
              
              <label 
                :for="'image-upload-'+point.id"
                class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition duration-200"
                :class="{
                  'border-gray-300 hover:border-indigo-400 bg-gray-50 hover:bg-indigo-50': form.results[point.id].images.length === 0,
                  'border-indigo-300 bg-indigo-50': form.results[point.id].images.length > 0
                }"
              >
                <template v-if="form.results[point.id].images.length === 0">
                  <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <p class="text-sm text-gray-500 font-medium">Upload Image</p>
                  <p class="text-xs text-gray-400 mt-1">Click to browse or drag and drop</p>
                </template>
                
                <template v-else>
                  <div class="relative w-full h-full flex items-center justify-center">
                    <div class="flex flex-wrap gap-2 p-2">
                      <div v-for="(image, idx) in form.results[point.id].images" :key="idx" class="relative group">
                        <img 
                          :src="image.preview || '/storage/'+image.image_path" 
                          class="h-20 w-20 object-cover rounded-lg border border-gray-200 shadow-sm"
                        >
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition duration-200 flex items-center justify-center">
                          <button 
                            @click.stop="removeImage(point.id, idx)"
                            type="button"
                            class="opacity-0 group-hover:opacity-100 bg-red-500 text-white rounded-full h-6 w-6 flex items-center justify-center transition duration-200 transform group-hover:scale-100 scale-90"
                          >
                            ×
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="absolute bottom-2 right-2">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        + Add More
                      </span>
                    </div>
                  </div>
                </template>
              </label>
              
              <p v-if="form.errors[`results.${point.id}.images`]" class="mt-2 text-sm text-red-600">
                {{ form.errors[`results.${point.id}.images`] }}
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="flex justify-end gap-4">
        <button
          type="submit"
          :disabled="form.processing"
          class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ form.processing ? 'Submitting...' : 'Final Submit Inspection' }}</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const props = defineProps({
  inspection: Object,
  categories: Array,
  existingResults: Array,
  existingImages: Object,
});

const initializeForm = () => {
  const results = {};
  
  props.categories.forEach(category => {
    (category.points || []).forEach(point => {
      const existing = Array.isArray(props.existingResults)
        ? props.existingResults.find(r => r.point_id === point.id)
        : null;
      
      results[point.id] = {
        status: existing?.status || '',
        note: existing?.note || '',
        images: props.existingImages?.[point.id]?.map(img => ({
          image_path: img.image_path,
          preview: null
        })) || []
      };
    });
  });
  
  return {
    inspection_id: props.inspection.id,
    results
  };
};

const form = useForm(initializeForm());

// Setup debounced save
const debouncedSave = debounce((pointId) => {
  saveResult(pointId);
}, 1000);

// Save single result
const saveResult = async (pointId) => {
  try {
    const response = await router.post(route('inspections.save-result'), {
      inspection_id: props.inspection.id,
      point_id: pointId,
      status: form.results[pointId].status,
      note: form.results[pointId].note,
    }, {
      preserveScroll: true,
      preserveState: true,
    });

    console.log('Saved successfully:', response);
  } catch (error) {
    console.error('Error saving result:', error);
  }
};

// Save single result
// const saveResult = async (pointId) => {
//   try {
//     await router.post(route('inspections.save-result'), {
//       inspection_id: props.inspection.id,
//       point_id: pointId,
//       status: form.results[pointId].status,
//       note: form.results[pointId].note,
//     }, {
//       preserveScroll: true,
//       preserveState: true,
//       onError: (errors) => {
//         let errorMessage = 'Failed to save data.';
        
//         // Format error message lebih baik
//         if (errors.message) {
//           errorMessage = errors.message;
//         } else if (typeof errors === 'string') {
//           errorMessage = errors;
//         } else if (errors?.errors) {
//           // Jika error dari Laravel validation
//           errorMessage = Object.values(errors.errors).join('\n');
//         }
        
//         alert(errorMessage);
//       }
//     });

//   } catch (error) {
//     console.error('Error saving result:', error);
    
//     // Format error untuk alert
//     let errorMessage = 'An unexpected error occurred while saving.';
//     if (error.response?.data?.message) {
//       errorMessage = error.response.data.message;
//     } else if (error.message) {
//       errorMessage = error.message;
//     }
    
//     alert(errorMessage);
//   }
// };

// Handle image upload
const handleImageUpload = async (event, pointId) => {
  const file = event.target.files[0];
  if (!file) return;

  // Create preview
  const preview = URL.createObjectURL(file);
  
  try {
    form.transform((data) => {
      const formData = new FormData();
      formData.append('inspection_id', data.inspection_id);
      formData.append('point_id', pointId);
      formData.append('image', file);

      // Use Inertia's router for file uploads
      router.post(route('inspections.upload-image'), formData, {
        preserveScroll: true,
        onSuccess: (response) => {
          // Update images array after successful upload
          form.results[pointId].images.push({
            image_path: response.props.imagePath,
            preview: preview
          });
        },
      });

      return data;
    });
  } catch (error) {
    console.error('Error uploading image:', error);
  } finally {
    event.target.value = '';
  }
};

// Remove image
const removeImage = async (pointId, imageIndex) => {
  const image = form.results[pointId].images[imageIndex];
  
  try {
    await router.delete(route('inspections.delete-image'), {
      data: { 
        image_path: image.image_path,
        point_id: pointId,
        inspection_id: props.inspection.id
      },
      preserveScroll: true,
      onSuccess: () => {
        form.results[pointId].images.splice(imageIndex, 1);
      }
    });
  } catch (error) {
    console.error('Error deleting image:', error);
  }
};

// Final submit all
const submitAll = () => {
  form.post(route('inspections.final-submit'), {
    preserveScroll: true,
    onSuccess: () => {
      usePage().props.flash.success = 'Inspection submitted successfully!';
    },
    onError: () => {
      usePage().props.flash.error = 'There were errors in your submission.';
    }
  });
};
</script>

<style scoped>
/* Smooth transitions for interactive elements */
input, select, textarea, button, label {
  transition: all 0.15s ease-in-out;
}

/* Custom scrollbar for select elements */
select::-webkit-scrollbar {
  width: 8px;
}

select::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

select::-webkit-scrollbar-thumb {
  background: #c7d2fe;
  border-radius: 4px;
}

select::-webkit-scrollbar-thumb:hover {
  background: #a5b4fc;
}

/* Improved focus states */
input:focus, select:focus, textarea:focus {
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
  outline: none;
}
</style>