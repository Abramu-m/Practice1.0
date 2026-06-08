const CACHE_NAME = 'laravel-pwa-v1';

const urlsToCache = [
    '/',
    '/offline'
];

// Install SW
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(urlsToCache);
        })
    );

    self.skipWaiting();
});

// Activate SW + cleanup old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );

    self.clients.claim();
});

// Fetch handler
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    // ❌ Never touch auth or API routes
    const ignoredPaths = ['/login', '/logout', '/register'];

    if (
        request.method !== 'GET' ||
        url.pathname.startsWith('/api') ||
        ignoredPaths.includes(url.pathname)
    ) {
        return;
    }

    event.respondWith(
        fetch(request)
            .then(networkResponse => {
                // Cache successful responses
                return caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, networkResponse.clone());
                    return networkResponse;
                });
            })
            .catch(() => {
                // Fallback to cache
                return caches.match(request)
                    .then(cached => {
                        return cached || caches.match('/offline');
                    });
            })
    );
});