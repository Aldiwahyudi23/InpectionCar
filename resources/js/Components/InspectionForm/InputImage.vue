<template>
  <div>
    <input
      ref="galleryInput"
      type="file"
      accept="image/*"
      class="hidden"
      @change="handleImageSelect"
      :multiple="allowMultiple"
    />

    <canvas ref="processingCanvas" class="hidden"></canvas>

    <label
      v-if="modelValue.length === 0"
      @click="openSourceOptions"
      class="block w-full border-2 border-dashed rounded-lg cursor-pointer transition-colors duration-200 h-28"
      :class="{
        'border-gray-300 hover:border-indigo-400 bg-gray-50': true, // Selalu aktifkan style ini jika tidak ada gambar
      }"
    >
      <div class="h-full flex flex-col items-center justify-center p-4 text-center">
        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-sm text-gray-600 font-medium">Upload Image</p>
        <p class="text-xs text-gray-500">Click to open options</p>
      </div>
    </label>

    <div 
      v-else 
      class="block w-full border-2 border-dashed rounded-lg transition-colors duration-200 border-indigo-300 bg-indigo-50 h-auto p-2"
      aria-label="Image gallery"
    >
        <div class="grid grid-cols-3 gap-2">
          <div
            v-for="(image, idx) in modelValue"
            :key="image.id || idx"
            class="relative w-full overflow-hidden rounded-md border border-gray-200 cursor-pointer"
            :style="getImageContainerStyle(image)"
            @click="openPreviewModal(idx)"
          >
            <img
              :src="getImageSrc(image)"
              class="absolute top-0 left-0 w-full h-full object-contain"
            >
            <button
        @click.stop="removeImage(idx)"
        type="button"
        class="absolute top-1 right-1 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs shadow-sm hover:bg-red-600 transition-colors z-10"
        aria-label="Remove image"
      >
        ×
      </button>
          </div>
        </div>

        <div v-if="allowMultiple && modelValue.length < settings.max_files" class="mt-2 text-center">
          <span @click="openSourceOptions" class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition-colors cursor-pointer">
            + Add More Images
          </span>
        </div>
    </div>

    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>

    <ImageSourceOptionsModal
      :show="showSourceOptionsModal"
      @close="showSourceOptionsModal = false"
      @open-webcam="openWebcam"
      @trigger-gallery="triggerGallery"
    />

    <WebcamModal
      :show="showWebcamModal"
      :aspect-ratio="aspectRatio"
      :settings="settings"
      @close="closeWebcam"
      @photo-captured="handlePhotoCaptured"
    />

    <ImagePreviewModal
      :show="showPreviewModal"
      :images="previewImages"
      :initial-index="currentPreviewIndex"
      :allow-multiple="allowMultiple"
      :max-files="settings.max_files"
      :is-uploading="isUploading"
      @close="closePreviewModal"
      @save-images="triggerUploadAndSave"
      @remove-preview-image="handleRemovePreviewImage"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
// Sesuaikan path import modal Anda jika perlu
import ImageSourceOptionsModal from './image-uploader/ImageSourceOptionsModal.vue';
import WebcamModal from './image-uploader/WebcamModal.vue';
import ImagePreviewModal from './image-uploader/ImagePreviewModal.vue';
import axios from 'axios'; 
// Pastikan `route` tersedia secara global (dari Ziggy) jika Anda menggunakannya.
// Jika tidak, Anda harus membuat URL secara manual, misalnya: `/api/inspections/upload-image`

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  error: String,

  inspectionId: { // Tambahkan prop ini jika Anda menggunakannya di triggerUploadAndSave
    type: [String, Number],
    required: false // Opsional jika inspectionId tidak selalu diperlukan di sini
  },
  pointId: {
    type: [String, Number],
    required: true // Tandai sebagai required untuk membantu debugging
  }, 
  settings: {
    type: Object,
    default: () => ({
      max_files: 1,
      allowed_types: ['jpg', 'png'],
      camera_aspect_ratio: '3:4',
      enable_flash: true,
      enable_camera_switch: true,
    })
  }
});

const emit = defineEmits(['update:modelValue', 'save', 'removeImage', 'uploaded']);

const galleryInput = ref(null);
const processingCanvas = ref(null);

const showSourceOptionsModal = ref(false);
const showWebcamModal = ref(false);
const showPreviewModal = ref(false);

const previewImages = ref([]);
const currentPreviewIndex = ref(0);
const isUploading = ref(false);

const allowMultiple = computed(() => Number(props.settings.max_files) > 1);

const aspectRatio = computed(() => {
  const parts = props.settings.camera_aspect_ratio.split(':');
  if (parts.length === 2) {
    const width = parseFloat(parts[0]);
    const height = parseFloat(parts[1]);
    if (!isNaN(width) && !isNaN(height) && height !== 0) {
      return width / height;
    }
  }
  return 3 / 4;
});

const getImageSrc = (image) => {
  // Menggunakan image.preview jika ada (untuk gambar baru/preview dari blob URL)
  // Jika tidak, gunakan image.image_path (untuk gambar dari DB)
  // Pastikan image_path sudah relatif ke public, misal 'inspection_uploads/images/foto.jpg'
  // Atau jika Anda menggunakan URL lengkap dari backend, gunakan image.public_url jika tersedia.
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};

const getImageContainerStyle = (image) => {
  if (!image.width || !image.height || image.width === 0 || image.height === 0) {
    return {
      paddingBottom: '100%', // Default to square if dimensions are unknown
      position: 'relative'
    };
  }
  const aspectRatioPercentage = (image.height / image.width) * 100;
  return {
    paddingBottom: `${aspectRatioPercentage}%`,
    position: 'relative'
  };
};

// Fungsi ini tidak lagi digunakan oleh elemen utama, tetapi mungkin masih dipanggil dari tempat lain.
// Saya biarkan di sini dengan catatan.
const triggerAction = () => {
  console.warn("triggerAction was called. This function is no longer directly attached to the main label click for existing images.");
  // if (props.modelValue.length === 0) {
  //   openSourceOptions();
  // } else {
  //   openPreviewModal(0);
  // }
};

const openSourceOptions = () => {
  showSourceOptionsModal.value = true;
};

const triggerGallery = () => {
  showSourceOptionsModal.value = false;
  galleryInput.value.click();
};

const loadImageWithDimensions = (file) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                resolve({
                    file,
                    preview: URL.createObjectURL(file),
                    rotation: 0,
                    width: img.naturalWidth,
                    height: img.naturalHeight,
                    isNew: true // Tandai sebagai gambar baru
                });
            };
            img.onerror = () => {
                console.error("Failed to load image for dimensions:", file.name);
                resolve({ file, preview: URL.createObjectURL(file), rotation: 0, width: 0, height: 0, isNew: true });
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
};

const handleImageSelect = (event) => {
  const files = Array.from(event.target.files);
  if (!files.length) return;

  // Revoke previous blob URLs if not allowing multiple or replacing existing ones
  if (!allowMultiple.value) {
      previewImages.value.forEach(img => img.preview && img.preview.startsWith('blob:') && URL.revokeObjectURL(img.preview));
      previewImages.value = [];
  }

  Promise.all(files.map(loadImageWithDimensions)).then(newImages => {
      const currentTotalImages = props.modelValue.length + previewImages.value.length;
      const allowedToAdd = props.settings.max_files - currentTotalImages;
      const imagesToAdd = allowMultiple.value ? newImages.slice(0, allowedToAdd) : newImages.slice(0, 1);

      previewImages.value.push(...imagesToAdd);
      currentPreviewIndex.value = previewImages.value.length - 1;
      showPreviewModal.value = true;
      event.target.value = ''; // Reset input file to allow re-selection of the same file
  }).catch(error => {
      console.error("Error processing selected images:", error);
      alert("Failed to process selected images. Please try again.");
  });
};

const openWebcam = () => {
  showSourceOptionsModal.value = false;
  showWebcamModal.value = true;
};

const closeWebcam = () => {
  showWebcamModal.value = false;
};

const handlePhotoCaptured = (newImageFile) => {
  const currentTotalImages = props.modelValue.length + previewImages.value.length;
  if (!allowMultiple.value || currentTotalImages < props.settings.max_files) {
    const img = new Image();
    img.onload = () => {
      const newImage = {
          file: newImageFile,
          preview: URL.createObjectURL(newImageFile),
          rotation: 0,
          width: img.naturalWidth,
          height: img.naturalHeight,
          isNew: true // Tandai sebagai gambar baru
      };

      if (!allowMultiple.value) {
          previewImages.value.forEach(img => img.preview && img.preview.startsWith('blob:') && URL.revokeObjectURL(img.preview));
          previewImages.value = [newImage];
      } else {
          previewImages.value.push(newImage);
      }
      currentPreviewIndex.value = previewImages.value.length - 1;
      showWebcamModal.value = false;
      showPreviewModal.value = true;
    };
    img.onerror = () => {
      console.error("Failed to load captured image for dimensions.");
      const newImage = { file: newImageFile, preview: URL.createObjectURL(newImageFile), rotation: 0, width: 0, height: 0, isNew: true };
      if (!allowMultiple.value) {
          previewImages.value.forEach(img => img.preview && img.preview.startsWith('blob:') && URL.revokeObjectURL(img.preview));
          previewImages.value = [newImage];
      } else {
          previewImages.value.push(newImage);
      }
      currentPreviewIndex.value = previewImages.value.length - 1;
      showWebcamModal.value = false;
      showPreviewModal.value = true;
    };
    img.src = URL.createObjectURL(newImageFile);
  } else {
    alert(`Maximum ${props.settings.max_files} files allowed.`);
    showWebcamModal.value = false;
  }
};

const openPreviewModal = async (initialIdx = 0) => {
  // Kumpulkan gambar yang sudah ada (dari DB) dan gambar yang baru ditambahkan (dari previewImages)
  // Pastikan gambar dari DB juga memiliki preview URL yang bisa ditampilkan oleh modal
  const existingImagesProcessed = await Promise.all(props.modelValue.map(async (img) => {
      // Jika gambar dari DB sudah memiliki lebar/tinggi dan image_path, gunakan langsung
      if (img.width && img.height && img.image_path) {
          return { ...img, rotation: img.rotation || 0, isNew: false };
      } else {
          // Jika tidak, coba load gambar untuk mendapatkan dimensi dan URL preview
          const tempImg = new Image();
          return new Promise(resolve => {
            tempImg.onload = () => resolve({ 
                ...img, 
                rotation: img.rotation || 0, 
                preview: img.image_path ? `/${img.image_path}` : null, // Menggunakan path relatif dari public
                width: tempImg.naturalWidth, 
                height: tempImg.naturalHeight, 
                isNew: false 
            });
            tempImg.onerror = () => resolve({ 
                ...img, 
                rotation: img.rotation || 0, 
                preview: img.image_path ? `/${img.image_path}` : null, 
                width: 0, 
                height: 0, 
                isNew: false 
            });
            tempImg.src = img.image_path ? `/${img.image_path}` : ''; // Load dari path relatif
          });
      }
  }));

  // Gabungkan gambar yang sudah ada dengan gambar baru yang masih di preview (belum di-upload)
  // Filter gambar baru untuk memastikan hanya yang isNew atau yang dirotasi
  const currentTemporaryNewImages = previewImages.value.filter(img => img.isNew || img.rotation !== 0);
  previewImages.value = [...existingImagesProcessed, ...currentTemporaryNewImages];

  currentPreviewIndex.value = initialIdx; 
  showPreviewModal.value = true;
};

const closePreviewModal = () => {
  showPreviewModal.value = false;
  // Hapus blob URLs yang mungkin dibuat untuk preview gambar baru
  previewImages.value.forEach(img => {
      if (img.preview && img.preview.startsWith('blob:')) {
          URL.revokeObjectURL(img.preview);
      }
  });
  // Clear previewImages array setelah modal ditutup untuk mencegah duplikasi atau masalah state
  previewImages.value = [];
};

const applyRotationToImage = (imageObject) => {
    return new Promise((resolve) => {
        // Jika tidak ada rotasi atau canvas tidak tersedia, langsung resolve file asli
        if (imageObject.rotation === 0 || !processingCanvas.value) {
            resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
            return;
        }

        const img = new Image();
        img.onload = () => {
            const canvas = processingCanvas.value;
            const context = canvas.getContext('2d');

            const originalWidth = img.width;
            const originalHeight = img.height;

            let newCanvasWidth, newCanvasHeight;
            if (imageObject.rotation === 90 || imageObject.rotation === 270) {
                newCanvasWidth = originalHeight;
                newCanvasHeight = originalWidth;
            } else {
                newCanvasWidth = originalWidth;
                newCanvasHeight = originalHeight;
            }

            canvas.width = newCanvasWidth;
            canvas.height = newCanvasHeight;

            context.clearRect(0, 0, canvas.width, canvas.height);
            context.translate(canvas.width / 2, canvas.height / 2);
            context.rotate(imageObject.rotation * Math.PI / 180);
            context.drawImage(img, -originalWidth / 2, -originalHeight / 2, originalWidth, originalHeight);
            context.setTransform(1, 0, 0, 1, 0, 0);

            // Convert canvas content to Blob (File-like object)
            canvas.toBlob((blob) => {
                const newFile = new File([blob], imageObject.file.name, { type: imageObject.file.type });
                resolve({ file: newFile, width: newCanvasWidth, height: newCanvasHeight });
            }, imageObject.file.type);
        };
        img.onerror = () => {
            console.error("Failed to load image for rotation processing:", imageObject.preview);
            resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
        };
        img.src = imageObject.preview; // Load from blob URL for rotation
    });
};

const triggerUploadAndSave = async (imagesToSave) => {
    isUploading.value = true;
    const uploadedImagesData = [];

    if (!props.pointId) {
        alert('Error: Point ID is missing. Cannot upload image. (Check parent component)');
        isUploading.value = false;
        return;
    }
    console.log('Uploading for Inspection ID:', props.inspectionId, 'Point ID:', props.pointId);

    try {
        for (const img of imagesToSave) {
            // Hanya proses dan upload gambar baru atau yang dirotasi
            if (img.isNew || img.rotation !== 0) {
                const { file: processedFile, width: newWidth, height: newHeight } = await applyRotationToImage(img);

                const formData = new FormData();
                formData.append('inspection_id', props.inspectionId); 
                formData.append('point_id', props.pointId); 
                formData.append('image', processedFile);

                const response = await axios.post(route('inspections.upload-image'), formData, { // Pastikan route ini sesuai
                    headers: {
                        'Content-Type': 'multipart/form-data' 
                    }
                });

                const serverImage = response.data.image; 
                // Asumsi serverImage.image_path adalah path yang disimpan di DB (relatif ke public_path)
                const imagePath = response.data.path; // Ini adalah path relatif dari public (misal 'inspection_uploads/images/nama.jpg')
                const publicUrl = response.data.public_url; // URL lengkap dari backend (misal 'http://localhost:8000/inspection_uploads/images/nama.jpg')


                uploadedImagesData.push({
                    id: serverImage.id, // ID gambar dari DB
                    image_path: imagePath, // Path relatif yang disimpan di DB
                    width: newWidth, 
                    height: newHeight,
                    rotation: 0, // Reset rotasi setelah diupload dan diproses
                    preview: publicUrl // Gunakan URL lengkap dari backend untuk preview
                });

                // Revoke Blob URL setelah berhasil diupload
                if (img.preview && img.preview.startsWith('blob:')) {
                    URL.revokeObjectURL(img.preview);
                }

            } else {
                // Jika bukan gambar baru atau tidak dirotasi, tambahkan langsung ke daftar uploadedImagesData
                // Pastikan preview URL-nya sudah benar
                uploadedImagesData.push({
                    ...img,
                    preview: img.preview || `/${img.image_path}` // Pastikan preview URL valid
                });
            }
        }

        // Update modelValue di komponen induk
        // Pastikan Anda membatasi jumlah file sesuai settings.max_files
        emit('update:modelValue', uploadedImagesData.slice(0, props.settings.max_files));
        emit('save', props.pointId); // Emit event save ke InspectionCategory/Edit.vue
        emit('uploaded', { pointId: props.pointId, images: uploadedImagesData }); // Emit event uploaded dengan data gambar yang baru

        closePreviewModal(); // Tutup modal preview
        // alert('All images processed and saved successfully!');

    } catch (error) {
        console.error("Error during image processing/upload:", error);
        if (error.response && error.response.data && error.response.data.message) {
            alert(`Failed to save images: ${error.response.data.message}`);
        } else if (error.response && error.response.data && error.response.data.errors) {
            // Tangani error validasi dari Laravel
            const errors = Object.values(error.response.data.errors).flat().join('\n');
            alert(`Failed to save images:\n${errors}`);
        }
        else {
            alert(`Failed to save images: ${error.message}`);
        }
    } finally {
        isUploading.value = false;
    }
};

const handleRemovePreviewImage = (indexToRemove) => {
  // Hanya menghapus dari daftar preview, bukan dari server
  // Penghapusan dari server terjadi di `removeImage` saat tombol 'x' diklik di thumbnail utama.
  previewImages.value.splice(indexToRemove, 1);
};


const removeImage = async (index) => {
    const updated = [...props.modelValue];
    const removed = updated.splice(index, 1)[0];

    // Jika gambar sudah memiliki ID atau image_path (berarti sudah ada di server/DB), coba hapus dari server
    if (removed.id || removed.image_path) {
        try {
            // Anda perlu route delete yang menerima image_path atau image_id
            // Contoh route: `Route::delete('/images/delete', [ImageController::class, 'destroy'])->name('inspections.delete-image');`
            await axios.delete(route('inspections.delete-image'), {
                data: { 
                    image_path: removed.image_path, // Kirim path untuk dihapus
                    image_id: removed.id // Atau kirim ID jika Anda menggunakannya di backend
                }
            });
            alert('Image deleted from server successfully!');
        } catch (error) {
            console.error("Error deleting image from server:", error);
            if (error.response && error.response.data && error.response.data.message) {
                alert(`Failed to delete image from server: ${error.response.data.message}`);
            } else if (error.response && error.response.data && error.response.data.errors) {
                const errors = Object.values(error.response.data.errors).flat().join('\n');
                alert(`Failed to delete image:\n${errors}`);
            }
            else {
                alert(`Failed to delete image from server: ${error.message}`);
            }
            // Penting: Jika gagal hapus dari server, mungkin Anda tidak ingin menghapus dari frontend
            // throw error; // Re-throw error jika ingin menghentikan penghapusan di frontend
        }
    }

    // Revoke Blob URL jika ada
    if (removed.preview && removed.preview.startsWith('blob:')) {
        URL.revokeObjectURL(removed.preview);
    }
    
    // Update modelValue untuk menghapus gambar dari tampilan dan state komponen induk
    emit('update:modelValue', updated);
    // Emit event removeImage ke komponen induk (jika InspectionCategory perlu tahu)
    emit('removeImage', { index, pointId: props.pointId, image: removed });

    // Setelah menghapus gambar, jika tidak ada gambar tersisa, pastikan modal preview ditutup
    if (updated.length === 0) {
      closePreviewModal();
    }
};
</script>