@php
    $supportPage = request()->path();
    $supportUrl = auth()->check()
        ? route('account.support.create', ['page' => $supportPage])
        : route('login', ['redirect' => route('account.support.create')]);
@endphp
<div id="floating-support" class="fixed bottom-20 right-4 z-[60] sm:bottom-6 sm:right-6">
    <button type="button" id="floating-support-toggle" class="flex h-14 w-14 items-center justify-center rounded-full bg-orange-600 text-white shadow-lg ring-4 ring-orange-100 transition hover:bg-orange-700" aria-label="{{ __('ui.support') }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
    </button>
    <div id="floating-support-panel" class="mb-3 hidden w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl sm:w-80">
        <div class="border-b border-slate-100 bg-slate-50 px-4 py-3">
            <p class="text-sm font-bold text-slate-900">{{ __('ui.support_title') }}</p>
            <p class="mt-0.5 text-xs text-slate-500">{{ __('ui.support_hint') }}</p>
        </div>
        <form method="POST" action="{{ auth()->check() ? route('account.support.store') : route('login') }}" class="space-y-3 p-4">
            @csrf
            <input type="hidden" name="page_url" value="{{ url()->current() }}">
            <input type="hidden" name="page_path" value="{{ $supportPage }}">
            <div>
                <label class="text-xs font-semibold text-slate-600">{{ __('ui.support_subject') }}</label>
                <input type="text" name="subject" class="by-input mt-1 w-full text-sm" maxlength="255" required placeholder="{{ __('ui.support_subject_ph') }}" @disabled(!auth()->check())>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600">{{ __('ui.support_body') }}</label>
                <textarea name="body" rows="3" class="by-input mt-1 w-full text-sm" maxlength="5000" required placeholder="{{ __('ui.support_body_ph') }}" @disabled(!auth()->check())></textarea>
            </div>
            @if(auth()->check())
                <button type="submit" class="by-btn-primary w-full text-sm">{{ __('ui.support_send') }}</button>
            @else
                <a href="{{ $supportUrl }}" class="by-btn-primary block w-full text-center text-sm">{{ __('ui.support_login') }}</a>
            @endif
        </form>
    </div>
</div>
<script>
(() => {
  const toggle = document.getElementById('floating-support-toggle');
  const panel = document.getElementById('floating-support-panel');
  if (!toggle || !panel) return;
  toggle.addEventListener('click', () => panel.classList.toggle('hidden'));
})();
</script>
