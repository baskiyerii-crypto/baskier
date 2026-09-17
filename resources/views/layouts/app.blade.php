<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#C2410C">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <x-seo-head 
        :title="$__env->yieldContent('title')" 
        :description="$__env->yieldContent('meta_description')" 
        :canonical="$__env->yieldContent('canonical')"
    />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('head')
</head>
<body class="storefront min-h-screen font-sans bg-[#F7F5F0] text-[#182023]">

    {{-- Mobile Navigation Drawer --}}
    <x-drawer id="site-mobile-drawer" title="Menü" side="left">
        @include('partials.site-menu-panel')
    </x-drawer>

    <div class="min-h-screen flex flex-col">
        {{-- Global Header --}}
        <header class="sticky top-0 z-40 border-b border-[#DEDAD2] bg-white/95 backdrop-blur-md">
            <div class="by-container py-3">
                {{-- Mobile Header Row --}}
                <div class="flex items-center justify-between gap-3 md:hidden">
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-[#DEDAD2] bg-white text-[#182023] hover:bg-[#F7F5F0] transition" data-drawer-open="site-mobile-drawer" aria-label="{{ __('ui.menu') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>

                    <div class="flex min-w-0 items-center justify-center">
                        @include('partials.platform-brand', ['compact' => true, 'forceName' => true, 'brandHref' => route('home')])
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-[#DEDAD2] bg-white text-[#182023] hover:bg-[#F7F5F0] transition" data-modal-open="mobile-search-modal" aria-label="{{ __('ui.search') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                        <a href="{{ route('cart.index') }}" class="relative inline-flex h-11 w-11 items-center justify-center rounded-lg bg-[#C2410C] text-white transition hover:bg-[#9A3412]" aria-label="{{ __('ui.cart') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            @auth
                                @php $cartCount = auth()->user()->cartItems()->sum('quantity'); @endphp
                                @if($cartCount > 0)
                                    <span class="cart-count-badge absolute -top-1.5 -right-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#182023] px-1 text-[10px] font-bold text-white border border-white">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            @endauth
                        </a>
                    </div>
                </div>

                {{-- Desktop Header --}}
                <div class="hidden items-center justify-between gap-6 md:flex">
                    <div class="shrink-0">
                        @include('partials.platform-brand', ['compact' => false, 'brandHref' => route('home')])
                    </div>

                    {{-- Search Form (Center-Left) --}}
                    <form action="{{ route('products.index') }}" method="GET" class="flex-1 max-w-md">
                        <div class="relative">
                            <input 
                                type="search" 
                                name="q" 
                                value="{{ request('q') }}" 
                                placeholder="{{ __('ui.search_placeholder') }}" 
                                class="w-full min-h-[44px] rounded-lg border border-[#DEDAD2] bg-[#F7F5F0]/60 pl-10 pr-4 py-2 text-sm text-[#182023] placeholder-[#596166] transition focus:bg-white focus:border-[#C2410C] focus:ring-2 focus:ring-[#C2410C]/20 outline-none"
                            />
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[#596166]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                        </div>
                    </form>

                    {{-- Primary Nav Links --}}
                    <nav class="flex items-center gap-1.5 text-sm font-semibold">
                        <a href="{{ route('products.index') }}" class="rounded-lg px-3 py-2 transition {{ request()->routeIs('products.*') ? 'bg-[#F7F5F0] text-[#C2410C]' : 'text-[#182023] hover:bg-[#F7F5F0]' }}">
                            {{ __('ui.products') }}
                        </a>
                        <a href="{{ route('service-requests.index') }}" class="rounded-lg px-3 py-2 transition {{ request()->routeIs('service-requests.*') ? 'bg-[#F7F5F0] text-[#C2410C]' : 'text-[#182023] hover:bg-[#F7F5F0]' }}">
                            Hizmet Talepleri
                        </a>
                        <a href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}" class="rounded-lg px-3 py-2 text-[#596166] hover:text-[#182023] hover:bg-[#F7F5F0] transition">
                            Özel Teklif Al
                        </a>
                        <a href="{{ route('vendors.index') }}" class="rounded-lg px-3 py-2 text-[#596166] hover:text-[#182023] hover:bg-[#F7F5F0] transition">
                            {{ __('ui.vendors') }}
                        </a>
                    </nav>

                    {{-- Actions: Language, Account, Cart --}}
                    <div class="flex items-center gap-2.5 shrink-0">
                        @include('partials.locale-switcher', ['compact' => true])

                        @auth
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 rounded-lg border border-[#DEDAD2] bg-white px-3.5 text-xs font-semibold text-[#182023] hover:bg-[#F7F5F0] transition">
                                    {{ __('ui.management') }}
                                </a>
                            @elseif(auth()->user()->isVendor())
                                <a href="{{ route('vendor.dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 rounded-lg border border-[#DEDAD2] bg-white px-3.5 text-xs font-semibold text-[#182023] hover:bg-[#F7F5F0] transition">
                                    {{ __('ui.vendor_short') }}
                                </a>
                            @else
                                <a href="{{ route('customer.dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 rounded-lg border border-[#DEDAD2] bg-white px-3.5 text-xs font-semibold text-[#182023] hover:bg-[#F7F5F0] transition">
                                    {{ __('ui.account') }}
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center rounded-lg border border-[#DEDAD2] bg-white px-4 text-xs font-semibold text-[#182023] hover:bg-[#F7F5F0] transition">
                                {{ __('ui.login') }}
                            </a>
                        @endauth

                        <a href="{{ route('cart.index') }}" class="inline-flex min-h-[44px] items-center gap-2 rounded-lg bg-[#C2410C] px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-[#9A3412] transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            <span>{{ __('ui.cart') }}</span>
                            @auth
                                @php $cartCount = auth()->user()->cartItems()->sum('quantity'); @endphp
                                @if($cartCount > 0)
                                    <span class="cart-count-badge rounded-full bg-white/20 px-1.5 py-0.5 text-[11px] font-bold text-white">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            @endauth
                        </a>
                    </div>
                </div>
            </div>
        </header>

        {{-- Mobile Search Modal --}}
        <x-modal id="mobile-search-modal" title="Ürün veya Hizmet Ara" maxWidth="max-w-md">
            <form action="{{ route('products.index') }}" method="GET" class="space-y-3">
                <x-input 
                    name="q" 
                    type="search" 
                    placeholder="Kartvizit, tabela, broşür, etiket..." 
                    value="{{ request('q') }}" 
                    required 
                    autofocus
                />
                <div class="flex items-center justify-end gap-2">
                    <x-button type="button" variant="secondary" size="sm" data-modal-close>Vazgeç</x-button>
                    <x-button type="submit" variant="primary" size="sm">Ara</x-button>
                </div>
            </form>
        </x-modal>

        {{-- Main Content Shell --}}
        <main class="flex-1 pb-24 md:pb-0">
            {{-- Flash Messages --}}
            <div class="by-container pt-4 space-y-3">
                @if(session('success'))
                    <x-alert type="success">{{ session('success') }}</x-alert>
                @endif
                @if(session('error'))
                    <x-alert type="error">{{ session('error') }}</x-alert>
                @endif
                @if(session('info'))
                    <x-alert type="info">{{ session('info') }}</x-alert>
                @endif
                @include('partials.validation-errors')
            </div>

            @yield('content')
        </main>

        {{-- Mobile Bottom Navigation Dock (Fixed) --}}
        <nav class="fixed bottom-0 inset-x-0 z-30 grid grid-cols-5 border-t border-[#DEDAD2] bg-white/95 backdrop-blur-md py-1.5 md:hidden" aria-label="Mobil Gezinme">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center py-1 text-[11px] font-semibold {{ request()->routeIs('home') ? 'text-[#C2410C]' : 'text-[#596166]' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span class="mt-0.5">Ana Sayfa</span>
            </a>
            <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center py-1 text-[11px] font-semibold {{ request()->routeIs('products.*') ? 'text-[#C2410C]' : 'text-[#596166]' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                <span class="mt-0.5">Ürünler</span>
            </a>
            <a href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}" class="flex flex-col items-center justify-center py-1 text-[11px] font-semibold {{ request()->routeIs('quote-requests.*') ? 'text-[#C2410C]' : 'text-[#596166]' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                <span class="mt-0.5">Teklif Al</span>
            </a>
            <a href="{{ route('cart.index') }}" class="relative flex flex-col items-center justify-center py-1 text-[11px] font-semibold {{ request()->routeIs('cart.*') ? 'text-[#C2410C]' : 'text-[#596166]' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span class="mt-0.5">Sepet</span>
                @auth
                    @php $cartCount = auth()->user()->cartItems()->sum('quantity'); @endphp
                    @if($cartCount > 0)
                        <span class="absolute top-1 right-3 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#C2410C] px-1 text-[9px] font-bold text-white">
                            {{ $cartCount }}
                        </span>
                    @endif
                @endauth
            </a>
            <a href="{{ auth()->check() ? (auth()->user()->isVendor() ? route('vendor.dashboard') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('customer.dashboard'))) : route('login') }}" class="flex flex-col items-center justify-center py-1 text-[11px] font-semibold {{ request()->routeIs('customer.*', 'account.*', 'vendor.*', 'admin.*', 'login') ? 'text-[#C2410C]' : 'text-[#596166]' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="mt-0.5">Hesabım</span>
            </a>
        </nav>

        {{-- Footer --}}
        <footer class="mt-16 border-t border-[#DEDAD2] bg-white text-[#182023] mb-16 md:mb-0">
            <div class="by-container py-12">
                {{-- Vendor Registration CTA Banner (Only for guests/customers) --}}
                @unless(request()->routeIs('cart.*', 'checkout.*', 'login', 'register') || auth()->user()?->isVendor() || auth()->user()?->isAdmin())
                    <div class="rounded-xl border border-[#DEDAD2] bg-[#F7F5F0] p-6 sm:p-8 mb-10">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span class="inline-block text-[11px] font-bold uppercase tracking-wider text-[#C2410C] mb-1">
                                    {{ __('ui.vendor_cta_eyebrow') }}
                                </span>
                                <h3 class="text-xl font-bold text-[#182023] tracking-tight">
                                    {{ __('ui.vendor_cta_title') }}
                                </h3>
                                <p class="mt-1 text-sm text-[#596166] max-w-xl">
                                    {{ __('ui.vendor_cta_body') }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2 shrink-0">
                                <x-button href="{{ route('register', ['role' => 'vendor']) }}" variant="primary">
                                    {{ __('ui.vendor_cta_primary') }}
                                </x-button>
                                <x-button href="{{ route('vendors.index') }}" variant="secondary">
                                    {{ __('ui.vendor_cta_secondary') }}
                                </x-button>
                            </div>
                        </div>
                    </div>
                @endunless

                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        @include('partials.platform-brand', ['brandHref' => route('home')])
                        <p class="mt-3 max-w-md text-sm leading-relaxed text-[#596166]">
                            {{ __('ui.tagline') }}
                        </p>
                        <div class="mt-4">
                            @include('partials.platform-contact')
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-[#596166]">{{ __('ui.discover') }}</p>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li><a href="{{ route('products.index') }}" class="text-[#182023] hover:text-[#C2410C] transition">{{ __('ui.products') }}</a></li>
                            <li><a href="{{ route('service-requests.index') }}" class="text-[#182023] hover:text-[#C2410C] transition">Hizmet Talepleri</a></li>
                            <li><a href="{{ route('quote-requests.create', ['type' => 'physical_quote']) }}" class="text-[#182023] hover:text-[#C2410C] transition">Özel Baskı Teklifi</a></li>
                            <li><a href="{{ route('vendors.index') }}" class="text-[#182023] hover:text-[#C2410C] transition">{{ __('ui.vendors') }}</a></li>
                            <li><a href="{{ route('blog.index') }}" class="text-[#182023] hover:text-[#C2410C] transition">Blog & İçerikler</a></li>
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-[#596166]">{{ __('ui.corporate') }}</p>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li><a href="{{ route('pages.about') }}" class="text-[#182023] hover:text-[#C2410C] transition">{{ __('ui.about') }}</a></li>
                            <li><a href="{{ route('pages.contact') }}" class="text-[#182023] hover:text-[#C2410C] transition">{{ __('ui.contact') }}</a></li>
                            <li><a href="{{ route('pages.terms') }}" class="text-[#182023] hover:text-[#C2410C] transition">{{ __('ui.terms') }}</a></li>
                            <li><a href="{{ route('pages.privacy') }}" class="text-[#182023] hover:text-[#C2410C] transition">{{ __('ui.privacy') }}</a></li>
                            <li><a href="{{ route('sitemap') }}" class="text-[#182023] hover:text-[#C2410C] transition">Site Haritası</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 flex flex-col gap-2 border-t border-[#DEDAD2] pt-6 text-xs text-[#596166] sm:flex-row sm:items-center sm:justify-between">
                    <p>{{ __('ui.rights', ['year' => date('Y')]) }}</p>
                    <p>{{ __('ui.country') }} · Güvenli E-Ticaret & Özel Üretim Platformu</p>
                </div>
            </div>
        </footer>
    </div>

    @include('partials.review-prompt')
    @include('partials.pwa-install')
    @include('partials.product-card-scripts')
    @stack('scripts')
</body>
</html>
