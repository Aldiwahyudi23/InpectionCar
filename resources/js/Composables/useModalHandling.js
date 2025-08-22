import { ref } from 'vue';

export function useModalHandling() {
  const showSourceOptionsModal = ref(false);
  const showWebcamModal = ref(false);
  const showPreviewModal = ref(false);

  const openSourceOptions = () => {
    if (showPreviewModal.value) {
      showPreviewModal.value = false;
    }
    showSourceOptionsModal.value = true;
  };

  const closeSourceOptions = () => {
    showSourceOptionsModal.value = false;
  };

  const openWebcam = () => {
    showSourceOptionsModal.value = false;
    showWebcamModal.value = true;
  };

  const closeWebcam = () => {
    showWebcamModal.value = false;
  };

  const openPreviewModal = (initialIdx = 0) => {
    showPreviewModal.value = true;
  };

  const closePreviewModal = () => {
    showPreviewModal.value = false;
  };

  return {
    showSourceOptionsModal,
    showWebcamModal,
    showPreviewModal,
    openSourceOptions,
    closeSourceOptions,
    openWebcam,
    closeWebcam,
    openPreviewModal,
    closePreviewModal
  };
}