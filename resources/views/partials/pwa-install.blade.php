<div id="pwa-install-banner" class="fixed bottom-4 left-4 right-4 z-50 mx-auto hidden max-w-lg rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-slate-900">Uygulamayı ana ekrana ekle</p>
            <p class="text-xs text-slate-500">Daha hızlı erişim için ana ekrana ekleyin. Sildiyseniz buradan tekrar ekleyebilirsiniz.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" id="pwa-dismiss" class="rounded-xl border px-3 py-2 text-xs">Sonra</button>
            <button type="button" id="pwa-install" class="rounded-xl bg-orange-600 px-3 py-2 text-xs font-semibold text-white">Ekle</button>
        </div>
    </div>
</div>
<script>
(() => {
  const DISMISS_KEY = 'pwa_dismiss_until';
  const DISMISS_DAYS = 7;
  const banner = document.getElementById('pwa-install-banner');

  const isStandalone = () =>
    window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;

  const isDismissed = () => {
    try {
      const until = parseInt(localStorage.getItem(DISMISS_KEY) || '0', 10);
      return until > Date.now();
    } catch (_) { return false; }
  };

  const showBanner = () => {
    if (!banner || isStandalone() || isDismissed()) return;
    banner.classList.remove('hidden');
  };

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' })
      .then((reg) => { reg.update().catch(() => {}); })
      .catch(() => {});
  }

  try {
    if (!localStorage.getItem('sw_v3_purged')) {
      caches.keys().then((keys) => Promise.all(keys.map((k) => {
        if (k.startsWith('baskiyeri-shell-') && k !== 'baskiyeri-shell-v3') return caches.delete(k);
      }))).finally(() => localStorage.setItem('sw_v3_purged', '1'));
    }
  } catch (_) {}

  // Clear permanent legacy flag so re-prompt works after upgrade.
  try { localStorage.removeItem('pwa_dismissed'); } catch (_) {}

  if (isStandalone()) {
    banner?.classList.add('hidden');
    return;
  }

  let deferredPrompt = null;
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    showBanner();
  });

  window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    banner?.classList.add('hidden');
  });

  // Safari / already-eligible: still offer soft reminder if not installed.
  setTimeout(() => {
    if (!deferredPrompt) showBanner();
  }, 2500);

  document.getElementById('pwa-install')?.addEventListener('click', async () => {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      await deferredPrompt.userChoice;
      deferredPrompt = null;
      banner?.classList.add('hidden');
      return;
    }
    // Fallback hint when browser has no install prompt (e.g. iOS Safari).
    alert('Tarayıcı menüsünden «Ana Ekrana Ekle» / «Add to Home Screen» seçin.');
  });

  document.getElementById('pwa-dismiss')?.addEventListener('click', () => {
    try {
      localStorage.setItem(DISMISS_KEY, String(Date.now() + DISMISS_DAYS * 86400000));
    } catch (_) {}
    banner?.classList.add('hidden');
  });
})();
</script>
