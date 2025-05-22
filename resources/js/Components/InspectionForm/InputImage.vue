<template>
  <div>
    <input
      ref="galleryInput"
      type="file"
      accept="image/*"
      class="hidden"
      @change="handleImageSelect"
      :multiple="allowMultiple"
      :disabled="isUploading"
    />

    <canvas ref="processingCanvas" class="hidden"></canvas>

    <label
      v-if="allImages.length === 0"
      @click="openSourceOptions"
      class="block w-full border-2 border-dashed rounded-lg cursor-pointer transition-colors duration-200 h-28"
      :class="{
        'border-gray-300 hover:border-indigo-400 bg-gray-50': true,
      }"
      :disabled="isUploading"
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
          v-for="(image, idx) in allImages"
          :key="image.id || image.preview" class="relative w-full overflow-hidden rounded-md border border-gray-200 cursor-pointer"
          :style="getImageContainerStyle(image)"
          @click="openPreviewModal(idx)"
        >
          <img
            :src="getImageSrc(image)"
            class="absolute top-0 left-0 w-full h-full object-cover"
          >
          <div v-if="image.isNew || image.rotation !== 0"
               class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center text-white text-xs font-bold">
              <span v-if="image.isNew">BARU</span>
              <span v-if="image.rotation !== 0" class="ml-1">ROTASI</span>
          </div>

          <button
            @click.stop="removeImage(image)" type="button"
            class="absolute top-1 right-1 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs shadow-sm hover:bg-red-600 transition-colors z-10"
            aria-label="Remove image"
            :disabled="isUploading"
          >
            ×
          </button>
        </div>

        <div
          v-if="allowMultiple && allImages.length < settings.max_files"
          class="flex flex-col items-center justify-center p-2 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition-colors"
          @click="openSourceOptions"
          :disabled="isUploading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <p class="mt-1 text-xs text-gray-600">Tambah</p>
        </div>
      </div>
    </div>

    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>

    <ImageSourceOptionsModal
      :show="showSourceOptionsModal"
      @close="closeSourceOptions" @open-webcam="openWebcam"
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
      :images="allImages" :initial-index="currentPreviewIndex"
      :allow-multiple="allowMultiple"
      :max-files="settings.max_files"
      :is-uploading="isUploading"
      @close="closePreviewModal"
      @save-images="triggerUploadAndSave"
      @remove-preview-image="handleRemovePreviewImage"
      @trigger-add-more-photos="openSourceOptions"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ImageSourceOptionsModal from './image-uploader/ImageSourceOptionsModal.vue';
import WebcamModal from './image-uploader/WebcamModal.vue';
import ImagePreviewModal from './image-uploader/ImagePreviewModal.vue';
import axios from 'axios';
// Asumsi 'route' tersedia secara global (dari Ziggy.js untuk Laravel)
// import { route } from 'ziggy-js'; 

const props = defineProps({
  modelValue: { type: Array, default: () => [] }, // Gambar yang sudah ada/disimpan
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

const previewImages = ref([]); // Menampung gambar baru atau yang dirotasi sebelum disimpan
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

// Computed property untuk menggabungkan gambar yang sudah ada (modelValue) dan gambar baru (previewImages)
// INI PENTING UNTUK MEMASTIKAN TAMPILAN GALERI DAN PREVIEW SELALU TERKINI
const allImages = computed(() => {
  const finalImages = [];
  const processedIds = new Set(); // Untuk melacak ID gambar yang sudah ditambahkan

  // 1. Tambahkan gambar baru/dirotasi dari previewImages.value
  // Ini akan diprioritaskan karena mungkin ada perubahan rotasi atau itu adalah gambar yang baru di-select/capture.
  for (const pImg of previewImages.value) {
    if (pImg.id) { // Jika ada ID, tandai sudah diproses
      processedIds.add(pImg.id);
    }
    finalImages.push(pImg);
  }

  // 2. Tambahkan gambar dari modelValue yang belum ada di finalImages (berdasarkan ID)
  for (const mImg of props.modelValue) {
    if (!processedIds.has(mImg.id)) {
      finalImages.push({
        ...mImg,
        rotation: mImg.rotation || 0, // Pastikan rotasi default 0
        isNew: false, // Tandai bukan gambar baru
        preview: mImg.preview || (mImg.image_path ? `/${mImg.image_path}` : null) // Pastikan URL preview ada
      });
    }
  }

  return finalImages;
});


const getImageSrc = (image) => {
  // Menggunakan image.preview (blob URL atau URL lengkap) jika tersedia
  // Jika tidak, gunakan image.image_path (relatif dari public)
  return image.preview || (image.image_path ? `/${image.image_path}` : '');
};

const getImageContainerStyle = (image) => {
  // Default ke rasio 1:1 jika dimensi tidak diketahui
  if (!image.width || !image.height || image.width === 0 || image.height === 0) {
    return {
      paddingBottom: '100%',
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
};

const openSourceOptions = () => {
  // Sembunyikan preview modal terlebih dahulu untuk mencegah tumpang tindih
  if (showPreviewModal.value) {
    showPreviewModal.value = false;
  }
  showSourceOptionsModal.value = true;
};

const triggerGallery = () => {
  showSourceOptionsModal.value = false; // Tutup modal pilihan sumber
  galleryInput.value.click(); // Trigger input file
};

const openWebcam = () => {
  showSourceOptionsModal.value = false; // Tutup modal pilihan sumber
  showWebcamModal.value = true; // Buka modal webcam
};

// Fungsi ini dipanggil saat ImageSourceOptionsModal ditutup (X atau klik di luar)
const closeSourceOptions = () => {
  showSourceOptionsModal.value = false;
  // Jika ada gambar di `allImages` (setelah penambahan atau jika sudah ada sebelumnya),
  // kita ingin kembali ke preview modal.
  if (allImages.value.length > 0) {
    showPreviewModal.value = true;
  }
};

// Fungsi ini dipanggil saat WebcamModal ditutup (X atau tombol batal)
const closeWebcam = () => {
  showWebcamModal.value = false;
  // Jika ada gambar di `allImages`, tampilkan kembali preview modal
  if (allImages.value.length > 0) {
    showPreviewModal.value = true;
  }
};

const closePreviewModal = () => {
  showPreviewModal.value = false;
  // Saat menutup preview modal, hapus semua blob URLs yang belum disimpan
  // dan kosongkan `previewImages` karena `modelValue` sekarang menjadi sumber kebenaran
  // untuk gambar yang sudah disimpan.
  previewImages.value.forEach(img => {
    if (img.preview && img.preview.startsWith('blob:')) {
      URL.revokeObjectURL(img.preview);
    }
  });
  previewImages.value = []; // Reset array gambar yang belum disimpan
};


// ===========================================
// FUNGSI PENANGANAN GAMBAR
// ===========================================

const loadImageWithDimensions = (file) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                resolve({
                    file,
                    preview: URL.createObjectURL(file), // Blob URL untuk preview
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
  if (!files.length) {
      showSourceOptionsModal.value = false; // Tutup modal pilihan sumber
      if (allImages.value.length > 0) { // Jika ada gambar, tampilkan kembali preview
          showPreviewModal.value = true;
      }
      return;
  }

  Promise.all(files.map(loadImageWithDimensions)).then(newImages => {
      const currentTotalImages = allImages.value.length; // Hitung dari allImages
      const allowedToAdd = props.settings.max_files - currentTotalImages;
      
      const imagesToProcess = allowMultiple.value ? newImages.slice(0, allowedToAdd) : newImages.slice(0, 1);

      if (!allowMultiple.value) {
          // Jika hanya satu file diizinkan, hapus semua gambar lama dan yang belum disimpan
          // Revoke Blob URLs
          previewImages.value.forEach(img => img.preview && img.preview.startsWith('blob:') && URL.revokeObjectURL(img.preview));
          previewImages.value = imagesToProcess;
          // Penting: Jika single, modelValue harus di-clear saat ini jika ingin langsung diganti
          // emit('update:modelValue', []); // Ini akan menghapus dari thumbnail utama secara instan
      } else {
          previewImages.value.push(...imagesToProcess);
      }
      
      showSourceOptionsModal.value = false; // Tutup modal pilihan sumber

      // Tampilkan kembali preview modal dan pindah ke gambar pertama yang baru ditambahkan
      if (imagesToProcess.length > 0) {
        currentPreviewIndex.value = allImages.value.length - imagesToProcess.length; // Index dari gambar pertama yang baru ditambahkan
        if (currentPreviewIndex.value < 0) currentPreviewIndex.value = 0; // Fallback
        showPreviewModal.value = true;
      } else {
          // Jika tidak ada gambar yang ditambahkan (misal sudah maxFiles), kembali ke preview jika sudah ada gambar
          if (allImages.value.length > 0) {
              showPreviewModal.value = true;
          }
      }
      event.target.value = ''; // Reset input file agar bisa memilih file yang sama lagi
  }).catch(error => {
      console.error("Error processing selected images:", error);
      alert("Failed to process selected images. Please try again.");
      showSourceOptionsModal.value = false; // Pastikan modal tertutup jika ada error
      if (allImages.value.length > 0) { // Jika ada gambar, tampilkan kembali preview
          showPreviewModal.value = true;
      }
  });
};

const handlePhotoCaptured = (newImageFile) => {
  const currentTotalImages = allImages.value.length; // Hitung dari allImages
  if (!allowMultiple.value || currentTotalImages < props.settings.max_files) {
    const img = new Image();
    img.onload = () => {
      const newImage = {
          file: newImageFile,
          preview: URL.createObjectURL(newImageFile), // Blob URL untuk preview
          rotation: 0,
          width: img.naturalWidth,
          height: img.naturalHeight,
          isNew: true // Tandai sebagai gambar baru
      };

      if (!allowMultiple.value) {
          // Jika hanya satu file diizinkan, hapus semua gambar lama dan yang belum disimpan
          previewImages.value.forEach(img => img.preview && img.preview.startsWith('blob:') && URL.revokeObjectURL(img.preview));
          previewImages.value = [newImage];
          // emit('update:modelValue', []); // Juga clear modelValue secara instan
      } else {
          previewImages.value.push(newImage);
      }
      
      showWebcamModal.value = false;       // Tutup modal webcam
      showSourceOptionsModal.value = false; // Tutup modal pilihan sumber (jika dibuka dari sana)

      // Tampilkan kembali preview modal dan pindah ke gambar yang baru ditambahkan
      currentPreviewIndex.value = allImages.value.length - 1; // Pindah ke gambar terakhir
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
      currentPreviewIndex.value = allImages.value.length - 1;
      showWebcamModal.value = false;
      showSourceOptionsModal.value = false;
      showPreviewModal.value = true;
    };
    img.src = URL.createObjectURL(newImageFile);
  } else {
    alert(`Maksimum ${props.settings.max_files} file diizinkan.`);
    showWebcamModal.value = false;
    showSourceOptionsModal.value = false;
    if (allImages.value.length > 0) { // Jika ada gambar, tampilkan kembali preview
        showPreviewModal.value = true;
    }
  }
};

const openPreviewModal = (initialIdx = 0) => {
  // allImages computed property akan otomatis memperbarui dirinya
  currentPreviewIndex.value = initialIdx;
  showPreviewModal.value = true;
};

const applyRotationToImage = (imageObject) => {
    return new Promise((resolve) => {
        // Jika tidak ada rotasi atau canvas tidak tersedia, langsung resolve file asli (atau gunakan preview jika itu sudah final)
        if (imageObject.rotation === 0 || !processingCanvas.value) {
            // Jika ini gambar yang sudah ada di DB, kita tidak perlu memproses file,
            // cukup gunakan data yang sudah ada.
            if (!imageObject.isNew && !imageObject.file) { // Cek apakah itu gambar dari DB
                resolve({ file: null, width: imageObject.width, height: imageObject.height, isOriginal: true });
                return;
            }
            // Jika ini gambar baru tanpa rotasi, gunakan file aslinya
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
                const newFile = new File([blob], imageObject.file ? imageObject.file.name : `rotated_image_${Date.now()}.png`, { type: imageObject.file ? imageObject.file.type : 'image/png' });
                resolve({ file: newFile, width: newCanvasWidth, height: newCanvasHeight });
            }, imageObject.file ? imageObject.file.type : 'image/png');
        };
        img.onerror = () => {
            console.error("Failed to load image for rotation processing:", imageObject.preview);
            // Fallback: Jika gagal load, resolve dengan file asli (tanpa rotasi)
            resolve({ file: imageObject.file, width: imageObject.width, height: imageObject.height });
        };
        // Load dari preview URL untuk rotasi (bisa blob URL atau URL dari server)
        img.src = imageObject.preview;
    });
};

const triggerUploadAndSave = async (imagesToSaveFromPreview) => {
    isUploading.value = true;
    const finalUploadedImages = []; // Array untuk menampung data gambar yang akan menjadi modelValue baru

    if (!props.pointId) {
        alert('Error: Point ID is missing. Cannot upload image. (Check parent component)');
        isUploading.value = false;
        return;
    }
    console.log('Uploading for Inspection ID:', props.inspectionId, 'Point ID:', props.pointId);

    try {
        for (const img of imagesToSaveFromPreview) {
            // 1. Gambar yang sudah ada dan tidak dirotasi: tambahkan langsung ke final list
            if (!img.isNew && img.rotation === 0 && img.id && img.image_path) {
                finalUploadedImages.push({
                    id: img.id,
                    image_path: img.image_path,
                    width: img.width,
                    height: img.height,
                    rotation: 0,
                    preview: img.preview // Pertahankan URL preview yang sudah ada (dari DB)
                });
                continue; // Lanjut ke gambar berikutnya
            }

            // 2. Gambar baru (isNew=true) atau gambar lama yang dirotasi (rotation !== 0)
            const { file: processedFile, width: newWidth, height: newHeight, isOriginal } = await applyRotationToImage(img);

            // Jika isOriginal true, berarti gambar dari DB tidak perlu diproses ulang/upload ulang
            if (isOriginal && !img.isNew) {
                finalUploadedImages.push({
                    id: img.id,
                    image_path: img.image_path,
                    width: img.width,
                    height: img.height,
                    rotation: 0, // Rotasi sudah diaplikasikan di preview modal jika ada
                    preview: img.preview
                });
                continue;
            }

            // Proses upload
            const formData = new FormData();
            formData.append('inspection_id', props.inspectionId);
            formData.append('point_id', props.pointId);
            formData.append('image', processedFile);
            formData.append('width', newWidth); // Kirim dimensi baru
            formData.append('height', newHeight);
            formData.append('rotation', 0); // Di backend akan disimpan 0 karena rotasi sudah diterapkan ke file

            // Jika ini adalah gambar yang sudah ada dan dirotasi, kirim ID-nya untuk update
            if (img.id && !img.isNew) {
                formData.append('image_id', img.id);
            }

            const response = await axios.post(route('inspections.upload-image'), formData, { // Pastikan route ini sesuai
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            const serverImage = response.data.image;
            const imagePath = serverImage.image_path; // Path relatif dari public
            const publicUrl = serverImage.public_url; // URL lengkap (jika server menyediakannya)

            finalUploadedImages.push({
                id: serverImage.id, // ID gambar dari DB
                image_path: imagePath,
                width: serverImage.width, // Gunakan dimensi dari server
                height: serverImage.height,
                rotation: serverImage.rotation, // Gunakan rotasi dari server (seharusnya 0 setelah diproses)
                preview: publicUrl // URL lengkap untuk preview
            });

            // Revoke Blob URL setelah berhasil diupload
            if (img.preview && img.preview.startsWith('blob:')) {
                URL.revokeObjectURL(img.preview);
            }
        }

        // Emit update:modelValue dengan daftar gambar yang sudah di-upload (dari server)
        emit('update:modelValue', finalUploadedImages); // INI PENTING: Perbarui modelValue
        emit('save', props.pointId); // Emit event save ke komponen induk (misal InspectionCategory/Edit.vue)
        emit('uploaded', { pointId: props.pointId, images: finalUploadedImages }); // Emit event uploaded dengan data gambar yang baru

        closePreviewModal(); // Tutup modal preview

    } catch (error) {
        console.error("Error during image processing/upload:", error);
        if (error.response && error.response.data && error.response.data.message) {
            alert(`Failed to save images: ${error.response.data.message}`);
        } else if (error.response && error.response.data && error.response.data.errors) {
            const errors = Object.values(error.response.data.errors).flat().join('\n');
            alert(`Failed to save images:\n${errors}`);
        } else {
            alert(`Failed to save images: ${error.message}`);
        }
    } finally {
        isUploading.value = false;
    }
};

// Ubah `handleRemovePreviewImage` untuk menangani penghapusan di previewImages
const handleRemovePreviewImage = (indexToRemove) => {
  // Dapatkan gambar yang akan dihapus dari `allImages` (bukan previewImages)
  const imageToRemove = allImages.value[indexToRemove];

  if (!imageToRemove) return;

  // Jika gambar berasal dari DB (punya ID dan bukan `isNew`), panggil `removeImage`
  // `removeImage` akan menghapus dari DB dan juga meng-update `modelValue`.
  if (imageToRemove.id && !imageToRemove.isNew) {
    removeImage(imageToRemove); // Panggil fungsi hapus DB
  } else {
    // Jika gambar baru (hanya ada di previewImages), hapus dari previewImages saja
    const originalIndexInPreviewImages = previewImages.value.findIndex(
        img => (img.preview === imageToRemove.preview && img.isNew) || (img.id && img.id === imageToRemove.id)
    );
    if (originalIndexInPreviewImages !== -1) {
        const removed = previewImages.value.splice(originalIndexInPreviewImages, 1)[0];
        if (removed.preview && removed.preview.startsWith('blob:')) {
            URL.revokeObjectURL(removed.preview);
        }
    }
  }
  // Sesuaikan indeks setelah penghapusan
  if (currentPreviewIndex.value >= allImages.value.length) {
    currentPreviewIndex.value = Math.max(0, allImages.value.length - 1);
  }
  // Jika tidak ada gambar tersisa setelah penghapusan, tutup modal preview
  if (allImages.value.length === 0) {
    closePreviewModal();
  }
};


// Mengubah `removeImage` untuk menerima objek gambar
const removeImage = async (imageObject) => {
    // Jika gambar memiliki ID atau image_path, berarti sudah ada di server/DB, coba hapus dari server
    if (imageObject.id || imageObject.image_path) {
        try {
            await axios.delete(route('inspections.delete-image'), {
                data: {
                    image_id: imageObject.id, // Gunakan ID untuk penghapusan yang lebih akurat
                    image_path: imageObject.image_path // Fallback atau tambahan
                }
            });
            // console.log('Image deleted from server successfully!');
        } catch (error) {
            console.error("Error deleting image from server:", error);
            if (error.response && error.response.data && error.response.data.message) {
                alert(`Failed to delete image from server: ${error.response.data.message}`);
            } else {
                alert(`Failed to delete image from server: ${error.message}`);
            }
            // Jika gagal hapus dari server, mungkin Anda tidak ingin menghapus dari frontend
            return; // Hentikan fungsi jika gagal hapus dari server
        }
    }

    // Revoke Blob URL jika ada
    if (imageObject.preview && imageObject.preview.startsWith('blob:')) {
        URL.revokeObjectURL(imageObject.preview);
    }

    // Hapus gambar dari `modelValue` (gambar yang sudah ada di DB)
    const updatedModelValue = props.modelValue.filter(img => img.id !== imageObject.id);
    emit('update:modelValue', updatedModelValue);

    // Hapus juga dari `previewImages` (jika ada gambar baru atau yang dirotasi)
    previewImages.value = previewImages.value.filter(img => {
      // Jika itu gambar baru yang belum di-upload, hapus berdasarkan preview/file
      if (img.isNew && imageObject.isNew) {
        return img.preview !== imageObject.preview;
      }
      // Jika gambar lama yang mungkin dirotasi, hapus berdasarkan ID
      return img.id !== imageObject.id;
    });

    emit('removeImage', { image: imageObject }); // Emit event removeImage ke komponen induk

    // Logika penyesuaian indeks dan penutupan modal preview sekarang ditangani di `handleRemovePreviewImage`
    // Jika fungsi ini dipanggil langsung dari thumbnail, kita perlu memastikan preview modal tetap sinkron
    if (showPreviewModal.value && allImages.value.length === 0) {
        closePreviewModal();
    } else if (showPreviewModal.value) {
      // Jika preview modal masih terbuka dan ada gambar, sesuaikan indeks
      if (currentPreviewIndex.value >= allImages.value.length) {
        currentPreviewIndex.value = Math.max(0, allImages.value.length - 1);
      }
    }
};

</script>

<style scoped>
/* Anda bisa menambahkan gaya CSS di sini jika diperlukan. */
/* Pastikan z-index untuk modal sesuai. ImageSourceOptionsModal dan WebcamModal harus > ImagePreviewModal */
/* Contoh (jika Anda ingin mengatur di CSS): */

.image-source-options-modal {
  z-index: 60;
}
.webcam-modal {
  z-index: 70;
}
.image-preview-modal {
  z-index: 50;
}

</style>