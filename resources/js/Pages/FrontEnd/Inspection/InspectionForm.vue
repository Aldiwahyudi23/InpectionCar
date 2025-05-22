<template>
  <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Pemeriksaan Kendaraan</h1>
      <h2 class="text-xl font-semibold text-indigo-600">
        {{ inspection.car?.brand_id || 'Kendaraan Tidak Dikenal' }}
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
          :data-category-id="category.id"
        >
          {{ category.name }}
          <span 
            v-if="isCategoryComplete(category)"
            class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white"
          >
            ✓
          </span>
        </button>

        <button
          @click="changeCategory('summary')"
          class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
          :class="{
            'bg-indigo-600 text-white': activeCategory === 'summary',
            'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== 'summary'
          }"
          data-category-id="summary"
        >
          Kesimpulan
        </button>
      </div>
    </div>

    <div class="relative overflow-hidden">
      <transition name="category-slide" mode="out-in">
        <category-section
          v-if="activeCategoryData && activeCategory !== 'summary'"
          :key="activeCategoryData.id"
          :category="activeCategoryData"
          :form="form"
          @saveResult="saveResult"
          @updateResult="updateResult"
          @removeImage="removeImage"
          @handleImageUpload="handleImageUpload"
        />

        <div 
          v-else-if="activeCategory === 'summary'" 
          key="summary-section"
          class="bg-white rounded-xl shadow-md p-6"
        >
          <h3 class="text-2xl font-semibold text-gray-800 mb-4">Kesimpulan Inspeksi</h3>
          
          <div class="mb-4">
            <label for="overall_note" class="block text-sm font-medium text-gray-700 mb-1">Catatan Keseluruhan Inspeksi:</label>
            <textarea
              id="overall_note"
              v-model="form.overall_note"
              @input="debounceSaveOverallNote"
              rows="5"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Tambahkan catatan penting atau ringkasan inspeksi di sini..."
            ></textarea>
          </div>

          <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-700 mb-3">Status Kategori:</h4>
            <ul class="list-disc pl-5">
              <li 
                v-for="category in categories" 
                :key="category.id" 
                :class="{'text-green-600': isCategoryComplete(category), 'text-red-600': !isCategoryComplete(category)}"
              >
                {{ category.name }} 
                <span v-if="isCategoryComplete(category)">(Selesai ✓)</span>
                <span v-else>(Belum Selesai ✗)</span>
              </li>
            </ul>
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
              <span>{{ form.processing ? 'Mengirim...' : 'Final Kirim Inspeksi' }}</span>
            </button>
          </div>
        </div>
      </transition>
      
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

// Ubah inisialisasi activeCategory untuk bisa menerima 'summary'
const activeCategory = ref(props.categories[0]?.id || 'summary'); // Default ke kategori pertama atau 'summary'
const activeIndex = ref(0); // Ini masih digunakan untuk swipe, tapi tidak lagi untuk translateX
const categoriesWrapper = ref(null); 

// Properti terhitung untuk mendapatkan data kategori aktif
const activeCategoryData = computed(() => {
  // Hanya mencari di `props.categories` jika activeCategory bukan 'summary'
  return activeCategory.value !== 'summary' 
    ? props.categories.find(c => c.id === activeCategory.value)
    : null; // Jika 'summary', tidak ada data kategori spesifik
});

// Inisialisasi form
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
          preview: null // Preview akan dibuat saat gambar baru dipilih
        })) || []
      };
    });
  });
  
  return {
    inspection_id: props.inspection.id,
    results,
    // Tambahkan field baru untuk catatan keseluruhan
    overall_note: props.inspection.overall_note || '' 
  };
};

const form = useForm(initializeForm());

// Debounce untuk menyimpan catatan keseluruhan
const debounceSaveOverallNote = debounce(() => {
  router.post(route('inspections.save-overall-note'), {
    inspection_id: props.inspection.id,
    overall_note: form.overall_note,
  }, {
    preserveScroll: true,
    preserveState: true,
    only: ['inspection'], // Hanya refresh prop inspection jika perlu
    onSuccess: () => {
      // console.log('Catatan keseluruhan berhasil disimpan.');
    },
    onError: (errors) => {
      console.error('Error menyimpan catatan keseluruhan:', errors);
    }
  });
}, 800); // Sesuaikan debounce time

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
        return !!result.status || !!result.note;
    }
  });
};

// Change active category
const changeCategory = (categoryId) => {
  // Jika categoryId adalah 'summary', set activeIndex ke posisi terakhir
  if (categoryId === 'summary') {
    activeIndex.value = props.categories.length; // Index terakhir + 1 untuk kesimpulan
  } else {
    const index = props.categories.findIndex(c => c.id === categoryId);
    if (index >= 0) {
      activeIndex.value = index;
    }
  }
  activeCategory.value = categoryId;
};

// Navigate between categories (Tidak lagi digunakan untuk panah, tapi tetap untuk swipe)
const navigate = (direction) => {
  let newIndex = activeIndex.value + direction;
  
  // Batasi index agar tidak keluar dari batas kategori + kesimpulan
  const maxIndex = props.categories.length; // categories.length adalah index untuk 'summary'
  
  if (newIndex >= 0 && newIndex <= maxIndex) {
    activeIndex.value = newIndex;
    if (newIndex === maxIndex) {
      activeCategory.value = 'summary';
    } else {
      activeCategory.value = props.categories[newIndex].id;
    }
  }
};

// Handle swipe gestures
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
      only: ['existingResults'], 
      onSuccess: () => {},
    });
  } catch (error) {
    console.error('Error menyimpan hasil:', error);
  }
}, 500); 

// Update result data (this should trigger `saveResult` via a watch or direct call)
const updateResult = ({ pointId, type, value }) => {
  if (form.results[pointId].hasOwnProperty(type)) { // Check if property exists
    form.results[pointId][type] = value;
  }
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

    await router.post(route('inspections.upload-image'), formData, {
      preserveScroll: true,
      onSuccess: (page) => {
        const uploadedImagePath = page.props.flash?.imagePath || page.props.imagePath;
        if (uploadedImagePath) {
          form.results[pointId].images.push({
            image_path: uploadedImagePath,
            preview: preview
          });
        } else {
          console.error("Path gambar tidak dikembalikan dari unggahan, tidak dapat memperbarui UI.");
        }
      },
      onError: (errors) => {
        console.error('Error mengunggah gambar:', errors);
        alert('Gagal mengunggah gambar. Silakan coba lagi.');
      }
    });
  } catch (error) {
    console.error('Error mengunggah gambar:', error);
    alert('Terjadi kesalahan tak terduga saat mengunggah gambar.');
  } finally {
    event.target.value = ''; 
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
        console.error('Error menghapus gambar:', errors);
        alert('Gagal menghapus gambar. Silakan coba lagi.');
      }
    });
  } catch (error) {
    console.error('Error menghapus gambar:', error);
    alert('Terjadi kesalahan tak terduga saat menghapus gambar.');
  }
};

// Final submit all
const submitAll = () => {
  // Pastikan overall_note juga dikirim saat submit
  form.post(route('inspections.final-submit'), {
    preserveScroll: true,
    onSuccess: () => {
      usePage().props.flash.success = 'Inspeksi berhasil dikirim!';
      // Redirect atau tindakan lain setelah sukses
    },
    onError: (errors) => {
      console.error('Kesalahan pengiriman:', errors);
      usePage().props.flash.error = 'Ada kesalahan dalam pengiriman Anda. Harap periksa semua bidang.';
    }
  });
};

onMounted(() => {
  setupSwipe();
});

// Watcher untuk perubahan activeCategory untuk menggulir navigasi horizontal
watch(activeCategory, (newVal) => {
  const categoryButton = document.querySelector(`.flex-shrink-0[data-category-id="${newVal}"]`);
  if (categoryButton) {
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

/* Transisi untuk conditional rendering (v-if) */
.category-slide-enter-active,
.category-slide-leave-active {
  transition: all 0.3s ease-out;
  position: absolute; 
  width: 100%; 
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