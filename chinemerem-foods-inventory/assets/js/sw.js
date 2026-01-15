/**
 * Chinemerem Foods Service Worker
 */

const CACHE_NAME = 'cfi-cache-v1';
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
    '/',
    '/wp-content/plugins/chinemerem-foods-inventory/assets/css/main.css',
    '/wp-content/plugins/chinemerem-foods-inventory/assets/js/main.js',
    '/wp-content/plugins/chinemerem-foods-inventory/assets/images/logo.svg',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install event
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            console.log('CFI: Caching static assets');
            return cache.addAll(STATIC_ASSETS.filter(url => !url.startsWith('http')));
        })
    );
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.filter(function(cacheName) {
                    return cacheName.startsWith('cfi-') && cacheName !== CACHE_NAME;
                }).map(function(cacheName) {
                    return caches.delete(cacheName);
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event
self.addEventListener('fetch', function(event) {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip admin requests
    if (event.request.url.includes('/wp-admin/')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then(function(response) {
            if (response) {
                return response;
            }

            return fetch(event.request).then(function(response) {
                // Don't cache non-successful responses
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                // Clone the response
                const responseToCache = response.clone();

                // Cache static assets
                if (event.request.url.includes('/assets/')) {
                    caches.open(CACHE_NAME).then(function(cache) {
                        cache.put(event.request, responseToCache);
                    });
                }

                return response;
            }).catch(function() {
                // Return offline page for navigation requests
                if (event.request.mode === 'navigate') {
                    return caches.match(OFFLINE_URL);
                }
            });
        })
    );
});

// Background sync for offline orders
self.addEventListener('sync', function(event) {
    if (event.tag === 'sync-orders') {
        event.waitUntil(syncOrders());
    }
});

async function syncOrders() {
    // Get pending orders from IndexedDB or localStorage
    // This would be implemented with a proper IndexedDB setup
    console.log('CFI: Syncing offline orders');
}

// Push notifications (future enhancement)
self.addEventListener('push', function(event) {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: '/wp-content/plugins/chinemerem-foods-inventory/assets/images/logo.svg',
            badge: '/wp-content/plugins/chinemerem-foods-inventory/assets/images/badge.png',
            vibrate: [100, 50, 100],
            data: {
                url: data.url
            }
        };
        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    if (event.notification.data && event.notification.data.url) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});
