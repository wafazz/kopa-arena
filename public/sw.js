const CACHE_NAME = 'kopa-arena-v1';
const STATIC_ASSETS = [
    '/vendor/bootstrap-5.3.8.min.css',
    '/vendor/fontawesome-7.1.0/css/all.min.css',
    '/css/font-face.css',
    '/css/theme.css',
    '/images/pwa/icon-192x192.png',
    '/images/pwa/icon-512x512.png'
];

// Install — cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch — network first, fallback to cache for static assets
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response.ok && event.request.url.match(/\.(css|js|png|jpg|woff2?)$/)) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
