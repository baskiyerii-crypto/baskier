<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#C2410C">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <title>@yield('title', 'Hesabım') – BaskıYeri</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('head')
</head>
<body class="storefront min-h-screen font-sans bg-[#F7F5F0] text-[#182023]">
    <div class="min-h-screen flex flex-col">
        {{-- Account Header --}}
        <header class="sticky top-0 z-40 border-b border-[#DEDAD2] bg-white/95 backdrop-blur-md">
            <div class="by-container py-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 text-sm font-bold tracking-tight text-[#182023]">
                        @include('partials.platform-brand', ['compact' => true, 'logoOnly' => false, 'brandHref' => route('home')])
                        <span class="text-[#DEDAD2]">/</span>
                        <a href="{{ route('customer.dashboard') }}" class="text-[#596166] hover:text-[#182023]">Hesabım</a>
                    </div>

                    <div class="flex items-center gap-2">
                        @include('partials.locale-switcher', ['compact' => true])
                        <span class="hidden sm:inline-block px-2 py-1 text-xs font-mono bg-[#F7F5F0] rounded-md border border-[#DEDAD2] text-[#596166]">
                            {{ auth()->user()?->publicCode() }}
                        </span>
                        <a href="{{ route('products.index') }}" class="hidden sm:inline-flex items-center justify-center min-h-[40px] px-3.5 py-1.5 text-xs font-semibold rounded-lg border border-[#DEDAD2] bg-white hover:bg-[#F7F5F0] transition">
                            Alışveriş
                        </a>
                        <a href="{{ route('cart.index') }}" class="relative inline-flex items-center justify-center min-h-[40px] px-3.5 py-1.5 text-xs font-bold rounded-lg bg-[#C2410C] text-white hover:bg-[#9A3412] transition">
                            <span>Sepet</span>
                            @php $cartCount = auth()->user()->cartItems()->sum('quantity'); @endphp
                            @if($cartCount > 0)
                                <span class="ml-1.5 rounded-full bg-white/20 px-1.5 py-0.2 text-[10px] font-bold">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline-block">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center min-h-[40px] px-3.5 py-1.5 text-xs font-semibold rounded-lg border border-[#DEDAD2] bg-white text-[#182023] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition">
                                {{ __('panel.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Account Body --}}
        <div class="by-container py-6 sm:py-8 flex-1">
            <div class="account-layout">
                {{-- Left Sidebar --}}
                <aside class="account-layout__sidebar rounded-xl border border-[#DEDAD2] bg-white shadow-xs overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-[#DEDAD2] flex items-center justify-between gap-2 bg-[#F7F5F0]/50">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#596166]">Müşteri Paneli</p>
                            <p class="mt-0.5 text-sm font-bold text-[#182023] truncate">{{ auth()->user()->name ?? '' }}</p>
                        </div>
                        <button type="button" class="md:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#DEDAD2] bg-white text-[#182023]" data-account-nav-toggle aria-label="{{ __('panel.toggle_nav') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                        </button>
                    </div>

                    <nav class="p-2 space-y-1 account-nav" data-account-nav>
                        @php
                            $items = [
                                ['label' => 'Özet', 'route' => auth()->user()->isAdmin() ? 'admin.dashboard' : (auth()->user()->isVendor() ? 'vendor.dashboard' : 'customer.dashboard'), 'match' => '*.dashboard'],
                                ['label' => 'Siparişlerim', 'route' => 'account.orders.index', 'match' => 'account.orders.*'],
                                ['label' => 'Sepetim', 'route' => 'cart.index', 'match' => 'cart.*'],
                                ['label' => 'Favorilerim', 'route' => 'favorites.index', 'match' => 'favorites.*'],
                                ['label' => 'Teslimat Adreslerim', 'route' => 'account.adresler.index', 'match' => 'account.adresler.*'],
                                ['label' => 'Teklif Taleplerim', 'route' => 'quote-requests.index', 'match' => 'quote-requests.*'],
                                ['label' => __('panel.direct_quotes'), 'route' => 'customer.direct-quotes.index', 'match' => 'customer.direct-quotes.*'],
                                ['label' => 'Destek Taleplerim', 'route' => 'account.support.index', 'match' => 'account.support.*'],
                            ];

                            if (auth()->user()->isCustomer()) {
                                $items[] = ['label' => 'Hizmet Taleplerim', 'route' => 'service-requests.my', 'match' => 'service-requests.*'];
                            } else {
                                $items[] = ['label' => 'Açık Hizmet Talepleri', 'route' => 'service-requests.index', 'match' => 'service-requests.*'];
                            }
                        @endphp

                        @if(auth()->user()?->role === 'customer')
                            <a href="{{ route('customer.price-estimate') }}"
                               class="flex items-center gap-2 rounded-lg px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('customer.price-estimate*') ? 'bg-[#F7F5F0] text-[#C2410C] font-bold' : 'text-[#182023] hover:bg-[#F7F5F0]' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M8 10h.01M12 10h.01M8 14h.01M12 14h.01M8 18h.01M12 18h.01"/></svg>
                                <span>İş Fiyatı Hesaplama</span>
                            </a>
                        @endif

                        @foreach($items as $it)
                            <a href="{{ route($it['route']) }}"
                               class="flex items-center justify-between rounded-lg px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs($it['match']) ? 'bg-[#F7F5F0] text-[#C2410C] font-bold border-l-2 border-[#C2410C]' : 'text-[#182023] hover:bg-[#F7F5F0]' }}">
                                <span>{{ $it['label'] }}</span>
                            </a>
                        @endforeach

                        <div class="pt-3 mt-3 border-t border-[#DEDAD2] sm:hidden">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3.5 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50 rounded-lg">
                                    {{ __('panel.logout') }}
                                </button>
                            </form>
                        </div>
                    </nav>
                </aside>

                {{-- Main Account Content Area --}}
                <main class="account-layout__main rounded-xl border border-[#DEDAD2] bg-white p-5 sm:p-8 shadow-xs">
                    {{-- Flash Alerts --}}
                    <div class="mb-5 space-y-3">
                        @if(session('success') && !request()->routeIs('account.adresler.index'))
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
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 767.98px) {
            .account-nav { display: none; }
            .account-nav.is-open { display: block; }
        }
    </style>
    <script>
        document.querySelector('[data-account-nav-toggle]')?.addEventListener('click', () => {
            document.querySelector('[data-account-nav]')?.classList.toggle('is-open');
        });
    </script>
    @include('partials.review-prompt')
    @include('partials.pwa-install')
    @include('partials.floating-support')
    @stack('scripts')
</body>
</html>
