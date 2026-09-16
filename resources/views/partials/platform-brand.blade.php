@php
    $logoUrl = \App\Support\PlatformBranding::logoUrl();
    $logoRel = \App\Support\PlatformBranding::logoRelativePath();
    $brandClass = $compact ?? false
        ? 'inline-flex items-center gap-2 text-sm font-extrabold tracking-tight text-slate-900'
        : 'inline-flex items-center gap-2 text-base font-extrabold tracking-tight text-slate-900';
    $logoBox = 'h-9 w-9';
    $showName = !($logoOnly ?? false);
    $version = ($logoRel && is_file(public_path($logoRel))) ? filemtime(public_path($logoRel)) : null;
@endphp
<a href="{{ $href ?? route('home') }}" class="{{ $brandClass }}">
    @if($logoUrl)
        <img src="{{ $logoUrl }}{{ $version ? '?v='.$version : '' }}" alt="BaskıYeri" class="{{ $logoBox }} rounded-2xl object-contain bg-white border border-slate-200/80 shadow-sm">
    @else
        <span class="inline-flex {{ $logoBox }} items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-sm">B</span>
    @endif
    @if($showName)
        <span @class(['hidden sm:inline' => ($hideNameOnMobile ?? false)])>BaskıYeri</span>
    @endif
</a>
