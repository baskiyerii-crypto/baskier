@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => null,
    'ogType' => 'website',
    'ogImage' => null,
    'jsonLd' => null,
])

@php
    $finalTitle = \App\Support\SeoHelper::title($title);
    $finalDescription = \App\Support\SeoHelper::description($description);
    $finalCanonical = \App\Support\SeoHelper::canonicalUrl($canonical);
    $indexable = \App\Support\SeoHelper::shouldIndex();
    $finalRobots = $robots ?? ($indexable ? 'index, follow' : 'noindex, nofollow');
    $defaultOgImage = url('/icons/icon-512.png');
    $finalOgImage = $ogImage ?? $defaultOgImage;
@endphp

<title>{{ $finalTitle }}</title>
<meta name="description" content="{{ $finalDescription }}">
<meta name="robots" content="{{ $finalRobots }}">
<link rel="canonical" href="{{ $finalCanonical }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="BaskıYeri">
<meta property="og:title" content="{{ $finalTitle }}">
<meta property="og:description" content="{{ $finalDescription }}">
<meta property="og:url" content="{{ $finalCanonical }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:image" content="{{ $finalOgImage }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) == 'en' ? 'en_US' : 'tr_TR' }}">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $finalTitle }}">
<meta name="twitter:description" content="{{ $finalDescription }}">
<meta name="twitter:image" content="{{ $finalOgImage }}">

{{-- JSON-LD Structured Data --}}
<script type="application/ld+json">
{!! json_encode(\App\Support\SeoHelper::organizationJsonLd(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

@if(!empty($jsonLd))
<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
