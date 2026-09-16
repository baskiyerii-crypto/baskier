<div id="pwa-install-banner" class="fixed bottom-4 left-4 right-4 z-50 mx-auto hidden max-w-lg rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-slate-900">Uygulamayı ana ekrana ekle</p>
            <p class="text-xs text-slate-500">Daha hızlı erişim ve bildirimler için PWA olarak kurun.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" id="pwa-dismiss" class="rounded-xl border px-3 py-2 text-xs">Sonra</button>
            <button type="button" id="pwa-install" class="rounded-xl bg-orange-600 px-3 py-2 text-xs font-semibold text-white">Ekle</button>
        </div>
    </div>
</div>
<script>
(() => {
  if (!('serviceWorker' in navigator)) return;

  // Force update + drop legacy caches that may hold Traefik 503 HTML.
  navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' })
    .then((reg) => {
      reg.update().catch(() => {});
      if (reg.waiting) reg.waiting.postMessage({ type: 'SKIP_WAITING' });
    })
    .catch(() => {});

  // One-time mobile recovery: purge old shell caches after SW v3 deploy.
  try {
    if (!localStorage.getItem('sw_v3_purged')) {
      caches.keys().then((keys) => Promise.all(keys.map((k) => {
        if (k.startsWith('baskiyeri-shell-') && k !== 'baskiyeri-shell-v3') {
          return caches.delete(k);
        }
      }))).finally(() => localStorage.setItem('sw_v3_purged', '1'));
    }
  } catch (_) {}

  let deferredPrompt = null;
  const banner = document.getElementById('pwa-install-banner');
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    if (banner && !localStorage.getItem('pwa_dismissed')) banner.classList.remove('hidden');
  });
  document.getElementById('pwa-install')?.addEventListener('click', async () => {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    await deferredPrompt.userChoice;
    deferredPrompt = null;
    banner?.classList.add('hidden');
  });
  document.getElementById('pwa-dismiss')?.addEventListener('click', () => {
    localStorage.setItem('pwa_dismissed', '1');
    banner?.classList.add('hidden');
  });
})();
</script>
