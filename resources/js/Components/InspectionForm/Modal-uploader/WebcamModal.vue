<template>
  <div v-if="show" class="fixed inset-0 z-40 p-0 webcam-modal-container">
    <div class="webcam-content-box">
      <!-- Header -->
      <div class="webcam-header">
        <div class="inspection-point-name">{{ currentInspectionPoint }}</div>
        <button @click="closeModal" class="p-2 rounded-full text-white hover:bg-gray-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Video -->
      <div class="webcam-video-container" @click="triggerFocus">
        <video ref="webcamVideo" autoplay playsinline class="webcam-video"></video>
        <canvas ref="webcamCanvas" class="hidden"></canvas>
        <div v-if="showScreenFlash" class="screen-flash-overlay"></div>
      </div>

      <!-- Footer controls -->
      <div class="webcam-footer">
        <div class="camera-controls">
          <!-- Flash -->
          <button v-if="settings.enable_flash" 
            @click="toggleFlash" 
            :disabled="!isFlashSupported" 
            class="control-button"
            :class="{'active': isFlashOn, 'disabled': !isFlashSupported}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </button>

          <!-- Capture -->
          <button @click="capturePhoto" class="capture-button">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4l2 2h4a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h4l2-2z" />
            </svg>
          </button>

          <!-- Switch Camera -->
          <button v-if="settings.enable_camera_switch && hasMultipleCameras" 
            @click="switchCamera" 
            class="control-button">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
  show: Boolean,
  aspectRatio: Number,
  settings: Object,
  inspectionPoint: String,
  pointname: String
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
    alert('Failed to access camera. Please allow camera permission.');
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
  hasMultipleCameras.value = devices.filter(d => d.kind === 'videoinput').length > 1;
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
      await videoTrack.applyConstraints({ advanced: [{ torch: !isFlashOn.value }] });
      isFlashOn.value = !isFlashOn.value;
    }
  } catch (err) {
    console.error("Flash toggle failed:", err);
  }
};

const switchCamera = async () => {
  if (!hasMultipleCameras.value) return;
  currentFacingMode.value = currentFacingMode.value === 'environment' ? 'user' : 'environment';
  currentInspectionPoint.value = `${props.inspectionPoint} (${currentFacingMode.value === 'environment' ? 'Rear' : 'Front'})`;
  await initializeWebcam();
};

const triggerFocus = async () => {
  if (!videoTrack) return;
  const caps = videoTrack.getCapabilities();
  if (caps.focusMode && caps.focusMode.includes('continuous')) {
    try {
      await videoTrack.applyConstraints({ advanced: [{ focusMode: "continuous" }] });
    } catch (e) {
      console.warn("Focus not supported", e);
    }
  }
};

const capturePhoto = async () => {
  const video = webcamVideo.value;
  const canvas = webcamCanvas.value;
  if (!video || !canvas) return;
  const ctx = canvas.getContext('2d');
  const vw = video.videoWidth, vh = video.videoHeight;
  let dw = vw, dh = vh, ox = 0, oy = 0;
  if (vw / vh > props.aspectRatio) {
    dw = vh * props.aspectRatio; ox = (vw - dw) / 2;
  } else if (vw / vh < props.aspectRatio) {
    dh = vw / props.aspectRatio; oy = (vh - dh) / 2;
  }
  canvas.width = dw; canvas.height = dh;
  ctx.drawImage(video, ox, oy, dw, dh, 0, 0, dw, dh);
  canvas.toBlob(blob => {
    if (blob) {
      const file = new File([blob], `captured_${Date.now()}.png`, { type: 'image/png' });
      emit('photoCaptured', file);
    }
  }, 'image/png');
};

watch(() => props.show, (v) => { v ? initializeWebcam() : closeModal(); });
watch(() => props.inspectionPoint, (v) => { currentInspectionPoint.value = v || 'Camera'; });

onBeforeUnmount(() => { if (mediaStream) mediaStream.getTracks().forEach(t => t.stop()); });
</script>

<style scoped>
.webcam-modal-container {
  background-color: rgba(0,0,0,0.9);
}
.webcam-content-box {
  background: black;
  width: 100%; height: 100%;
  display: flex; flex-direction: column;
}
.webcam-header {
  background: rgba(0,0,0,0.7);
  color: white;
  padding: 1rem;
  display: flex; justify-content: space-between; align-items: center;
}
.inspection-point-name { flex-grow:1; text-align:center; font-weight:bold; }
.webcam-video-container {
  flex-grow: 1; position: relative;
  display: flex; justify-content:center; align-items:center;
  background:black;
}
.webcam-video {
  width:100%; height:100%; object-fit:cover; position:absolute;
}
.webcam-footer {
  background: rgba(0,0,0,0.7);
  padding: 1rem; display:flex; justify-content:center; align-items:center;
}
.camera-controls {
  display:flex; justify-content:space-around; align-items:center;
  width: 100%; max-width: 300px;
}
.control-button, .capture-button {
  background:white; border:none;
  width:60px; height:60px;
  border-radius:50%;
  display:flex; justify-content:center; align-items:center;
  box-shadow:0 4px 10px rgba(0,0,0,0.3);
  cursor:pointer;
  transition:0.2s;
}
.control-button svg, .capture-button svg { stroke:black; }
.control-button:hover, .capture-button:hover { transform: scale(1.05); }
.capture-button {
  width:75px; height:75px;
}
.control-button.disabled { opacity:0.4; cursor:not-allowed; }
.control-button.active { background:yellow; }
.screen-flash-overlay {
  position:absolute; top:0; left:0; width:100%; height:100%;
  background:white; z-index:20;
}
</style>
