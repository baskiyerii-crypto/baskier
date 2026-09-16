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
</head>
<body class="storefront min-h-screen font-sans">
<div class="min-h-screen flex flex-col">
    <div data-left-drawer id="site-menu" inert aria-hidden="true" class="fixed inset-0 z-60 pointer-events-none">
        <div data-left-drawer-overlay class="absolute inset-0 bg-slate-900/40 opacity-0 transition-opacity duration-200"></div>
        <div data-left-drawer-panel role="dialog" aria-modal="true" aria-label="Menü" tabindex="-1" class="absolute left-0 top-0 h-full w-85 max-w-[88vw] -translate-x-full transition-transform duration-200">
            <div class="h-full bg-white/95 backdrop-blur border-r border-slate-200 shadow-xl">
                <div class="p-4 border-b border-slate-200/70 flex items-center justify-between gap-2">
                    @include('partials.platform-brand', ['compact' => true])
                    <button data-left-drawer-close class="by-btn-secondary px-3 py-2" type="button" aria-label="{{ __('ui.close_menu') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <div class="p-4">
                    <form action="{{ route('products.index') }}">
                        <div class="relative">
                            <input class="by-input pl-11" name="q" value="{{ request('q') }}" placeholder="{{ __('ui.search_drawer') }}" />
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                        </div>
                    </form>
                </div>

                <div class="px-4 pb-4">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('ui.menu') }}</p>
                    <div class="mt-2 grid gap-2">
                        @foreach(\App\Support\SiteMenu::forPlacement('drawer') as $item)
                            @if(($item['type'] ?? '') === 'categories_accordion')
                                <details class="by-card overflow-hidden">
                                    <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-slate-900">{{ \App\Support\SiteMenu::displayLabel($item) }}</summary>
                                    <div class="border-t border-slate-100 px-2 py-2 max-h-[40vh] overflow-auto">
                                        @if(!empty($headerCategories) && $headerCategories->isNotEmpty())
                                            @foreach($headerCategories as $parentCategory)
                                                <a class="block rounded-xl px-3 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50" href="{{ route('products.index', ['category_id' => $parentCategory->id]) }}">{{ $parentCategory->localizedName() }}</a>
                                                @foreach($parentCategory->children as $childCategory)
                                                    <a class="ml-3 block rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-slate-50" href="{{ route('products.index', ['category_id' => $childCategory->id]) }}">{{ $childCategory->localizedName() }}</a>
                                                @endforeach
                                            @endforeach
                                        @else
                                            <p class="px-2 py-2 text-sm text-slate-500">{{ __('ui.no_categories') }}</p>
                                        @endif
                                    </div>
                                </details>
                            @else
                                @php $href = \App\Support\SiteMenu::href($item); @endphp
                                @if($href)
                                    <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ $href }}">{{ \App\Support\SiteMenu::displayLabel($item) }}</a>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="px-4 pb-4">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('ui.account') }}</p>
                    <div class="mt-2 grid gap-2">
                        @auth
                            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('cart.index') }}">{{ __('ui.cart') }}</a>
                            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('account.orders.index') }}">{{ __('ui.orders') }}</a>
                            @if(auth()->user()->isAdmin())
                                <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('admin.dashboard') }}">{{ __('ui.admin') }}</a>
                            @endif
                            @if(auth()->user()->isVendor())
                                <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('vendor.dashboard') }}">{{ __('ui.vendor_panel') }}</a>
                            @endif
                            @if(auth()->user()->isCustomer())
                                <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('customer.dashboard') }}">{{ __('ui.account') }}</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full by-btn-secondary">{{ __('ui.logout') }}</button>
                            </form>
                        @else
                            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('login') }}">{{ __('ui.login_full') }}</a>
                            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('register') }}">{{ __('ui.register') }}</a>
                        @endauth
                    </div>
                </div>

                <div class="px-4 pb-4">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('ui.language') }}</p>
                    <div class="mt-2">
                        @include('partials.locale-switcher')
                    </div>
                </div>

                <div class="px-4 pb-6">
                    {{-- categories also via menu accordion; keep nothing duplicate if accordion used --}}
                </div>
            </div>
        </div>
    </div>

    <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/70 backdrop-blur">
        <div class="by-container py-3 md:py-4">
            {{-- Mobile: menu | brand | cart (lang in drawer) --}}
            <div class="grid grid-cols-[2.5rem_minmax(0,1fr)_auto] items-center gap-2 md:hidden">
                <button type="button" data-left-drawer-open class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700" aria-label="{{ __('ui.menu') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="flex min-w-0 items-center justify-center overflow-hidden px-1">
                    @include('partials.platform-brand', ['compact' => true, 'forceName' => true, 'mobileHeader' => true])
                </div>
                <div class="flex shrink-0 items-center justify-end gap-1.5">
                    @auth
                        <a href="{{ route('cart.index') }}" class="inline-flex h-11 items-center rounded-xl bg-orange-600 px-3 text-xs font-bold text-white">
                            {{ __('ui.cart') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex h-11 items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700">{{ __('ui.login') }}</a>
                    @endauth
                </div>
            </div>

            {{-- Desktop --}}
            <div class="hidden items-center justify-between gap-4 md:flex">
                @include('partials.platform-brand', ['compact' => true])

                <nav class="hidden flex-1 items-center justify-center gap-1 lg:flex">
                    @foreach(\App\Support\SiteMenu::forPlacement('top') as $item)
                        @php $href = \App\Support\SiteMenu::href($item); @endphp
                        @if($href)
                            <a href="{{ $href }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 {{ request()->url() === $href ? 'bg-orange-50 text-orange-900' : '' }}">{{ \App\Support\SiteMenu::displayLabel($item) }}</a>
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

                    <button type="button" data-left-drawer-open aria-controls="site-menu" aria-expanded="false" class="by-btn-secondary px-4 py-2.5" aria-label="{{ __('ui.menu') }}">
                        {{ __('ui.menu') }}
                    </button>

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
                        <a href="{{ route('cart.index') }}" class="by-btn-primary px-4 py-2.5">
                            {{ __('ui.cart') }}
                            <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">
                                {{ auth()->user()->cartItems()->sum('quantity') }}
                            </span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="by-btn-primary px-4 py-2.5">{{ __('ui.cart') }}</a>
                    @endauth
                </div>
            </div>

            <form action="{{ route('products.index') }}" class="mt-3 md:hidden">
                <input class="by-input" name="q" value="{{ request('q') }}" placeholder="{{ __('ui.search_placeholder_short') }}" />
            </form>
        </div>
    </header>

    <main class="flex-1 pb-20 md:pb-0">
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

    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 backdrop-blur md:hidden" aria-label="{{ __('ui.menu') }}">
        <div class="grid grid-cols-4 gap-1 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2">
            <button type="button" data-left-drawer-open class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[11px] font-semibold text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                {{ __('ui.menu') }}
            </button>
            <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[11px] font-semibold {{ request()->routeIs('cart.*') ? 'bg-orange-50 text-orange-800' : 'text-slate-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                {{ __('ui.cart') }}
            </a>
            <a href="{{ auth()->check() ? (auth()->user()->isVendor() ? route('vendor.dashboard') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('customer.dashboard'))) : route('login') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[11px] font-semibold {{ request()->routeIs('customer.*','account.*','vendor.*','admin.*') ? 'bg-orange-50 text-orange-800' : 'text-slate-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {{ __('ui.account') }}
            </a>
            <a href="{{ route('pages.contact') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[11px] font-semibold {{ request()->routeIs('pages.contact') ? 'bg-orange-50 text-orange-800' : 'text-slate-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                {{ __('ui.contact') }}
            </a>
        </div>
    </nav>

    <footer class="mt-16 border-t border-slate-200/70 bg-white/60 backdrop-blur mb-16 md:mb-0">
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
                    @include('partials.platform-brand')
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
<style>
/* Vite build yoksa da drawer çalışsın */
[data-left-drawer] { pointer-events: none; }
[data-left-drawer].is-open { pointer-events: auto; }
[data-left-drawer].is-open [data-left-drawer-overlay] { opacity: 1; }
[data-left-drawer].is-open [data-left-drawer-panel] { transform: translateX(0); }
</style>
<script>
(() => {
  const drawer = document.querySelector('[data-left-drawer]');
  if (!drawer || drawer.dataset.drawerBound === '1') return;
  drawer.dataset.drawerBound = '1';
  const panel = drawer.querySelector('[data-left-drawer-panel]');
  const overlay = drawer.querySelector('[data-left-drawer-overlay]');
  const setOpen = (open) => {
    drawer.classList.toggle('is-open', open);
    document.documentElement.classList.toggle('overflow-hidden', open);
    document.body.classList.toggle('overflow-hidden', open);
  };
  document.querySelectorAll('[data-left-drawer-open]').forEach((btn) => {
    btn.addEventListener('click', (e) => { e.preventDefault(); setOpen(true); });
  });
  drawer.querySelectorAll('[data-left-drawer-close]').forEach((btn) => {
    btn.addEventListener('click', (e) => { e.preventDefault(); setOpen(false); });
  });
  overlay?.addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
  panel?.addEventListener('click', (e) => { if (e.target && e.target.tagName === 'A') setOpen(false); });
})();
</script>
@stack('scripts')
</body>
</html>
