<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Hesabım') – BaskıYeri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --by-accent: #f97316; --by-bg: #f8fafc; --by-border: #e2e8f0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--by-bg); color: #0f172a; min-height: 100vh; }
        .acc-top { background: #fff; border-bottom: 1px solid var(--by-border); padding: .75rem 0; position: sticky; top: 0; z-index: 100; }
        .acc-top .logo { font-family: Georgia, serif; font-weight: 600; font-size: 1.25rem; color: #111; text-decoration: none; }
        .acc-top .logo span { color: var(--by-accent); }
        .acc-shell { max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem; }
        .acc-side { background: #fff; border: 1px solid var(--by-border); border-radius: 14px; padding: 0; overflow: hidden; }
        .acc-side .list-group-item { border: none; border-bottom: 1px solid #f1f5f9; padding: .75rem 1rem; font-size: .9375rem; font-weight: 500; color: #475569; }
        .acc-side .list-group-item:hover { background: #fff7ed; color: #c2410c; }
        .acc-side .list-group-item.active { background: #fff7ed; color: #ea580c; border-left: 3px solid var(--by-accent); }
        .acc-main { background: #fff; border: 1px solid var(--by-border); border-radius: 14px; padding: 1.5rem; min-height: 420px; }
        .acc-side-title { font-size: .7rem; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; padding: 1rem 1rem .25rem; font-weight: 600; }
    </style>
</head>
<body>
    <header class="acc-top">
        <div class="container-fluid px-3 d-flex justify-content-between align-items-center" style="max-width:1200px;margin:0 auto;">
            <a href="{{ route('home') }}" class="logo">Baskı<span>Yeri</span></a>
            <div class="d-flex align-items-center gap-3 small">
                <a href="{{ route('cart.index') }}" class="text-decoration-none text-dark">Sepet</a>
                <a href="{{ route('products.index') }}" class="text-decoration-none text-muted d-none d-md-inline">Alışveriş</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf
                    <button type="submit" class="btn btn-link btn-sm text-muted p-0">Çıkış</button>
                </form>
            </div>
        </div>
    </header>
    <div class="acc-shell">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="acc-side">
                    <div class="acc-side-title px-3 pt-3 pb-0">Hesabım</div>
                    <div class="list-group list-group-flush rounded-0">
                        <a href="{{ route('customer.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">Özet</a>
                        <a href="{{ route('account.orders.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('account.orders.*') ? 'active' : '' }}">Siparişlerim</a>
                        <a href="{{ route('cart.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('cart.*') ? 'active' : '' }}">Sepet</a>
                        <a href="{{ route('favorites.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('favorites.*') ? 'active' : '' }}">Favorilerim</a>
                        <a href="{{ route('account.adresler.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('account.adresler.*') ? 'active' : '' }}">Adreslerim</a>
                        <a href="{{ route('quote-requests.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('quote-requests.*') ? 'active' : '' }}">Teklif taleplerim</a>
                        <a href="{{ route('freelancer-jobs.index') }}" class="list-group-item list-group-item-action">İş ilanları</a>
                        <a href="{{ route('freelancer-jobs.my') }}" class="list-group-item list-group-item-action {{ request()->routeIs('freelancer-jobs.my') ? 'active' : '' }}">İlanlarım</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="acc-main">
                    @if(session('success'))<div class="alert alert-success py-2 small">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="alert alert-danger py-2 small">{{ session('error') }}</div>@endif
                    @if(session('info'))<div class="alert alert-info py-2 small">{{ session('info') }}</div>@endif
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
