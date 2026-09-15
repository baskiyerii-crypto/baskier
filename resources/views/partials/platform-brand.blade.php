@php
    $platformLogo = \App\Models\Setting::get('platform_logo');
    $brandClass = $compact ?? false
        ? 'flex items-center gap-2 text-sm font-extrabold tracking-tight text-slate-900'
        : 'flex items-center gap-2 text-base font-extrabold tracking-tight text-slate-900';
    $logoBox = $compact ?? false ? 'h-9 w-9' : 'h-9 w-9';
@endphp
<a href="{{ $href ?? route('home') }}" class="{{ $brandClass }}">
    @if($platformLogo)
        <img src="{{ asset('storage/'.$platformLogo) }}" alt="BaskıYeri" class="{{ $logoBox }} rounded-2xl object-contain bg-white border border-slate-200/80 shadow-sm">
    @else
        <span class="inline-flex {{ $logoBox }} items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-sm">B</span>
    @endif
    <span>BaskıYeri</span>
</a>
