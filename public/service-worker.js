/**
 * Service Worker - FC Chiche PWA
 * Stratégie : Network First pour API, Cache First pour assets
 */

'use strict';

const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `fcchiche-${CACHE_VERSION}`;
const API_CACHE_NAME = `fcchiche-api-${CACHE_VERSION}`;

const STATIC_ASSETS = [
    '/',
    '/index.php',
    '/assets/css/variables.css',
    '/assets/css/main.css',
    '/assets/css/components.css',
    '/assets/js/app.js',
    '/manifest.json'
];

const API_CACHE_TIME = 5 * 60 * 1000; // 5 minutes

/**
 * Installation - Cache assets statiques
 */
self.addEventListener('install', event => {
    console.log('[SW] Installing version', CACHE_VERSION);
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
            .catch(error => console.error('[SW] Install error:', error))
    );
});

/**
 * Activation - Nettoyage anciens caches
 */
self.addEventListener('activate', event => {
    console.log('[SW] Activating version', CACHE_VERSION);
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames
                        .filter(name => name.startsWith('fcchiche-') && name !== CACHE_NAME && name !== API_CACHE_NAME)
                        .map(name => {
                            console.log('[SW] Deleting old cache:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => self.clients.claim())
            .catch(error => console.error('[SW] Activate error:', error))
    );
});

/**
 * Fetch - Stratégie selon type de ressource
 */
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    
    // API : Network First avec cache fallback
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirstStrategy(event.request, API_CACHE_NAME));
        return;
    }
    
    // Assets statiques : Cache First
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirstStrategy(event.request, CACHE_NAME));
        return;
    }
    
    // HTML : Network First
    if (event.request.mode === 'navigate' || event.request.destination === 'document') {
        event.respondWith(networkFirstStrategy(event.request, CACHE_NAME));
        return;
    }
    
    // Par défaut : Network First
    event.respondWith(networkFirstStrategy(event.request, CACHE_NAME));
});

/**
 * Stratégie Network First
 * Essaie réseau, puis cache si échec
 */
async function networkFirstStrategy(request, cacheName) {
    const cache = await caches.open(cacheName);
    
    try {
        const networkResponse = await fetch(request);
        
        // Mettre en cache si succès
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
        
    } catch (error) {
        console.log('[SW] Network failed, trying cache:', request.url);
        
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Fallback pour navigation
        if (request.mode === 'navigate') {
            const fallbackResponse = await cache.match('/');
            if (fallbackResponse) {
                return fallbackResponse;
            }
        }
        
        throw error;
    }
}

/**
 * Stratégie Cache First
 * Utilise cache d'abord, puis réseau si absent
 */
async function cacheFirstStrategy(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
        // Mise à jour cache en arrière-plan
        fetch(request)
            .then(networkResponse => {
                if (networkResponse.ok) {
                    cache.put(request, networkResponse.clone());
                }
            })
            .catch(() => {}); // Ignore les erreurs réseau
        
        return cachedResponse;
    }
    
    // Pas en cache : fetch et cache
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
        
    } catch (error) {
        console.error('[SW] Fetch error:', request.url, error);
        throw error;
    }
}

/**
 * Vérifier si asset statique
 */
function isStaticAsset(pathname) {
    const staticExtensions = ['.css', '.js', '.jpg', '.jpeg', '.png', '.svg', '.webp', '.woff', '.woff2'];
    return staticExtensions.some(ext => pathname.endsWith(ext));
}

/**
 * Messages depuis clients
 */
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames
                        .filter(name => name.startsWith('fcchiche-'))
                        .map(name => caches.delete(name))
                );
            })
        );
    }
});