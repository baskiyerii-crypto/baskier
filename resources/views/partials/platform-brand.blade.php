@php
    $logoUrl = \App\Support\PlatformBranding::logoUrl();
    $logoRel = \App\Support\PlatformBranding::logoRelativePath();
    $siteName = \App\Support\SiteMenu::platformName();
    // Do NOT use $href — parent views leak menu loop $href (e.g. freelancer-jobs).
    $homeUrl = $brandHref ?? route('home');
    $isMobileHeader = (bool) ($mobileHeader ?? false);
    $brandClass = $isMobileHeader
        ? 'inline-flex min-w-0 max-w-full items-center gap-2.5 text-xl leading-tight font-extrabold tracking-tight text-slate-900'
        : (($compact ?? false)
            ? 'inline-flex min-w-0 max-w-full items-center gap-2 text-sm font-extrabold tracking-tight text-slate-900'
            : 'inline-flex items-center gap-2 text-base font-extrabold tracking-tight text-slate-900');
    $logoBox = $isMobileHeader ? 'h-12 w-12 shrink-0' : 'h-9 w-9 shrink-0';
    $showName = ! ($logoOnly ?? false);
    if (($forceName ?? false) === true) {
        $showName = true;
    }
    $version = ($logoRel && is_file(public_path($logoRel))) ? filemtime(public_path($logoRel)) : null;
    $nameClass = $isMobileHeader
        ? 'truncate min-w-0'
        : 'truncate max-w-[9rem] sm:max-w-none';
@endphp
<a href="{{ $homeUrl }}" class="{{ $brandClass }}" aria-label="{{ $siteName }}">
    @if($logoUrl)
        <img src="{{ $logoUrl }}{{ $version ? '?v='.$version : '' }}" alt="{{ $siteName }}" class="{{ $logoBox }} rounded-2xl object-contain bg-white border border-slate-200/80 shadow-sm">
    @else
        <span class="inline-flex {{ $logoBox }} items-center justify-center rounded-xl bg-[#C2410C] text-white shadow-xs text-sm font-bold">{{ mb_strtoupper(mb_substr($siteName, 0, 1)) }}</span>
    @endif
    @if($showName)
        <span class="{{ $nameClass }}">{{ $siteName }}</span>
    @endif
</a>
