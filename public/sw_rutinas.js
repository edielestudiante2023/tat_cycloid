const CACHE_NAME = 'rutinas-v1';
const BASE_PATH  = '/tat_cycloid/public';
const ASSETS_TO_CACHE = [
    BASE_PATH + '/rutinas/calendario',
    BASE_PATH + '/rutinas/actividades',
    BASE_PATH + '/rutinas/asignaciones',
    BASE_PATH + '/js/offline_queue.js',
    // CSS
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css',
    // JS
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js',
    'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
    // Icons
    BASE_PATH + '/icons/icon-192.png',
    BASE_PATH + '/icons/icon-512.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(ASSETS_TO_CACHE).catch(() => {}))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // POST y demás: nunca cachear (y dejar que fallen para que la cola offline actúe)
    if (event.request.method !== 'GET') return;

    // Navegación HTML
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request)
                    .then(cached => cached || caches.match(BASE_PATH + '/rutinas/calendario')))
        );
        return;
    }

    // Assets CDN: cache-first
    if (url.hostname !== location.hostname) {
        event.respondWith(
            caches.match(event.request).then(cached =>
                cached || fetch(event.request).then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    return response;
                })
            )
        );
        return;
    }

    // Local: network-first, cache fallback
    event.respondWith(
        fetch(event.request)
            .then(response => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});

// Background Sync
self.addEventListener('sync', event => {
    if (event.tag === 'sync-firmas' || event.tag === 'sync-rutinas') {
        // La sincronización real la dispara la app al detectar "online" via offline_queue.js
        // Este listener existe para compatibilidad con SyncManager.
    }
});
