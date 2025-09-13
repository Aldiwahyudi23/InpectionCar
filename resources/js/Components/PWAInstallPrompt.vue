<template>
  <div v-if="showInstallPrompt" class="pwa-install-prompt">
    <div class="pwa-install-content">
      <h3>Install Aplikasi</h3>
      <p>Tambahkan aplikasi ke layar utama untuk pengalaman yang lebih baik</p>
      <div class="pwa-install-buttons">
        <button @click="installApp" class="btn-install">Install</button>
        <button @click="dismissPrompt" class="btn-dismiss">Nanti</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'PWAInstallPrompt',
  setup() {
    const showInstallPrompt = ref(false);
    let deferredPrompt = null;

    onMounted(() => {
      // Listen for the beforeinstallprompt event
      window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Stash the event so it can be triggered later
        deferredPrompt = e;
        // Update UI to notify the user they can install the PWA
        showInstallPrompt.value = true;
      });

      // Listen for the appinstalled event
      window.addEventListener('appinstalled', () => {
        // Hide the app provided install promotion
        showInstallPrompt.value = false;
        // Clear the deferredPrompt so it can be garbage collected
        deferredPrompt = null;
        // Optionally, send analytics event to indicate successful install
        console.log('PWA was installed');
      });
    });

    const installApp = async () => {
      if (!deferredPrompt) return;
      
      // Show the install prompt
      deferredPrompt.prompt();
      
      // Wait for the user to respond to the prompt
      const { outcome } = await deferredPrompt.userChoice;
      
      // We've used the prompt, and can't use it again, throw it away
      deferredPrompt = null;
      
      // Hide the install prompt
      showInstallPrompt.value = false;
      
      // Log the outcome
      console.log(`User response to the install prompt: ${outcome}`);
    };

    const dismissPrompt = () => {
      showInstallPrompt.value = false;
    };

    return {
      showInstallPrompt,
      installApp,
      dismissPrompt
    };
  }
};
</script>

<style scoped>
.pwa-install-prompt {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 16px;
  max-width: 320px;
}

.pwa-install-content h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
  color: #333;
}

.pwa-install-content p {
  margin: 0 0 16px 0;
  font-size: 14px;
  color: #666;
}

.pwa-install-buttons {
  display: flex;
  gap: 8px;
}

.btn-install {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.btn-dismiss {
  background: #f5f5f5;
  color: #333;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}
</style>