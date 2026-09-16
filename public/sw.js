// Do not cache account HTML: it can contain session-specific information and
// references to build assets which change on deployment.
const CACHE = 'baskiyeri-shell-v2';
const ASSETS = ['/manifest.webmanifest'];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(ASSETS)));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((key) => key.startsWith('baskiyeri-shell-') && key !== CACHE).map((key) => caches.delete(key)));
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  if (event.request.mode === 'navigate') {
    event.respondWith(fetch(event.request).catch(() => new Response(
      '<!doctype html><html lang="tr"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Bağlantı kurulamadı</title><body style="font-family:system-ui;max-width:32rem;margin:15vh auto;padding:1.5rem"><h1>İnternet bağlantınızı kontrol edin</h1><p>Güncel fiyatları ve hesap bilgilerinizi göstermek için bağlantı gerekiyor.</p><a href="">Yeniden dene</a></body></html>',
      { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    )));
  }
});
