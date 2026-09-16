@php
    $currentLocale = app()->getLocale();
    $compact = (bool) ($compact ?? false);
    $pad = $compact ? 'px-2 py-1' : 'px-3 py-1.5';
@endphp
<div class="inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-white/70 p-0.5" role="group" aria-label="{{ __('ui.language') }}">
    <a
        href="{{ route('locale.switch', ['locale' => 'tr']) }}"
        class="{{ $currentLocale === 'tr' ? 'rounded-full bg-slate-900 '.$pad.' text-xs font-bold text-white' : 'rounded-full '.$pad.' text-xs font-semibold text-slate-600 hover:text-slate-900' }}"
        @if($currentLocale === 'tr') aria-current="true" @endif
    >TR</a>
    <a
        href="{{ route('locale.switch', ['locale' => 'en']) }}"
        class="{{ $currentLocale === 'en' ? 'rounded-full bg-slate-900 '.$pad.' text-xs font-bold text-white' : 'rounded-full '.$pad.' text-xs font-semibold text-slate-600 hover:text-slate-900' }}"
        @if($currentLocale === 'en') aria-current="true" @endif
    >EN</a>
</div>
