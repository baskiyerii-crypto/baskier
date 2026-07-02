<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'BaskıYeri – Matbaa & Reklam Pazaryeri')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans">
<div class="min-h-screen flex flex-col">
    <div data-left-drawer class="fixed inset-0 z-[60] pointer-events-none">
        <div data-left-drawer-overlay class="absolute inset-0 bg-slate-900/40 opacity-0 transition-opacity duration-200"></div>
        <div data-left-drawer-panel class="absolute left-0 top-0 h-full w-[340px] max-w-[88vw] -translate-x-full transition-transform duration-200">
            <div class="h-full bg-white/95 backdrop-blur border-r border-slate-200 shadow-xl">
                <div class="p-4 border-b border-slate-200/70 flex items-center justify-between gap-2">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm font-extrabold tracking-tight text-slate-900">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-sm">B</span>
                        <span>BaskıYeri</span>
                    </a>
                    <button data-left-drawer-close class="by-btn-secondary px-3 py-2" type="button" aria-label="Menüyü kapat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <div class="p-4">
                    <form action="{{ route('products.index') }}">
                        <div class="relative">
                            <input class="by-input pl-11" name="q" value="{{ request('q') }}" placeholder="Ürün ara (kartvizit, broşür, tabela...)" />
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                        </div>
                    </form>
                </div>

                <div class="px-4 pb-4">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Menü</p>
                    <div class="mt-2 grid gap-2">
                        <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('home') }}">Anasayfa</a>
                        <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('products.index') }}">Ürünler</a>
                        <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('vendors.index') }}">Satıcılar</a>
                        <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('freelancer-jobs.index') }}">İş ilanları</a>
                    </div>
                </div>

                <div class="px-4 pb-4">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Hesap</p>
                    <div class="mt-2 grid gap-2">
                        @auth
                            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('cart.index') }}">Sepet</a>
                            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('account.orders.index') }}">Siparişlerim</a>
                            @if(auth()->user()->isAdmin())
                                <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('admin.dashboard') }}">Yönetici Paneli</a>
                            @endif
                            @if(auth()->user()->isVendor())
                                <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('vendor.dashboard') }}">Satıcı Paneli</a>
                            @endif
                            @if(auth()->user()->isCustomer())
                                <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('customer.dashboard') }}">Hesabım</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full by-btn-secondary">Çıkış</button>
                            </form>
                        @else
                            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('login') }}">Giriş Yap</a>
                            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('register') }}">Kayıt Ol</a>
                        @endauth
                    </div>
                </div>

                <div class="px-4 pb-6">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Kategoriler</p>
                    <div class="mt-2 max-h-[38vh] overflow-auto pr-1">
                        @if(!empty($headerCategories) && $headerCategories->isNotEmpty())
                            <div class="grid gap-1.5">
                                @foreach($headerCategories as $parentCategory)
                                    <a class="rounded-2xl px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-50"
                                       href="{{ route('products.index', ['category_id' => $parentCategory->id]) }}">
                                        {{ $parentCategory->name }}
                                    </a>
                                    @foreach($parentCategory->children as $childCategory)
                                        <a class="ml-3 rounded-2xl px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50"
                                           href="{{ route('products.index', ['category_id' => $childCategory->id]) }}">
                                            {{ $childCategory->name }}
                                        </a>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            <p class="px-2 py-2 text-sm text-slate-500">Kategori bulunamadı.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/70 backdrop-blur">
        <div class="by-container py-4">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm font-extrabold tracking-tight text-slate-900">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-sm">B</span>
                    <span>BaskıYeri</span>
                </a>

                <form action="{{ route('products.index') }}" class="hidden flex-1 lg:block">
                    <div class="relative">
                        <input class="by-input pl-11" name="q" value="{{ request('q') }}" placeholder="Ürün ara (kartvizit, broşür, tabela...)" />
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </span>
                    </div>
                </form>

                <div class="flex items-center gap-2">
                    <button type="button" data-left-drawer-open class="by-btn-secondary px-4 py-2.5">
                        Menü
                    </button>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex by-btn-secondary">Yönetim</a>
                        @elseif(auth()->user()->isVendor())
                            <a href="{{ route('vendor.dashboard') }}" class="hidden sm:inline-flex by-btn-secondary">Satıcı</a>
                        @else
                            <a href="{{ route('customer.dashboard') }}" class="hidden sm:inline-flex by-btn-secondary">Hesabım</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex by-btn-secondary">Giriş</a>
                    @endauth

                    @auth
                        <a href="{{ route('cart.index') }}" class="by-btn-primary px-4 py-2.5">
                            Sepet
                            <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">
                                {{ auth()->user()->cartItems()->count() }}
                            </span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="by-btn-primary px-4 py-2.5">Sepet</a>
                    @endauth
                </div>
            </div>

            <form action="{{ route('products.index') }}" class="mt-4 lg:hidden">
                <input class="by-input" name="q" value="{{ request('q') }}" placeholder="Ürün ara..." />
            </form>
        </div>
    </header>

    <main class="flex-1">
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
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-slate-200/70 bg-white/60 backdrop-blur">
        <div class="by-container py-12">
            <div class="by-card by-gradient-border p-6 md:p-8 mb-10">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Satıcı mısın?</p>
                        <p class="mt-2 text-xl font-extrabold tracking-tight text-slate-900">
                            BaskıYeri’nde mağazanı aç, tekliflere cevap ver, sipariş al.
                        </p>
                        <p class="mt-2 text-sm text-slate-600">
                            Satıcı hesabı oluşturup iş kolunu seçerek başvurunu tamamla.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('register', ['role' => 'vendor']) }}" class="by-btn-cta px-7 py-3 text-base">Satıcı ol</a>
                        <a href="{{ route('vendors.index') }}" class="by-btn-secondary px-7 py-3 text-base">Satıcıları gör</a>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 md:grid-cols-4">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 text-base font-extrabold tracking-tight text-slate-900">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-sm">B</span>
                        <span>BaskıYeri</span>
                    </div>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-600">
                        Matbaa, tabela, promosyon ve özel üretim işleriniz için modern pazaryeri + teklif (RFQ) platformu.
                    </p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Keşfet</p>
                    <div class="mt-3 space-y-2 text-sm">
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('products.index') }}">Ürünler</a>
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('vendors.index') }}">Satıcılar</a>
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('freelancer-jobs.index') }}">İş ilanları</a>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Kurumsal</p>
                    <div class="mt-3 space-y-2 text-sm">
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('pages.about') }}">Hakkımızda</a>
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('pages.terms') }}">Kullanım koşulları</a>
                        <a class="block text-slate-700 hover:text-slate-900" href="{{ route('pages.privacy') }}">Gizlilik</a>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-2 border-t border-slate-200 pt-6 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                <p>© {{ date('Y') }} BaskıYeri. Tüm hakları saklıdır.</p>
                <p>Türkiye</p>
            </div>
        </div>
    </footer>
</div>
</body>
</html>

