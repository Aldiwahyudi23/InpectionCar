// resources/js/pwa.js
import { ref } from 'vue';

// Buat reactive state untuk PWA
export const usePWA = () => {
    const deferredPrompt = ref(null);
    const isAppInstalled = ref(false);
    const canInstall = ref(false);

    // Check if app is already installed
    const checkInstallStatus = () => {
        if (window.matchMedia('(display-mode: standalone)').matches) {
            isAppInstalled.value = true;
            canInstall.value = false;
        }
    };

    // Register Service Worker
    const registerServiceWorker = async () => {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigatorServiceWorker.register('/sw.js');
                console.log('SW registered: ', registration);
                
                // Periodic update check
                setInterval(() => {
                    registration.update();
                }, 1000 * 60 * 60);
                
                return true;
            } catch (error) {
                console.log('SW registration failed: ', error);
                return false;
            }
        }
        return false;
    };

    // Setup event listeners
    const setupEventListeners = () => {
        // Before install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('beforeinstallprompt event fired');
            e.preventDefault();
            deferredPrompt.value = e;
            canInstall.value = true;
        });

        // App installed
        window.addEventListener('appinstalled', () => {
            console.log('PWA was installed');
            isAppInstalled.value = true;
            canInstall.value = false;
            deferredPrompt.value = null;
        });
    };

    // Install app
    const installApp = async () => {
        if (deferredPrompt.value) {
            deferredPrompt.value.prompt();
            const { outcome } = await deferredPrompt.value.userChoice;
            console.log(`User response: ${outcome}`);
            deferredPrompt.value = null;
            canInstall.value = false;
        }
    };

    // Initialize
    const initPWA = async () => {
        checkInstallStatus();
        setupEventListeners();
        await registerServiceWorker();
    };

    return {
        deferredPrompt,
        isAppInstalled,
        canInstall,
        installApp,
        initPWA
    };
};

// Export untuk digunakan di komponen Vue
export default usePWA;