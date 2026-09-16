{{-- Reusable scroll-gate legal acceptance. Pass $slug, $label, $field, $contractHtml --}}
@php
    $field = $field ?? 'accept_'.$slug;
    $modalId = 'legal-modal-'.$slug;
    $bodyId = 'legal-body-'.$slug;
    $confirmId = 'legal-confirm-'.$slug;
    $openId = 'legal-open-'.$slug;
    $scrolledId = 'legal-scrolled-'.$slug;
    $checkId = 'legal-check-'.$slug;
@endphp
<div class="space-y-1" data-legal-gate data-slug="{{ $slug }}">
    <div class="flex items-start gap-2">
        <input class="mt-1 h-4 w-4" type="checkbox" id="{{ $checkId }}" name="{{ $field }}" value="1" disabled required @checked(old($field))>
        <label for="{{ $checkId }}" class="text-sm text-slate-700">
            <span class="font-semibold">{{ $label }}</span> — {{ __('ui.legal_must_scroll') }}
            <button type="button" class="by-link ml-1 text-sm" id="{{ $openId }}">{{ __('ui.legal_open') }}</button>
        </label>
    </div>
    <input type="hidden" name="{{ $field }}_scrolled_at" id="{{ $scrolledId }}" value="{{ old($field.'_scrolled_at') }}">
</div>
<div id="{{ $modalId }}" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h3 class="text-sm font-bold text-slate-900">{{ $label }}</h3>
            <button type="button" class="by-btn-secondary px-3 py-1 text-xs" data-legal-close="{{ $slug }}">{{ __('ui.close') }}</button>
        </div>
        <div class="flex-1 overflow-auto p-4 text-sm text-slate-700" id="{{ $bodyId }}" style="max-height:55vh;">
            {!! $contractHtml ?? '<p>'.e(__('ui.legal_missing')).'</p>' !!}
        </div>
        <div class="border-t p-3">
            <button type="button" class="by-btn-primary w-full text-sm" id="{{ $confirmId }}" disabled>{{ __('ui.legal_confirm') }}</button>
        </div>
    </div>
</div>
<script>
(() => {
  const slug = @json($slug);
  const modal = document.getElementById('legal-modal-' + slug);
  const body = document.getElementById('legal-body-' + slug);
  const openBtn = document.getElementById('legal-open-' + slug);
  const confirmBtn = document.getElementById('legal-confirm-' + slug);
  const checkbox = document.getElementById('legal-check-' + slug);
  const scrolledAt = document.getElementById('legal-scrolled-' + slug);
  let scrolled = false;
  const show = () => { if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); } };
  const hide = () => { if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); } };
  openBtn?.addEventListener('click', (e) => { e.preventDefault(); show(); });
  document.querySelectorAll('[data-legal-close="' + slug + '"]').forEach((b) => b.addEventListener('click', hide));
  body?.addEventListener('scroll', () => {
    if (body.scrollTop + body.clientHeight >= body.scrollHeight - 8) {
      scrolled = true;
      confirmBtn && (confirmBtn.disabled = false);
      if (scrolledAt && !scrolledAt.value) scrolledAt.value = new Date().toISOString();
    }
  });
  confirmBtn?.addEventListener('click', () => {
    if (!scrolled) return;
    if (checkbox) { checkbox.disabled = false; checkbox.checked = true; }
    hide();
  });
  checkbox?.addEventListener('click', (e) => {
    if (!checkbox.checked && !scrolled) {
      e.preventDefault();
      show();
    }
  });
})();
</script>
