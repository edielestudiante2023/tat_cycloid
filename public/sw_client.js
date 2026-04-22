const CACHE_NAME = 'client-dashboard-v6';
const BASE_PATH = '/tat_cycloid/public';
const ASSETS_TO_CACHE = [
    BASE_PATH + '/client/dashboard',
    // CDN - CSS
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
    'https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css',
    'https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css',
    'https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css',
    'https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css',
    'https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css',
    'https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css',
    // CDN - JS
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://code.jquery.com/jquery-3.7.0.min.js',
    'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js',
    'https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js',
    'https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap5.min.js',
    'https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js',
    'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js',
    'https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js',
    'https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js',
    'https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js',
    'https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js',
    'https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js',
    'https://cdn.datatables.net/responsive/2.4.0/js/responsive.bootstrap5.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
    'https://cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json',
    'https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css',
    'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js',
    'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
    // Iconos redes sociales (footer)
    'https://cdn-icons-png.flaticon.com/512/733/733547.png',
    'https://cdn-icons-png.flaticon.com/512/733/733561.png',
    'https://cdn-icons-png.flaticon.com/512/733/733558.png',
    'https://cdn-icons-png.flaticon.com/512/3046/3046126.png',
    // Logos e imagenes locales
    BASE_PATH + '/uploads/tat.png',
    BASE_PATH + '/uploads/tat.png',
    BASE_PATH + '/uploads/tat.png',
    BASE_PATH + '/uploads/tat.png',
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
