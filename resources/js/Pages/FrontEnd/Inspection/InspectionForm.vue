<template>
  <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Vehicle Inspection</h1>
      <h2 class="text-xl font-semibold text-indigo-600">
        {{ inspection.car?.brand_id || 'Unknown Vehicle' }}
      </h2>
    </div>

    <div class="sticky top-0 z-10 bg-white shadow-sm mb-6">
      <div class="flex overflow-x-auto scrollbar-hide py-3 px-4 space-x-2">
        <button
          v-for="category in categories"
          :key="category.id"
          @click="changeCategory(category.id)"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
          :class="{
            'bg-indigo-600 text-white': activeCategory === category.id,
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== category.id
          }"
        >
          {{ category.name }}
          <span 
            v-if="isCategoryComplete(category)"
            class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white"
          >
            ✓
          </span>
        </button>
      </div>
    </div>

    <div class="relative overflow-hidden">
      <transition name="category-slide" mode="out-in">
        <category-section
          v-if="activeCategoryData"
          :key="activeCategoryData.id"
          :category="activeCategoryData"
          :form="form"
          @saveResult="saveResult"
          @updateResult="updateResult"
          @removeImage="removeImage"
          @handleImageUpload="handleImageUpload"
        />
      </transition>
      
      <button
        v-if="activeIndex > 0"
        @click="navigate(-1)"
        class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white rounded-full shadow-md p-2 text-indigo-600 hover:bg-indigo-50"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      
      <button
        v-if="activeIndex < categories.length - 1"
        @click="navigate(1)"
        class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white rounded-full shadow-md p-2 text-indigo-600 hover:bg-indigo-50"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
    
    <div class="flex justify-end gap-4 mt-8">
      <button
        type="button"
        @click="submitAll"
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import CategorySection from '@/Components/InspectionForm/CategorySection.vue';

const props = defineProps({
  inspection: Object,
  categories: Array,
  existingResults: Array,
  existingImages: Object,
});

const activeCategory = ref(props.categories[0]?.id || null);
const activeIndex = ref(0);
const categoriesWrapper = ref(null); // Keep this for swipe gestures if still desired

// Computed property to get the data for the active category
const activeCategoryData = computed(() => {
  return props.categories.find(c => c.id === activeCategory.value);
});

// Initialize form
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
          preview: null // Preview will be generated when a new image is selected
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

// Check if category is complete
const isCategoryComplete = (category) => {
  return category.points.every(point => {
    const result = form.results[point.id];
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
        // For other input types, you might consider them complete if a status or note exists
        return !!result.status || !!result.note;
    }
  });
};

// Change active category
const changeCategory = (categoryId) => {
  const index = props.categories.findIndex(c => c.id === categoryId);
  if (index >= 0) {
    activeIndex.value = index;
    activeCategory.value = categoryId;
  }
};

// Navigate between categories
const navigate = (direction) => {
  const newIndex = activeIndex.value + direction;
  if (newIndex >= 0 && newIndex < props.categories.length) {
    activeIndex.value = newIndex;
    activeCategory.value = props.categories[newIndex].id;
  }
};

// Handle swipe gestures
// NOTE: Swipe gestures might be tricky with `v-if` because the element you're swiping on
// will unmount and remount. The current implementation relies on `categoriesWrapper`
// which would be gone after a category switch. If you want swipe, you'd need to attach
// the listeners to a persistent container that *holds* the conditionally rendered content.
// For simplicity and direct answer to the "stacking" problem, I'm keeping the original
// swipe setup but be aware it might not work as expected with v-if unless refactored.
const setupSwipe = () => {
  let touchStartX = 0;
  let touchEndX = 0;
  
  const handleTouchStart = (e) => {
    touchStartX = e.changedTouches[0].screenX;
  };
  
  const handleTouchEnd = (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  };
  
  const handleSwipe = () => {
    if (touchEndX < touchStartX - 50) {
      // Swipe left - go next
      navigate(1);
    } else if (touchEndX > touchStartX + 50) {
      // Swipe right - go previous
      navigate(-1);
    }
  };
  
  // Attach listeners to the main container or a persistent wrapper if swipe is desired
  // This will work if the entire section where CategorySection is rendered is the touch target
  const mainContentArea = document.querySelector('.relative.overflow-hidden'); 
  if (mainContentArea) {
    mainContentArea.addEventListener('touchstart', handleTouchStart, false);
    mainContentArea.addEventListener('touchend', handleTouchEnd, false);
  }
  
  return () => {
    if (mainContentArea) {
      mainContentArea.removeEventListener('touchstart', handleTouchStart);
      mainContentArea.removeEventListener('touchend', handleTouchEnd);
    }
  };
};

// Save single result (debounced to avoid too many requests)
const saveResult = debounce(async (pointId) => {
  try {
    await router.post(route('inspections.save-result'), {
      inspection_id: props.inspection.id,
      point_id: pointId,
      status: form.results[pointId].status,
      note: form.results[pointId].note,
    }, {
      preserveScroll: true,
      preserveState: true,
      only: ['existingResults'], // Only refresh existingResults prop
      onSuccess: () => {
        // You might want to re-initialize form data if existingResults are updated
        // or ensure form.results is reactive to prop changes.
        // For simplicity, we assume form.results directly updates based on user input.
      },
    });
  } catch (error) {
    console.error('Error saving result:', error);
  }
}, 500); // Debounce by 500ms

// Update result data (this should trigger `saveResult` via a watch or direct call)
const updateResult = ({ pointId, type, value }) => {
  if (type === 'note') {
    form.results[pointId].note = value;
  } else if (type === 'status') {
    form.results[pointId].status = value;
  }
  // Call saveResult after updating the form data
  saveResult(pointId);
};

// Handle image upload
const handleImageUpload = async (event, pointId) => {
  const file = event.target.files[0];
  if (!file) return;

  const preview = URL.createObjectURL(file);
  
  try {
    const formData = new FormData();
    formData.append('inspection_id', props.inspection.id);
    formData.append('point_id', pointId);
    formData.append('image', file);

    // Assuming your backend returns the path of the uploaded image
    await router.post(route('inspections.upload-image'), formData, {
      preserveScroll: true,
      onSuccess: (page) => {
        // Ensure that page.props.imagePath exists and is correct
        const uploadedImagePath = page.props.flash?.imagePath || page.props.imagePath;
        if (uploadedImagePath) {
          form.results[pointId].images.push({
            image_path: uploadedImagePath,
            preview: preview
          });
        } else {
          console.error("Image path not returned from upload, cannot update UI.");
        }
      },
      onError: (errors) => {
        console.error('Error uploading image:', errors);
        alert('Failed to upload image. Please try again.');
      }
    });
  } catch (error) {
    console.error('Error uploading image:', error);
    alert('An unexpected error occurred during image upload.');
  } finally {
    event.target.value = ''; // Clear the input field
  }
};

// Remove image
const removeImage = async (pointId, imageIndex) => {
  const image = form.results[pointId].images[imageIndex];
  if (!image) return;

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
        if (image.preview) {
          URL.revokeObjectURL(image.preview);
        }
      },
      onError: (errors) => {
        console.error('Error deleting image:', errors);
        alert('Failed to delete image. Please try again.');
      }
    });
  } catch (error) {
    console.error('Error deleting image:', error);
    alert('An unexpected error occurred during image deletion.');
  }
};

// Final submit all
const submitAll = () => {
  form.post(route('inspections.final-submit'), {
    preserveScroll: true,
    onSuccess: () => {
      usePage().props.flash.success = 'Inspection submitted successfully!';
      // Optionally redirect or show a success message
    },
    onError: (errors) => {
      console.error('Submission errors:', errors);
      usePage().props.flash.error = 'There were errors in your submission. Please check all fields.';
      // You might want to scroll to the first error or highlight problematic categories
    }
  });
};

onMounted(() => {
  setupSwipe();
});

// Watch for changes in activeCategory to potentially scroll the horizontal nav
watch(activeCategory, (newVal) => {
  const categoryButton = document.querySelector(`.flex-shrink-0[data-category-id="${newVal}"]`);
  if (categoryButton) {
    // Scroll the button into view if it's not fully visible
    categoryButton.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
  }
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Transitions for conditional rendering (v-if) */
.category-slide-enter-active,
.category-slide-leave-active {
  transition: all 0.3s ease-out;
  position: absolute; /* Needed for slide transition with v-if */
  width: 100%; /* Ensure it takes full width when absolute */
  top: 0;
  left: 0;
}
.category-slide-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.category-slide-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}
</style>