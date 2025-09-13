<template>
  <div v-if="showInstallPrompt" class="pwa-install-prompt">
    <div class="pwa-install-content">
      <div class="pwa-header">
        <div class="pwa-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
          </svg>
        </div>
        <button @click="dismissPrompt" class="pwa-close-btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
          </svg>
        </button>
      </div>
      
      <div class="pwa-body">
        <h3>Install Aplikasi</h3>
        <p>Tambahkan aplikasi ke layar utama untuk pengalaman yang lebih baik dan akses offline</p>
      </div>
      
      <div class="pwa-features">
        <div class="feature">
          <div class="feature-icon">🚀</div>
          <span>Akses Lebih Cepat</span>
        </div>
        <div class="feature">
          <div class="feature-icon">📱</div>
          <span>Seperti Aplikasi Native</span>
        </div>
        <div class="feature">
          <div class="feature-icon">⚡</div>
          <span>Bekerja Offline</span>
        </div>
      </div>
      
      <div class="pwa-install-buttons">
        <button @click="installApp" class="btn-install">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
          </svg>
          Install Sekarang
        </button>
        <button @click="dismissPrompt" class="btn-dismiss">Nanti Saja</button>
      </div>
      
      <div class="pwa-footer">
        <small>Klik "Install" lalu "Add to Home Screen"</small>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'PWAInstallButton',
  setup() {
    const showInstallPrompt = ref(false);
    let deferredPrompt = null;

    onMounted(() => {
      // Cek jika prompt sudah pernah ditutup
      const promptDismissed = localStorage.getItem('pwaPromptDismissed');
      if (promptDismissed) return;
      
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
      // Simpan di localStorage agar tidak muncul lagi hari ini
      localStorage.setItem('pwaPromptDismissed', 'true');
      // Reset setelah 24 jam
      setTimeout(() => {
        localStorage.removeItem('pwaPromptDismissed');
      }, 24 * 60 * 60 * 1000);
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
  z-index: 10000;
  /* Gradient background yang sesuai dengan navigasi */
  background: linear-gradient(135deg, #0ea5e9 0%, #4338ca 100%);
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.1);
  padding: 20px;
  max-width: 380px;
  color: white;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
  animation: slideIn 0.5s ease-out;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

@keyframes slideIn {
  from {
    transform: translateY(100px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.pwa-install-content {
  position: relative;
}

.pwa-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.pwa-icon {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pwa-icon svg {
  width: 24px;
  height: 24px;
  color: white;
}

.pwa-close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.3s;
}

.pwa-close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.pwa-close-btn svg {
  width: 18px;
  height: 18px;
  color: white;
}

.pwa-body h3 {
  margin: 0 0 8px 0;
  font-size: 20px;
  font-weight: 700;
}

.pwa-body p {
  margin: 0 0 20px 0;
  font-size: 14px;
  opacity: 0.9;
  line-height: 1.5;
}

.pwa-features {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.feature {
  display: flex;
  align-items: center;
  gap: 12px;
}

.feature-icon {
  font-size: 20px;
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.feature span {
  font-size: 14px;
  font-weight: 500;
}

.pwa-install-buttons {
  display: flex;
  gap: 12px;
  margin-bottom: 12px;
}

.btn-install {
  flex: 1;
  background: white;
  color: #0ea5e9;
  border: none;
  padding: 12px 16px;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 6px rgba(14, 165, 233, 0.3);
}

.btn-install:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(14, 165, 233, 0.4);
}

.btn-install svg {
  width: 18px;
  height: 18px;
}

.btn-dismiss {
  background: transparent;
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 12px 16px;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.3s;
}

.btn-dismiss:hover {
  background: rgba(255, 255, 255, 0.1);
}

.pwa-footer {
  text-align: center;
}

.pwa-footer small {
  opacity: 0.7;
  font-size: 12px;
}

/* Responsive design untuk mobile */
@media (max-width: 640px) {
  .pwa-install-prompt {
    bottom: 0;
    right: 0;
    left: 0;
    max-width: none;
    border-radius: 16px 16px 0 0;
    margin: 0;
  }
  
  .pwa-install-buttons {
    flex-direction: column;
  }
  
  .pwa-features {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .feature {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
  
  .feature span {
    font-size: 12px;
  }
}

/* Animasi untuk close */
.pwa-install-prompt.leaving {
  animation: slideOut 0.3s ease-in forwards;
}

@keyframes slideOut {
  from {
    transform: translateY(0);
    opacity: 1;
  }
  to {
    transform: translateY(100px);
    opacity: 0;
  }
}
</style>