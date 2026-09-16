/* v3: never cache error/503 pages; network-first for HTML */
const CACHE = 'baskiyeri-shell-v3';
const ASSETS = ['/manifest.webmanifest'];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE).then((cache) => cache.addAll(ASSETS).catch(() => undefined))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      const keys = await caches.keys();
      await Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)));
      await self.clients.claim();
    })()
  );
});

function shouldCache(response) {
  return response && response.ok && response.type === 'basic';
}

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  let url;
  try {
    url = new URL(event.request.url);
  } catch {
    return;
  }
  if (url.origin !== self.location.origin) return;

  const isNavigate = event.request.mode === 'navigate'
    || (event.request.headers.get('accept') || '').includes('text/html');

  if (isNavigate) {
    event.respondWith(
      fetch(event.request)
        .then((res) => {
          if (shouldCache(res)) {
            const copy = res.clone();
            caches.open(CACHE).then((cache) => cache.put(event.request, copy)).catch(() => {});
          }
          return res;
        })
        .catch(async () => {
          const cached = await caches.match(event.request);
          if (cached && cached.ok) return cached;
          return new Response(
            '<!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1"><title>Bağlantı yok</title><body style="font-family:sans-serif;padding:2rem"><h1>Sunucuya ulaşılamıyor</h1><p>Wi‑Fi / mobil veriyi deneyin veya site verilerini temizleyip yenileyin.</p></body>',
            { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
          );
        })
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cached) => {
      const fetched = fetch(event.request)
        .then((res) => {
          if (shouldCache(res)) {
            const copy = res.clone();
            caches.open(CACHE).then((cache) => cache.put(event.request, copy)).catch(() => {});
          }
          return res;
        })
        .catch(() => cached);
      return cached || fetched;
    })
  );
});
