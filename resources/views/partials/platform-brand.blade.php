@php
    $logoUrl = \App\Support\PlatformBranding::logoUrl();
    $logoRel = \App\Support\PlatformBranding::logoRelativePath();
    $siteName = \App\Support\SiteMenu::platformName();
    $brandClass = $compact ?? false
        ? 'inline-flex items-center gap-2 text-sm font-extrabold tracking-tight text-slate-900'
        : 'inline-flex items-center gap-2 text-base font-extrabold tracking-tight text-slate-900';
    $logoBox = 'h-9 w-9';
    $showName = ! ($logoOnly ?? false);
    // Mobile header used logoOnly before — always show short name unless explicitly hidden.
    if (($forceName ?? false) === true) {
        $showName = true;
    }
    $version = ($logoRel && is_file(public_path($logoRel))) ? filemtime(public_path($logoRel)) : null;
@endphp
<a href="{{ $href ?? route('home') }}" class="{{ $brandClass }}">
    @if($logoUrl)
        <img src="{{ $logoUrl }}{{ $version ? '?v='.$version : '' }}" alt="{{ $siteName }}" class="{{ $logoBox }} rounded-2xl object-contain bg-white border border-slate-200/80 shadow-sm">
    @else
        <span class="inline-flex {{ $logoBox }} items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-sm text-xs font-bold">{{ mb_strtoupper(mb_substr($siteName, 0, 1)) }}</span>
    @endif
    @if($showName)
        <span class="truncate max-w-[9rem] sm:max-w-none">{{ $siteName }}</span>
    @endif
</a>
