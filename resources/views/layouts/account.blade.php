<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ea580c">
    <link rel="manifest" href="/manifest.webmanifest">
    <title>@yield('title', 'Hesabım') – BaskıYeri</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen font-sans">
    <div class="min-h-screen flex flex-col">
        <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/70 backdrop-blur">
            <div class="by-container py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm font-extrabold tracking-tight text-slate-900">
                        @include('partials.platform-brand', ['compact' => true, 'logoOnly' => true])
                        <a href="{{ route('customer.dashboard') }}">Hesabım</a>
                    </div>
                    <div class="flex items-center gap-2">
                        @include('partials.locale-switcher')
                        <span class="hidden sm:inline text-xs font-mono text-slate-500">{{ auth()->user()?->publicCode() }}</span>
                        <a href="{{ route('products.index') }}" class="hidden sm:inline-flex by-btn-secondary">Alışveriş</a>
                        <a href="{{ route('otp.show') }}" class="hidden sm:inline-flex by-btn-secondary">{{ __('panel.verify_account') }}</a>
                        <a href="{{ route('cart.index') }}" class="by-btn-primary px-4 py-2.5">Sepet</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="by-btn-secondary">{{ __('panel.logout') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="by-container py-8 flex-1">
            <div class="account-layout">
                <aside class="account-layout__sidebar by-card overflow-hidden">
                    <div class="p-5 border-b border-slate-200/70 flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Hesabım</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? '' }}</p>
                        </div>
                        <button type="button" class="md:hidden by-btn-secondary px-3 py-2" data-account-nav-toggle aria-label="{{ __('panel.toggle_nav') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                        </button>
                    </div>
                    <nav class="p-2 account-nav" data-account-nav>
                        @php
                            $items = [
                                ['label' => 'Özet', 'route' => 'customer.dashboard', 'match' => 'customer.dashboard'],
                                ['label' => 'Siparişlerim', 'route' => 'account.orders.index', 'match' => 'account.orders.*'],
                                ['label' => 'Sepet', 'route' => 'cart.index', 'match' => 'cart.*'],
                                ['label' => 'Favorilerim', 'route' => 'favorites.index', 'match' => 'favorites.*'],
                                ['label' => 'Adreslerim', 'route' => 'account.adresler.index', 'match' => 'account.adresler.*'],
                                ['label' => 'Teklif taleplerim', 'route' => 'quote-requests.index', 'match' => 'quote-requests.*'],
                                ['label' => __('panel.direct_quotes'), 'route' => 'customer.direct-quotes.index', 'match' => 'customer.direct-quotes.*'],
                                ['label' => 'Destek talepleri', 'route' => 'account.support.index', 'match' => 'account.support.*'],
                                ['label' => 'İş ilanları', 'route' => 'freelancer-jobs.index', 'match' => 'freelancer-jobs.index'],
                                ['label' => 'İlanlarım', 'route' => 'freelancer-jobs.my', 'match' => 'freelancer-jobs.my'],
                            ];
                        @endphp

                        @if(auth()->user()?->role === 'customer')
                            <a href="{{ route('customer.price-estimate') }}"
                               class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('customer.price-estimate*') ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                İş hesaplama
                            </a>
                        @endif

                        @foreach($items as $it)
                            <a href="{{ route($it['route']) }}"
                               class="mt-1 block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs($it['match']) ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                {{ $it['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <main class="account-layout__main by-card p-6 md:p-8">
                    @if(session('success') && !request()->routeIs('account.adresler.index'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
                    @yield('content')
                </main>
            </div>
        </div>

        @stack('scripts')
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
</body>
</html>
