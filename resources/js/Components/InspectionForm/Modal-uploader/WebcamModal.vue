<template>
  <div v-if="show" class="fixed inset-0 z-40 p-0 webcam-modal-container">
    <div class="webcam-content-box">
      <div class="webcam-header">
        <div class="inspection-point-name">{{ currentInspectionPoint }}</div>
        <button @click="closeModal" class="p-2 rounded-full text-white hover:bg-gray-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="webcam-video-container" @click="triggerFocus">
        <video ref="webcamVideo" autoplay playsinline class="webcam-video"></video>
        <canvas ref="webcamCanvas" class="hidden"></canvas>
        <div v-if="showScreenFlash" class="screen-flash-overlay"></div>
      </div>
      <div class="webcam-footer">
        <div class="camera-controls">
          <button v-if="settings.enable_flash" @click="toggleFlash" :disabled="!isFlashSupported" class="control-button" :class="{'bg-gray-700': isFlashOn, 'opacity-50 cursor-not-allowed': !isFlashSupported}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </button>
          
          <button @click="capturePhoto" class="capture-button"></button>
          
          <button v-if="settings.enable_camera_switch && hasMultipleCameras" @click="switchCamera" class="control-button">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  show: Boolean,
  aspectRatio: Number,
  settings: Object,
  inspectionPoint: String
});

const emit = defineEmits(['close', 'photoCaptured']);

const webcamVideo = ref(null);
const webcamCanvas = ref(null);

let mediaStream = null;
let videoTrack = null;

const currentFacingMode = ref('environment');
const hasMultipleCameras = ref(false);
const isFlashSupported = ref(false);
const isFlashOn = ref(false);
const showScreenFlash = ref(false);
const currentInspectionPoint = ref(props.inspectionPoint || 'Camera');

const initializeWebcam = async () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  isFlashOn.value = false;

  try {
    const videoConstraints = {
      facingMode: currentFacingMode.value,
      width: { ideal: 1280 },
      height: { ideal: 960 },
      aspectRatio: { exact: props.aspectRatio }
    };

    if (currentFacingMode.value === 'environment') {
      videoConstraints.advanced = [{ focusMode: "continuous" }];
    }

    mediaStream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints });
    videoTrack = mediaStream.getVideoTracks()[0];
    webcamVideo.value.srcObject = mediaStream;

    webcamVideo.value.onloadedmetadata = async () => {
      webcamVideo.value.style.width = '100%';
      webcamVideo.value.style.height = 'auto';
      webcamVideo.value.style.objectFit = 'cover';
      await checkCameraCapabilities();
    };

  } catch (err) {
    console.error("Error accessing camera: ", err);
    alert('Failed to access camera. Please ensure camera permissions are granted and try again.');
    emit('close');
  }
};

const closeModal = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  emit('close');
};

const checkCameraCapabilities = async () => {
  if (!videoTrack) return;

  const devices = await navigator.mediaDevices.enumerateDevices();
  const videoDevices = devices.filter(device => device.kind === 'videoinput');
  hasMultipleCameras.value = videoDevices.length > 1;

  const capabilities = videoTrack.getCapabilities();
  isFlashSupported.value = capabilities.torch || (capabilities.fillLightMode && capabilities.fillLightMode.includes('torch'));
};

const toggleFlash = async () => {
  if (!videoTrack || !props.settings.enable_flash) return;

  if (currentFacingMode.value === 'user') {
    isFlashOn.value = !isFlashOn.value;
    return;
  }

  try {
    if (videoTrack.getCapabilities().torch) {
      await videoTrack.applyConstraints({
        advanced: [{ torch: !isFlashOn.value }]
      });
      isFlashOn.value = !isFlashOn.value;
    } else {
      const capabilities = videoTrack.getCapabilities();
      if (capabilities.fillLightMode && capabilities.fillLightMode.includes('torch')) {
        await videoTrack.applyConstraints({
          fillLightMode: isFlashOn.value ? 'none' : 'torch'
        });
        isFlashOn.value = !isFlashOn.value;
      } else {
        alert("Flash/Torch is not supported on this camera.");
        isFlashSupported.value = false;
      }
    }
  } catch (err) {
    console.error("Error toggling flash: ", err);
    alert("Could not toggle flash. Feature might not be supported.");
    isFlashSupported.value = false;
  }
};

const switchCamera = async () => {
  if (!hasMultipleCameras.value || !props.settings.enable_camera_switch) return;
  currentFacingMode.value = currentFacingMode.value === 'environment' ? 'user' : 'environment';
  currentInspectionPoint.value = `${props.inspectionPoint} (${currentFacingMode.value === 'environment' ? 'Rear' : 'Front'})`;
  await initializeWebcam();
};

const triggerFocus = async () => {
  if (!videoTrack) return;
  const capabilities = videoTrack.getCapabilities();
  if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
    try {
      await videoTrack.applyConstraints({
        advanced: [{ focusMode: "continuous" }]
      });
    } catch (e) {
      console.warn("Failed to apply continuous focus constraint:", e);
    }
  } else {
    console.log("Autofocus not explicitly controllable or not supported.");
  }
};

const capturePhoto = async () => {
  if (!webcamVideo.value || !webcamCanvas.value) return;

  if (currentFacingMode.value === 'user' && isFlashOn.value) {
    showScreenFlash.value = true;
    await new Promise(resolve => setTimeout(resolve, 100));
    showScreenFlash.value = false;
  }

  const video = webcamVideo.value;
  const canvas = webcamCanvas.value;
  const context = canvas.getContext('2d');

  const videoWidth = video.videoWidth;
  const videoHeight = video.videoHeight;
  
  let drawWidth = videoWidth;
  let drawHeight = videoHeight;
  let offsetX = 0;
  let offsetY = 0;

  if (videoWidth / videoHeight > props.aspectRatio) {
    drawWidth = videoHeight * props.aspectRatio;
    offsetX = (videoWidth - drawWidth) / 2;
  } else if (videoWidth / videoHeight < props.aspectRatio) {
    drawHeight = videoWidth / props.aspectRatio;
    offsetY = (videoHeight - drawHeight) / 2;
  }
  
  canvas.width = drawWidth;
  canvas.height = drawHeight;

  context.drawImage(
    video,
    offsetX, offsetY,
    drawWidth, drawHeight,
    0, 0,
    canvas.width, canvas.height
  );

  canvas.toBlob((blob) => {
    if (blob) {
      const file = new File([blob], `captured_image_${Date.now()}.png`, { type: 'image/png' });
      emit('photoCaptured', file);
    } else {
      console.error("Failed to convert canvas to blob.");
    }
  }, 'image/png');
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    currentInspectionPoint.value = props.inspectionPoint || 'Camera';
    initializeWebcam();
  } else {
    closeModal();
  }
});

watch(() => props.inspectionPoint, (newVal) => {
  currentInspectionPoint.value = newVal || 'Camera';
});

onBeforeUnmount(() => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
});
</script>

<style scoped>
.webcam-modal-container {
  padding: 0;
  background-color: rgba(0, 0, 0, 0.9);
}
.webcam-content-box {
  background-color: black;
  width: 100%;
  height: 100%;
  max-width: none;
  border-radius: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.webcam-header {
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  z-index: 10;
  min-height: 4rem;
}
.inspection-point-name {
  font-size: 1.2rem;
  font-weight: bold;
  flex-grow: 1;
  text-align: center;
  padding-right: 2rem; /* Untuk mengkompensasi tombol close */
}
.webcam-video-container {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  background-color: black;
  padding: 0;
  width: 100%;
  padding-bottom: calc(100% * 3 / 4); /* 4:3 aspect ratio */
  overflow: hidden;
  cursor: pointer;
}
.webcam-video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.webcam-footer {
  background-color: rgba(0, 0, 0, 0.7);
  padding: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  z-index: 10;
  min-height: 6rem;
}
.camera-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  max-width: 300px;
}
.control-button {
  background-color: rgba(255, 255, 255, 0.1);
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  color: white;
}
.control-button:hover {
  background-color: rgba(255, 255, 255, 0.2);
}
.control-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.capture-button {
  background-color: white;
  border: 4px solid rgba(255, 255, 255, 0.5);
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}
.capture-button:hover {
  background-color: #f0f0f0;
  border-color: rgba(255, 255, 255, 0.7);
  transform: scale(1.05);
}
.screen-flash-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: white;
  opacity: 1;
  z-index: 20;
  transition: opacity 0.05s ease-out;
}
</style>