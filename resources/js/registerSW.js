import { register } from 'register-service-worker';

if (process.env.NODE_ENV === 'production') {
  register(`${process.env.BASE_URL}service-worker.js`, {
    ready() {
      console.log('App is being served from cache by a service worker.');
    },
    registered(registration) {
      console.log('Service worker has been registered.');
      
      // Check for updates every hour
      setInterval(() => {
        registration.update();
      }, 60 * 60 * 1000);
    },
    cached() {
      console.log('Content has been cached for offline use.');
    },
    updatefound() {
      console.log('New content is downloading.');
    },
    updated(registration) {
      console.log('New content is available.');
      
      // Clear caches and reload
      const clearCachesAndReload = () => {
        if ('caches' in window) {
          caches.keys().then(cacheNames => {
            cacheNames.forEach(cacheName => {
              caches.delete(cacheName);
            });
            window.location.reload();
          });
        } else {
          window.location.reload();
        }
      };
      
      // Show update notification
      if (confirm('Versi baru tersedia. Muat ulang halaman sekarang?')) {
        clearCachesAndReload();
      } else {
        // Simpan informasi bahwa update tersedia
        localStorage.setItem('sw-update-available', 'true');
      }
    },
    offline() {
      console.log('No internet connection found. App is running in offline mode.');
    },
    error(error) {
      console.error('Error during service worker registration:', error);
    },
  });
  
  // Check for pending updates on page load
  if (localStorage.getItem('sw-update-available') === 'true') {
    if (confirm('Update tersedia dari kunjungan sebelumnya. Muat ulang sekarang?')) {
      if ('caches' in window) {
        caches.keys().then(cacheNames => {
          cacheNames.forEach(cacheName => {
            caches.delete(cacheName);
          });
          window.location.reload();
        });
      } else {
        window.location.reload();
      }
    }
    localStorage.removeItem('sw-update-available');
  }
}