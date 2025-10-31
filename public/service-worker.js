/**
 * Service Worker - FC Chiche PWA
 * Stratégie : Network First pour API, Cache First pour assets
 */

'use strict';

const CACHE_VERSION = 'v1.1.0';
const CACHE_NAME = `fcchiche-${CACHE_VERSION}`;
const API_CACHE_NAME = `fcchiche-api-${CACHE_VERSION}`;

const STATIC_ASSETS = [
    '/',
    'index.php',
    'matchs.php',
    'calendrier.php',
    'resultats.php',
    'classements.php',
    'classement.php',
    'contact.php',
    'equipes.php',
    'galerie.php',
    'partenaires.php',
    'assets/css/common.css',
    'assets/css/index.css',
    'assets/js/api.js',
    'assets/js/common.js',
    'assets/js/index.js',
    'assets/js/matchs.js',
    'assets/js/resultats.js',
    'assets/js/classements.js',
    'manifest.json'
];

const HTML_ALIASES = new Map([
    ['matchs', 'matchs.php'],
    ['calendrier', 'matchs.php'],
    ['resultats', 'resultats.php'],
    ['classements', 'classements.php'],
    ['classement', 'classements.php'],
    ['contact', 'contact.php'],
    ['equipes', 'equipes.php'],
    ['galerie', 'galerie.php'],
    ['partenaires', 'partenaires.php']
]);

function assert(condition, message) {
    if (!condition) {
        throw new Error(`Assertion failed: ${message}`);
    }
}

function resolveScopeUrl(resourcePath) {
    assert(
        typeof resourcePath === 'string' && resourcePath !== '',
        'Resource path must be a non-empty string'
    );
    const scopeUrl = new URL(self.registration.scope);
    assert(scopeUrl instanceof URL, 'Scope URL must be resolvable');

    if (resourcePath === '/') {
        return scopeUrl.href;
    }

    const normalizedPath = resourcePath.startsWith('/')
        ? resourcePath.slice(1)
        : resourcePath;
    assert(normalizedPath !== '', 'Normalised path must not be empty');

    return new URL(normalizedPath, scopeUrl).href;
}

async function cacheStaticAssets() {
    const cache = await caches.open(CACHE_NAME);
    assert(cache instanceof Cache, 'Cache instance must be available');

    const scopedAssets = STATIC_ASSETS.map(resolveScopeUrl);
    assert(
        scopedAssets.length === STATIC_ASSETS.length,
        'Scoped assets list must match source length'
    );

    await cache.addAll(scopedAssets);
    await self.skipWaiting();
}

async function purgeObsoleteCaches() {
    const cacheNames = await caches.keys();
    assert(Array.isArray(cacheNames), 'Cache keys must return an array');

    const deletions = cacheNames
        .filter(name =>
            name.startsWith('fcchiche-') && name !== CACHE_NAME && name !== API_CACHE_NAME
        )
        .map(name => caches.delete(name));
    assert(Array.isArray(deletions), 'Deletions list must be an array');

    await Promise.all(deletions);
    await self.clients.claim();
}

/**
 * Installation - Cache assets statiques
 */
self.addEventListener('install', event => {
    assert(event !== undefined && event !== null, 'Install event must be defined');
    assert(typeof event.waitUntil === 'function', 'Install event must expose waitUntil');

    console.log('[SW] Installing version', CACHE_VERSION);

    event.waitUntil(
        cacheStaticAssets().catch(error => {
            console.error('[SW] Install error:', error);
            throw error;
        })
    );
});

/**
 * Activation - Nettoyage anciens caches
 */
self.addEventListener('activate', event => {
    assert(event !== undefined && event !== null, 'Activate event must be defined');
    assert(typeof event.waitUntil === 'function', 'Activate event must expose waitUntil');

    console.log('[SW] Activating version', CACHE_VERSION);

    event.waitUntil(
        purgeObsoleteCaches().catch(error => {
            console.error('[SW] Activate error:', error);
            throw error;
        })
    );
});

/**
 * Fetch - Stratégie selon type de ressource
 */
self.addEventListener('fetch', event => {
    assert(event !== undefined && event !== null, 'Fetch event must be defined');
    assert(event.request instanceof Request, 'Fetch event must include a Request instance');

    const url = new URL(event.request.url);
    assert(url instanceof URL, 'Fetch URL must be parseable');

    const scopeUrl = new URL(self.registration.scope);
    assert(scopeUrl instanceof URL, 'Scope URL must be parseable');

    const scopePath = scopeUrl.pathname.endsWith('/') ? scopeUrl.pathname : `${scopeUrl.pathname}/`;
    const requestPath = url.pathname;

    const aliasTarget = resolveHtmlAlias(requestPath, scopePath);
    if (aliasTarget !== null) {
        event.respondWith(serveHtmlAlias(event.request, aliasTarget));
        return;
    }
    
    // API : Network First avec cache fallback
    if (requestPath.startsWith(`${scopePath}api/`)) {
        event.respondWith(networkFirstStrategy(event.request, API_CACHE_NAME));
        return;
    }

    // Assets statiques : Cache First
    if (isStaticAsset(requestPath, scopePath)) {
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
    assert(request instanceof Request, 'Network-first strategy requires a Request');
    assert(
        typeof cacheName === 'string' && cacheName !== '',
        'Cache name must be a non-empty string'
    );

    const cache = await caches.open(cacheName);
    assert(cache instanceof Cache, 'Cache instance must be available');

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
        assert(
            cachedResponse === undefined || cachedResponse instanceof Response,
            'Cached response must be undefined or a Response'
        );

        if (cachedResponse) {
            return cachedResponse;
        }

        // Fallback pour navigation
        if (request.mode === 'navigate') {
            const fallbackResponse = await cache.match(resolveScopeUrl('/'));
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
    assert(request instanceof Request, 'Cache-first strategy requires a Request');
    assert(
        typeof cacheName === 'string' && cacheName !== '',
        'Cache name must be a non-empty string'
    );

    const cache = await caches.open(cacheName);
    assert(cache instanceof Cache, 'Cache instance must be available');

    const cachedResponse = await cache.match(request);
    assert(
        cachedResponse === undefined || cachedResponse instanceof Response,
        'Cached response must be undefined or a Response'
    );

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
function isStaticAsset(pathname, scopePath) {
    assert(typeof pathname === 'string', 'Pathname must be a string');
    assert(typeof scopePath === 'string', 'Scope path must be a string');

    const normalisedPath = pathname.startsWith(scopePath)
        ? pathname.slice(scopePath.length)
        : pathname;
    const staticExtensions = [
        '.css',
        '.js',
        '.jpg',
        '.jpeg',
        '.png',
        '.svg',
        '.webp',
        '.woff',
        '.woff2'
    ];
    return staticExtensions.some(ext => normalisedPath.endsWith(ext));
}

function resolveHtmlAlias(pathname, scopePath) {
    assert(typeof pathname === 'string', 'Pathname must be a string');
    assert(typeof scopePath === 'string', 'Scope path must be a string');

    const relativePath = pathname.startsWith(scopePath)
        ? pathname.slice(scopePath.length)
        : pathname;
    const sanitized = relativePath.replace(/^\/+/, '').replace(/\/+$/, '');
    if (sanitized === '') {
        return null;
    }

    return HTML_ALIASES.get(sanitized) ?? null;
}

async function serveHtmlAlias(originalRequest, aliasTarget) {
    assert(originalRequest instanceof Request, 'Original request must be a Request instance');
    assert(
        typeof aliasTarget === 'string' && aliasTarget !== '',
        'Alias target must be a non-empty string'
    );

    const aliasUrl = resolveScopeUrl(aliasTarget);
    assert(typeof aliasUrl === 'string' && aliasUrl !== '', 'Alias URL must be a non-empty string');

    const method = originalRequest.method || 'GET';
    assert(method === 'GET', 'Alias requests must use the GET method');

    const requestInit = {
        method,
        headers: originalRequest.headers,
        mode: originalRequest.mode,
        credentials: originalRequest.credentials,
        cache: originalRequest.cache,
        redirect: originalRequest.redirect,
        referrer: originalRequest.referrer,
        referrerPolicy: originalRequest.referrerPolicy,
        integrity: originalRequest.integrity
    };

    const aliasRequest = new Request(aliasUrl, requestInit);
    return networkFirstStrategy(aliasRequest, CACHE_NAME);
}

/**
 * Messages depuis clients
 */
self.addEventListener('message', event => {
    assert(event !== undefined && event !== null, 'Message event must be defined');
    assert(
        typeof event.data === 'undefined' || typeof event.data === 'object',
        'Message data must be an object or undefined'
    );

    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        assert(typeof event.waitUntil === 'function', 'Message event must expose waitUntil');
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