// public/sw.js
const CACHE_NAME = 'v1-' + new Date().getTime();
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
  // Halaman dan asset utama
];

// Install event dengan error handling
self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');
  
  // Skip waiting agar SW langsung aktif
  self.skipWaiting();

  // Cache resources dengan error handling
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching app shell');
        
        // Gunakan Promise.all dengan error handling untuk setiap request
        return Promise.all(
          urlsToCache.map((url) => {
            return fetch(url, { credentials: 'include' })
              .then((response) => {
                // Only cache valid responses
                if (!response || response.status !== 200 || response.type !== 'basic') {
                  console.log('Skipping cache for:', url);
                  return Promise.resolve();
                }
                
                console.log('Caching:', url);
                return cache.put(url, response);
              })
              .catch((error) => {
                console.log('Failed to cache:', url, error);
                // Continue even if some files fail to cache
                return Promise.resolve();
              });
          })
        );
      })
      .catch((error) => {
        console.log('Cache opening failed:', error);
      })
  );
});

// Fetch event dengan fallback ke network
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Return cached response or fetch from network
        return response || fetch(event.request);
      })
      .catch(() => {
        // Fallback untuk halaman offline
        if (event.request.mode === 'navigate') {
          return caches.match('/offline.html') || 
                 new Response('Offline - No internet connection');
        }
        return fetch(event.request);
      })
  );
});

// Activate event - cleanup old caches
self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activating...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          // Delete old caches
          if (cacheName !== CACHE_NAME) {
            console.log('Service Worker: Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  
  // Claim clients immediately
  return self.clients.claim();
});