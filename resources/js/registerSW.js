import { register } from 'register-service-worker';

if (process.env.NODE_ENV === 'production') {
  register(`${process.env.BASE_URL}service-worker.js`, {
    ready() {
      console.log(
        'App is being served from cache by a service worker.\n' +
        'For more details, visit https://goo.gl/AFskqB'
      );
    },
    registered(registration) {
      console.log('Service worker has been registered.');
      
      // Periodic update check (every hour)
      setInterval(() => {
        registration.update();
      }, 60 * 60 * 1000);
    },
    cached(registration) {
      console.log('Content has been cached for offline use.');
    },
    updatefound(registration) {
      console.log('New content is downloading.');
    },
    updated(registration) {
      console.log('New content is available; please refresh.');
      
      // Bisa ditambahkan logika untuk menampilkan notifikasi update
      // Contoh: tampilkan button untuk refresh page
      if (confirm('Update tersedia. Muat ulang halaman sekarang?')) {
        window.location.reload();
      }
    },
    offline() {
      console.log('No internet connection found. App is running in offline mode.');
    },
    error(error) {
      console.error('Error during service worker registration:', error);
    },
  });
}