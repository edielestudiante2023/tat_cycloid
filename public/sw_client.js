const CACHE_NAME = 'client-dashboard-v1';
const BASE_PATH = '/tat_cycloid/public';
const ASSETS_TO_CACHE = [
    BASE_PATH + '/client/dashboard',
    // CDN - CSS
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
    // CDN - JS
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://code.jquery.com/jquery-3.7.0.min.js',
    // Logos e imagenes locales
    BASE_PATH + '/uploads/logocycloid_tatblancoslogan.png',
    BASE_PATH + '/uploads/logosst.png',
    BASE_PATH + '/uploads/logocycloidsinfondo.png',
    BASE_PATH + '/uploads/logocycloid.png',
    BASE_PATH + '/otto/otto.png',
    BASE_PATH + '/icons/icon-192.png',
    BASE_PATH + '/icons/icon-512.png'
];

// ── Install - cache assets ──
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(ASSETS_TO_CACHE))
            .then(() => self.skipWaiting())
    );
});

// ── Activate - clean old caches ──
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

// ── Fetch - strategies by content type ──
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET requests (POST, etc.)
    if (event.request.method !== 'GET') return;

    // ── Navigation requests (HTML pages) ──
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request)
                        .then(cached => {
                            if (cached) return cached;
                            // Fallback: servir dashboard cacheado
                            return caches.match(BASE_PATH + '/client/dashboard');
                        });
                })
        );
        return;
    }

    // ── CDN assets (cache-first) ──
    if (url.hostname !== location.hostname) {
        event.respondWith(
            caches.match(event.request)
                .then(cached => {
                    if (cached) return cached;
                    return fetch(event.request).then(response => {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                        return response;
                    });
                })
        );
        return;
    }

    // ── Local assets (network-first, cache fallback) ──
    event.respondWith(
        fetch(event.request)
            .then(response => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
