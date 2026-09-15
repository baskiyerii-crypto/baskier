<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm font-extrabold tracking-tight text-slate-900">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-sm">B</span>
                        <span>Hesabım</span>
                    </a>
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
                    <div class="p-5 border-b border-slate-200/70">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Hesabım</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? '' }}</p>
                    </div>
                    <nav class="p-2">
                        @php
                            $items = [
                                ['label' => 'Özet', 'route' => 'customer.dashboard', 'match' => 'customer.dashboard'],
                                ['label' => 'Siparişlerim', 'route' => 'account.orders.index', 'match' => 'account.orders.*'],
                                ['label' => 'Sepet', 'route' => 'cart.index', 'match' => 'cart.*'],
                                ['label' => 'Favorilerim', 'route' => 'favorites.index', 'match' => 'favorites.*'],
                                ['label' => 'Adreslerim', 'route' => 'account.adresler.index', 'match' => 'account.adresler.*'],
                                ['label' => 'Teklif taleplerim', 'route' => 'quote-requests.index', 'match' => 'quote-requests.*'],
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
</body>
</html>
