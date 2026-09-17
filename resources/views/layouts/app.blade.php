<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', __('ui.default_title'))</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#ea580c">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        .site-menu-toggle {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 24px !important;
            height: 24px !important;
            opacity: 0 !important;
            pointer-events: none !important;
            margin: 0 !important;
        }
        .site-menu {
            display: none !important;
            position: fixed !important;
            inset: 0 !important;
            z-index: 2147483646 !important;
        }
        .site-menu-toggle:checked + .site-menu { display: block !important; }
        .site-menu-overlay {
            position: absolute !important;
            inset: 0 !important;
            background: rgba(15, 23, 42, .42) !important;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .site-menu-panel {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            height: 100% !important;
            width: min(20.5rem, 86vw) !important;
            background: rgba(255,255,255,.92) !important;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-right: 1px solid rgba(226,232,240,.9);
            border-radius: 0 1.75rem 1.75rem 0;
            box-shadow: 16px 0 48px -20px rgba(15,23,42,.35) !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
            padding-bottom: max(1.25rem, env(safe-area-inset-bottom));
        }
        .site-menu-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1rem .9rem;
            border-bottom: 1px solid rgba(226,232,240,.8);
            position: sticky;
            top: 0;
            background: rgba(255,255,255,.88);
            backdrop-filter: blur(12px);
            z-index: 1;
        }
        .site-menu-close {
            display: inline-flex;
            height: 2.5rem;
            width: 2.5rem;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
            cursor: pointer;
        }
        .site-menu-nav { padding: 1rem 1rem 0; }
        .site-menu-kicker {
            margin: 0 0 .5rem;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #64748b;
        }
        .site-menu-list { display: grid; gap: .35rem; }
        .site-menu-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .85rem 1rem;
            border-radius: 1rem;
            font-size: .9rem;
            font-weight: 600;
            color: #0f172a;
            text-decoration: none;
            background: rgba(248,250,252,.8);
            border: 1px solid transparent;
        }
        .site-menu-link:hover { background: #fff7ed; color: #9a3412; }
        .site-menu-link.is-active {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #9a3412;
        }
        .site-menu-acc {
            border-radius: 1rem;
            background: rgba(248,250,252,.8);
            overflow: hidden;
        }
        .site-menu-acc summary {
            list-style: none;
            cursor: pointer;
            padding: .85rem 1rem;
            font-size: .9rem;
            font-weight: 600;
            color: #0f172a;
        }
        .site-menu-acc summary::-webkit-details-marker { display: none; }
        .site-menu-acc-body { border-top: 1px solid #f1f5f9; padding: .35rem; }
        .site-menu-acc-body a {
            display: block;
            padding: .65rem .85rem;
            border-radius: .85rem;
            font-size: .85rem;
            font-weight: 600;
            color: #0f172a;
            text-decoration: none;
        }
        .site-menu-acc-body a.is-child { color: #64748b; padding-left: 1.25rem; }
        .site-menu-empty { margin: 0; padding: .65rem .85rem; font-size: .8rem; color: #64748b; }
        .site-menu-cta {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: .85rem 1rem;
            border-radius: 999px;
            font-size: .9rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            background: linear-gradient(90deg, #6366f1, #22d3ee);
        }
        .site-menu-logout {
            width: 100%;
            margin-top: .15rem;
            padding: .75rem 1rem;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #fff;
            font-size: .85rem;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
        }
        .site-menu-foot { padding: 1rem 1rem 0; }
        .by-dock {
            position: fixed;
            left: .75rem;
            right: .75rem;
            bottom: max(.7rem, env(safe-area-inset-bottom));
            z-index: 50;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .2rem;
            padding: .4rem;
            border-radius: 1.6rem;
            border: 1px solid rgba(226,232,240,.9);
            background: rgba(255,255,255,.88);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 18px 40px -24px rgba(15,23,42,.45);
        }
        .by-dock-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .2rem;
            padding: .55rem .2rem .45rem;
            border-radius: 1.15rem;
            font-size: .65rem;
            font-weight: 700;
            color: #64748b;
            text-decoration: none;
            cursor: pointer;
            background: transparent;
            border: 0;
        }
        .by-dock-item svg { display: block; }
        .by-dock-item.is-active,
        .by-dock-item:active,
        body:has(.site-menu-toggle:checked) .by-dock-menu {
            color: #9a3412;
            background: #fff7ed;
        }
        #mobile-search-sheet {
            left: .75rem;
            right: .75rem;
            bottom: calc(4.75rem + env(safe-area-inset-bottom));
        }
        body:has(.site-menu-toggle:checked) { overflow: hidden; }
        @media (min-width: 768px) { .by-dock { display: none !important; } }
    </style>
</head>
<body class="storefront min-h-screen font-sans">
<input type="checkbox" id="site-menu-toggle" class="site-menu-toggle" autocomplete="off">
<div id="site-menu" class="site-menu" role="dialog" aria-label="{{ __('ui.menu') }}">
    <label for="site-menu-toggle" class="site-menu-overlay" aria-label="{{ __('ui.close_menu') }}"></label>
    <div class="site-menu-panel">
        @include('partials.site-menu-panel')
    </div>
</div>
<div class="min-h-screen flex flex-col">

    <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/70 backdrop-blur">
        <div class="by-container py-3 md:py-4">
            {{-- Mobile: brand (left) | lang + login — menu is in bottom dock --}}
            <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-2 md:hidden">
                <div class="flex min-w-0 items-center justify-start overflow-hidden">
                    @include('partials.platform-brand', ['compact' => true, 'forceName' => true, 'mobileHeader' => true, 'brandHref' => route('home')])
                </div>
                <div class="flex shrink-0 items-center justify-end gap-1.5">
                    @include('partials.locale-switcher', ['compact' => true])
                    @guest
                        <a href="{{ route('login') }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700">{{ __('ui.login') }}</a>
                    @endguest
                </div>
            </div>

            {{-- Desktop --}}
            <div class="hidden items-center justify-between gap-4 md:flex">
                @include('partials.platform-brand', ['compact' => true, 'brandHref' => route('home')])

                <nav class="hidden flex-1 items-center justify-center gap-1 lg:flex">
                    @foreach(\App\Support\SiteMenu::forPlacement('top') as $item)
                        @php $menuHref = \App\Support\SiteMenu::href($item); @endphp
                        @if($menuHref)
                            <a href="{{ $menuHref }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 {{ request()->url() === $menuHref ? 'bg-orange-50 text-orange-900' : '' }}">{{ \App\Support\SiteMenu::displayLabel($item) }}</a>
                        @endif
                    @endforeach
                </nav>

                <form action="{{ route('products.index') }}" class="hidden max-w-xs flex-1 xl:block">
                    <div class="relative">
                        <input class="by-input pl-11" name="q" value="{{ request('q') }}" placeholder="{{ __('ui.search_placeholder') }}" />
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </span>
                    </div>
                </form>

                <div class="flex items-center gap-2">
                    <div class="hidden sm:block">@include('partials.locale-switcher')</div>

                    <label for="site-menu-toggle" class="by-btn-secondary px-4 py-2.5 cursor-pointer" aria-label="{{ __('ui.menu') }}">
                        {{ __('ui.menu') }}
                    </label>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="by-btn-secondary">{{ __('ui.management') }}</a>
                        @elseif(auth()->user()->isVendor())
                            <a href="{{ route('vendor.dashboard') }}" class="by-btn-secondary">{{ __('ui.vendor_short') }}</a>
                        @else
                            <a href="{{ route('customer.dashboard') }}" class="by-btn-secondary">{{ __('ui.account') }}</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="by-btn-secondary">{{ __('ui.login') }}</a>
                    @endauth

                    @auth
                        <a href="{{ route('cart.index') }}" class="by-btn-primary px-4 py-2.5 flex items-center gap-2">
                            <span>{{ __('ui.cart') }}</span>
                            <span class="cart-count-badge rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">
                                {{ auth()->user()->cartItems()->sum('quantity') }}
                            </span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="by-btn-primary px-4 py-2.5 flex items-center gap-2">
                            <span>{{ __('ui.cart') }}</span>
                            <span class="cart-count-badge hidden rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">0</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- Mobile search sheet (opened from bottom nav) --}}
    <div id="mobile-search-sheet" class="fixed z-[90] md:hidden" hidden>
        <form action="{{ route('products.index') }}" class="rounded-2xl border border-slate-200/80 bg-white/95 p-3 shadow-xl backdrop-blur">
            <label class="sr-only" for="mobile-search-q">{{ __('ui.search') }}</label>
            <div class="flex gap-2">
                <input id="mobile-search-q" class="by-input flex-1" name="q" value="{{ request('q') }}" placeholder="{{ __('ui.search_placeholder_short') }}" autocomplete="off" />
                <button type="submit" class="by-btn-primary shrink-0 px-4">{{ __('ui.search') }}</button>
            </div>
        </form>
    </div>

    <main class="flex-1 pb-24 md:pb-0">
        @if(session('success'))
            <div class="by-container pt-4">
                <div class="by-card border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-900">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="by-container pt-4">
                <div class="by-card border-rose-200 bg-rose-50/70 p-4 text-sm text-rose-900">{{ session('error') }}</div>
            </div>
        @endif
        @if(session('info'))
            <div class="by-container pt-4">
                <div class="by-card border-sky-200 bg-sky-50/70 p-4 text-sm text-sky-900">{{ session('info') }}</div>
            </div>
        @endif
        <div class="by-container">@include('partials.validation-errors')</div>
        @yield('content')
    </main>

    <nav class="by-dock md:hidden" aria-label="{{ __('ui.menu') }}">
        <label for="site-menu-toggle" class="by-dock-item by-dock-menu" aria-label="{{ __('ui.menu') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            {{ __('ui.menu') }}
        </label>
        <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="by-dock-item {{ request()->routeIs('cart.*') ? 'is-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            {{ __('ui.cart') }}
        </a>
        <a href="{{ auth()->check() ? (auth()->user()->isVendor() ? route('vendor.dashboard') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('customer.dashboard'))) : route('login') }}" class="by-dock-item {{ request()->routeIs('customer.*','account.*','vendor.*','admin.*') ? 'is-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            {{ __('ui.account') }}
        </a>
        <button type="button" id="mobile-search-toggle" class="by-dock-item" aria-expanded="false" aria-controls="mobile-search-sheet">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            {{ __('ui.search') }}
        </button>
    </nav>

    <footer class="mt-16 border-t border-slate-200/70 bg-white/60 backdrop-blur mb-24 md:mb-0">
        <div class="by-container py-12">
            @unless(request()->routeIs('cart.*', 'checkout.*', 'login', 'register') || auth()->user()?->isVendor() || auth()->user()?->isAdmin())
            <div class="by-card by-gradient-border p-6 md:p-8 mb-10">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('ui.vendor_cta_eyebrow') }}</p>
                        <p class="mt-2 text-xl font-extrabold tracking-tight text-slate-900">
                            {{ __('ui.vendor_cta_title') }}
                        </p>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ __('ui.vendor_cta_body') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('register', ['role' => 'vendor']) }}" class="by-btn-cta px-7 py-3 text-base">{{ __('ui.vendor_cta_primary') }}</a>
                        <a href="{{ route('vendors.index') }}" class="by-btn-secondary px-7 py-3 text-base">{{ __('ui.vendor_cta_secondary') }}</a>
                    </div>
                </div>
            </div>

            @endunless
            <div class="grid gap-8 md:grid-cols-4">
                <div class="md:col-span-2">
                    @include('partials.platform-brand', ['brandHref' => route('home')])
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-600">
                        {{ __('ui.tagline') }}
                    </p>
                    @include('partials.platform-contact')
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('ui.discover') }}</p>
                    <div class="mt-3 space-y-2 text-sm">
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('products.index') }}">{{ __('ui.products') }}</a>
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('vendors.index') }}">{{ __('ui.vendors') }}</a>
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('freelancer-jobs.index') }}">{{ __('ui.jobs') }}</a>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('ui.corporate') }}</p>
                    <div class="mt-3 space-y-2 text-sm">
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('pages.about') }}">{{ __('ui.about') }}</a>
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('pages.contact') }}">{{ __('ui.contact') }}</a>
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('pages.terms') }}">{{ __('ui.terms') }}</a>
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('pages.privacy') }}">{{ __('ui.privacy') }}</a>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-2 border-t border-slate-200 pt-6 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                <p>{{ __('ui.rights', ['year' => date('Y')]) }}</p>
                <p>{{ __('ui.country') }}</p>
            </div>
        </div>
    </footer>
</div>
@include('partials.review-prompt')
@include('partials.pwa-install')
@include('partials.floating-actions')
@include('partials.floating-support')
@include('partials.product-card-scripts')
<style>
#mobile-search-sheet:not([hidden]) { display: block; }
</style>
<script>
(function () {
  var sheet = document.getElementById('mobile-search-sheet');
  var toggle = document.getElementById('mobile-search-toggle');
  var input = document.getElementById('mobile-search-q');
  if (!sheet || !toggle) return;
  toggle.addEventListener('click', function () {
    var open = sheet.hasAttribute('hidden');
    if (open) {
      sheet.removeAttribute('hidden');
      toggle.setAttribute('aria-expanded', 'true');
      toggle.classList.add('is-active');
      setTimeout(function () { input && input.focus(); }, 50);
    } else {
      sheet.setAttribute('hidden', '');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.classList.remove('is-active');
    }
  });
})();
</script>
@stack('scripts')
</body>
</html>
