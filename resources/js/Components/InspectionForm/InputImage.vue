<template>
  <div>
    <input
      type="file"
      @change="handleImageUpload"
      accept="image/*"
      class="hidden"
      :id="'image-upload-'+pointId"
      multiple
    />
    
    <label 
      :for="'image-upload-'+pointId"
      class="block w-full border-2 border-dashed rounded-lg cursor-pointer transition-colors duration-200"
      :class="{
        'border-gray-300 hover:border-indigo-400 bg-gray-50': modelValue.length === 0,
        'border-indigo-300 bg-indigo-50': modelValue.length > 0,
        'h-28': modelValue.length === 0,
        'h-auto': modelValue.length > 0
      }"
    >
      <template v-if="modelValue.length === 0">
        <div class="h-full flex flex-col items-center justify-center p-4 text-center">
          <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p class="text-sm text-gray-600 font-medium">Upload Image</p>
          <p class="text-xs text-gray-500">Click to browse or drag and drop</p>
        </div>
      </template>
      
      <template v-else>
        <div class="p-2">
          <div class="grid grid-cols-3 gap-2">
            <div 
              v-for="(image, idx) in modelValue" 
              :key="idx" 
              class="relative aspect-square"
            >
              <img 
                :src="image.preview || '/storage/'+image.image_path" 
                class="w-full h-full object-cover rounded-md border border-gray-200"
              >
              <button 
                @click.stop="removeImage(idx)"
                type="button"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs shadow-sm"
              >
                ×
              </button>
            </div>
          </div>
          <div class="mt-2 text-center">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
              + Add More Images
            </span>
          </div>
        </div>
      </template>
    </label>
    
    <p v-if="error" class="mt-1 text-xs text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  error: String,
  pointId: [String, Number]
});

const emit = defineEmits(['update:modelValue', 'save', 'removeImage']);

const handleImageUpload = (event) => {
  const files = Array.from(event.target.files);
  if (!files.length) return;

  const newImages = files.map(file => ({
    file,
    preview: URL.createObjectURL(file)
  }));

  emit('update:modelValue', [...props.modelValue, ...newImages]);
  emit('save', props.pointId);
  event.target.value = '';
};

const removeImage = (index) => {
  const updatedImages = [...props.modelValue];
  const removedImage = updatedImages.splice(index, 1)[0];
  
  if (removedImage.preview) {
    URL.revokeObjectURL(removedImage.preview);
  }
  
  emit('update:modelValue', updatedImages);
  emit('removeImage', { index, pointId: props.pointId });
};
</script>