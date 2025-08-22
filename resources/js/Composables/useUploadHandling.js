import axios from 'axios';

export function useUploadHandling({
  props,
  emit,
  galleryInput,
  previewImages,
  allImages,
  currentPreviewIndex,
  isUploading,
  allowMultiple,
  loadImageWithDimensions,
  applyRotationToImage,
  openSourceOptions,
  closeSourceOptions,
  openWebcam,
  closeWebcam,
  openPreviewModal
}) {
  const handleImageSelect = async (event) => {
    const files = Array.from(event.target.files);
    if (!files.length) {
      closeSourceOptions();
      if (allImages.value.length > 0) {
        openPreviewModal();
      }
      return;
    }

    try {
      const newImages = await Promise.all(files.map(loadImageWithDimensions));
      const currentTotalImages = allImages.value.length;
      const allowedToAdd = props.settings.max_files - currentTotalImages;
      
      const imagesToProcess = allowMultiple.value 
        ? newImages.slice(0, allowedToAdd) 
        : newImages.slice(0, 1);

      if (!allowMultiple.value) {
        previewImages.value.forEach(img => {
          if (img.preview && img.preview.startsWith('blob:')) {
            URL.revokeObjectURL(img.preview);
          }
        });
        previewImages.value = imagesToProcess;
      } else {
        previewImages.value.push(...imagesToProcess);
      }
      
      closeSourceOptions();

      if (imagesToProcess.length > 0) {
        currentPreviewIndex.value = Math.max(0, allImages.value.length - imagesToProcess.length);
        openPreviewModal();
      } else if (allImages.value.length > 0) {
        openPreviewModal();
      }
      
      event.target.value = '';
    } catch (error) {
      console.error("Error processing selected images:", error);
      closeSourceOptions();
      if (allImages.value.length > 0) {
        openPreviewModal();
      }
    }
  };

  const handlePhotoCaptured = (newImageFile) => {
    const currentTotalImages = allImages.value.length;
    if (!allowMultiple.value || currentTotalImages < props.settings.max_files) {
      const img = new Image();
      img.onload = () => {
        const newImage = {
          file: newImageFile,
          preview: URL.createObjectURL(newImageFile),
          rotation: 0,
          width: img.naturalWidth,
          height: img.naturalHeight,
          isNew: true
        };

        if (!allowMultiple.value) {
          previewImages.value.forEach(img => {
            if (img.preview && img.preview.startsWith('blob:')) {
              URL.revokeObjectURL(img.preview);
            }
          });
          previewImages.value = [newImage];
        } else {
          previewImages.value.push(newImage);
        }
        
        closeWebcam();
        closeSourceOptions();
        currentPreviewIndex.value = allImages.value.length - 1;
        openPreviewModal();
      };
      img.onerror = () => {
        const newImage = { 
          file: newImageFile, 
          preview: URL.createObjectURL(newImageFile), 
          rotation: 0, 
          width: 0, 
          height: 0, 
          isNew: true 
        };
        
        if (!allowMultiple.value) {
          previewImages.value = [newImage];
        } else {
          previewImages.value.push(newImage);
        }
        
        currentPreviewIndex.value = allImages.value.length - 1;
        closeWebcam();
        closeSourceOptions();
        openPreviewModal();
      };
      img.src = URL.createObjectURL(newImageFile);
    } else {
      alert(`Maksimum ${props.settings.max_files} file diizinkan.`);
      closeWebcam();
      closeSourceOptions();
      if (allImages.value.length > 0) {
        openPreviewModal();
      }
    }
  };

  const triggerUploadAndSave = async (imagesToSaveFromPreview) => {
    isUploading.value = true;
    const finalUploadedImages = [];

    if (!props.pointId) {
      alert('Error: Point ID is missing. Cannot upload image.');
      isUploading.value = false;
      return;
    }

    try {
      for (const img of imagesToSaveFromPreview) {
        if (!img.isNew && img.rotation === 0 && img.id && img.image_path) {
          finalUploadedImages.push({
            id: img.id,
            image_path: img.image_path,
            width: img.width,
            height: img.height,
            rotation: 0,
            preview: img.preview
          });
          continue;
        }

        const { file: processedFile, width: newWidth, height: newHeight, isOriginal } = await applyRotationToImage(img);

        if (isOriginal && !img.isNew) {
          finalUploadedImages.push({
            id: img.id,
            image_path: img.image_path,
            width: img.width,
            height: img.height,
            rotation: 0,
            preview: img.preview
          });
          continue;
        }

        const formData = new FormData();
        formData.append('inspection_id', props.inspectionId);
        formData.append('point_id', props.pointId);
        formData.append('image', processedFile);
        formData.append('width', newWidth);
        formData.append('height', newHeight);
        formData.append('rotation', 0);

        if (img.id && !img.isNew) {
          formData.append('image_id', img.id);
        }

        const response = await axios.post(route('inspections.upload-image'), formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        const serverImage = response.data.image;
        finalUploadedImages.push({
          id: serverImage.id,
          image_path: serverImage.image_path,
          width: serverImage.width,
          height: serverImage.height,
          rotation: serverImage.rotation,
          preview: serverImage.public_url
        });

        if (img.preview && img.preview.startsWith('blob:')) {
          URL.revokeObjectURL(img.preview);
        }
      }

      emit('update:modelValue', finalUploadedImages);
      emit('save', props.pointId);
      emit('uploaded', { pointId: props.pointId, images: finalUploadedImages });
      
      // Close preview modal handled by parent
    } catch (error) {
      console.error("Error during image processing/upload:", error);
      if (error.response?.data?.message) {
        alert(`Failed to save images: ${error.response.data.message}`);
      } else if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat().join('\n');
        alert(`Failed to save images:\n${errors}`);
      } else {
        alert(`Failed to save images: ${error.message}`);
      }
    } finally {
      isUploading.value = false;
    }
  };

  const removeImage = async (imageObject) => {
    if (imageObject.id || imageObject.image_path) {
      try {
        await axios.delete(route('inspections.delete-image'), {
          data: {
            image_id: imageObject.id,
            image_path: imageObject.image_path
          }
        });
      } catch (error) {
        console.error("Error deleting image from server:", error);
        if (error.response?.data?.message) {
          alert(`Failed to delete image from server: ${error.response.data.message}`);
        } else {
          alert(`Failed to delete image from server: ${error.message}`);
        }
        return;
      }
    }

    if (imageObject.preview && imageObject.preview.startsWith('blob:')) {
      URL.revokeObjectURL(imageObject.preview);
    }

    const updatedModelValue = props.modelValue.filter(img => img.id !== imageObject.id);
    emit('update:modelValue', updatedModelValue);

    previewImages.value = previewImages.value.filter(img => {
      if (img.isNew && imageObject.isNew) {
        return img.preview !== imageObject.preview;
      }
      return img.id !== imageObject.id;
    });

    emit('removeImage', { image: imageObject });
  };

  const handleRemovePreviewImage = (indexToRemove) => {
    const imageToRemove = allImages.value[indexToRemove];
    if (!imageToRemove) return;

    if (imageToRemove.id && !imageToRemove.isNew) {
      removeImage(imageToRemove);
    } else {
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
    
    if (currentPreviewIndex.value >= allImages.value.length) {
      currentPreviewIndex.value = Math.max(0, allImages.value.length - 1);
    }
  };

  return {
    handleImageSelect,
    handlePhotoCaptured,
    triggerUploadAndSave,
    removeImage,
    handleRemovePreviewImage
  };
}