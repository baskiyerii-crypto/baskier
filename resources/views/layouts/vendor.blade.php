<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Panel') – BaskıYeri Satıcı</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#059669">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --side-bg: #f8fafc;
            --side-border: #e2e8f0;
            --side-text: #475569;
            --side-text-hover: #0f172a;
            --side-active-bg: #ecfdf5;
            --side-active-text: #047857;
            --side-accent: #059669;
            --header-bg: #fff;
            --content-bg: #f8fafc;
            --card-radius: 14px;
        }
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--content-bg); color: #0f172a; min-height: 100vh; }
        .vendor-shell { display: flex; min-height: 100vh; }
        .vendor-sidebar {
            width: 260px; background: var(--side-bg); border-right: 1px solid var(--side-border);
            position: fixed; top: 0; left: 0; height: 100vh; z-index: 1040;
            display: flex; flex-direction: column; transition: transform 0.2s ease-in-out;
        }
        .vendor-sidebar .brand { padding: 1.25rem 1.25rem; border-bottom: 1px solid var(--side-border); }
        .vendor-sidebar .brand a { font-weight: 700; font-size: 1.15rem; color: var(--side-text-hover); text-decoration: none; }
        .vendor-sidebar .brand .accent { color: var(--side-accent); }
        .vendor-sidebar .nav { flex: 1; padding: 0.75rem 0.65rem; overflow-y: auto; display: flex; flex-direction: column; gap: 3px; }
        .vendor-sidebar .nav-link {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px;
            color: var(--side-text); text-decoration: none; font-size: 0.875rem; font-weight: 500;
            border-radius: 8px; transition: background 0.15s, color 0.15s;
        }
        .vendor-sidebar .nav-link:hover { color: var(--side-text-hover); background: rgba(5, 150, 105, 0.06); }
        .vendor-sidebar .nav-link.active { color: var(--side-active-text); background: var(--side-active-bg); font-weight: 600; }
        .vendor-sidebar .nav-link svg { flex-shrink: 0; opacity: 0.85; }
        .vendor-sidebar .balance-box { padding: 0.75rem 1rem; margin: 0.5rem 0.65rem; background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 10px; font-size: 0.8125rem; }
        .vendor-sidebar .balance-box strong { color: #047857; }
        .vendor-sidebar .user-footer { padding: 0.75rem 1rem; border-top: 1px solid var(--side-border); }
        .vendor-sidebar .btn-logout {
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
            padding: 8px 12px; font-size: 0.8125rem; font-weight: 500; color: #64748b;
            background: #fff; border: 1px solid var(--side-border); border-radius: 8px;
            cursor: pointer; transition: background 0.15s, color 0.15s;
        }
        .vendor-sidebar .btn-logout:hover { color: #dc2626; background: #fef2f2; border-color: #fecaca; }
        .vendor-main { flex: 1; margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        .vendor-header {
            height: 60px; background: var(--header-bg); border-bottom: 1px solid var(--side-border);
            display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem;
            position: sticky; top: 0; z-index: 30;
        }
        .vendor-header .page-title { font-size: 1.0625rem; font-weight: 600; color: #0f172a; }
        .vendor-header .user-menu { display: flex; align-items: center; gap: 1rem; }
        .vendor-header .user-menu .sep { width: 1px; height: 18px; background: var(--side-border); }
        .vendor-header .user-menu a { color: #64748b; font-size: 0.8125rem; text-decoration: none; font-weight: 500; }
        .vendor-header .user-menu a:hover { color: var(--side-accent); }
        .vendor-header .btn-out-vendor { padding: 4px 10px; font-size: 0.75rem; font-weight: 500; color: #64748b; background: transparent; border: 1px solid var(--side-border); border-radius: 6px; cursor: pointer; }
        .vendor-header .btn-out-vendor:hover { color: #dc2626; background: #fef2f2; border-color: #fecaca; }
        .vendor-content { flex: 1; padding: 1.5rem; }
        .vendor-content .card { border: 1px solid var(--side-border); border-radius: var(--card-radius); box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1030; }
        @media (max-width: 991.98px) {
            .vendor-sidebar { transform: translateX(-100%); }
            .vendor-sidebar.show { transform: translateX(0); }
            .sidebar-backdrop.show { display: block; }
            .vendor-main { margin-left: 0; }
        }
    </style>
</head>
<body class="min-h-screen">
<div class="panel-backdrop" data-panel-backdrop></div>
<div class="vendor-shell">
    <aside class="vendor-sidebar" id="panel-sidebar" data-panel-sidebar tabindex="-1" aria-label="Satıcı menüsü">
        <div class="brand d-flex justify-content-between align-items-center">
            <a href="{{ route('vendor.dashboard') }}">Baskı<span class="accent">Yeri</span> <span class="accent">Satıcı</span></a>
            <button type="button" class="panel-close-button" data-panel-close aria-label="Menüyü kapat">×</button>
        </div>
        <nav class="nav" aria-label="Panel menüsü">
            <a href="{{ route('vendor.dashboard') }}" class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span>Genel bakış</span>
            </a>
            <a href="{{ route('vendor.orders.index') }}" class="nav-link {{ request()->routeIs('vendor.orders.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span>Siparişler</span>
            </a>
            <a href="{{ route('vendor.products.index') }}" class="nav-link {{ request()->routeIs('vendor.products.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>Ürünlerim</span>
            </a>
            <a href="{{ route('vendor.documents.index') }}" class="nav-link {{ request()->routeIs('vendor.documents.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>Belgeler</span>
            </a>
            @if(auth()->user()->vendor?->hasActiveQuotesModule())
            <a href="{{ route('vendor.quote-requests.index') }}" class="nav-link {{ request()->routeIs('vendor.quote-requests.*') && request()->route('quoteRequest')?->request_type !== 'freelancer' ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span>Toplu üretim / Teklifler</span>
            </a>
            @endif
            @if(auth()->user()->vendor?->hasActiveFreelancerModule())
            <a href="{{ route('vendor.freelancer.index') }}" class="nav-link {{ request()->routeIs('vendor.freelancer.*') || request()->route('quoteRequest')?->request_type === 'freelancer' ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
                <span>Freelancerım</span>
            </a>
            @endif
            @if(auth()->user()->vendor?->hasActiveTabelaModule())
            <a href="{{ route('vendor.tabela.index') }}" class="nav-link {{ request()->routeIs('vendor.tabela.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M7 20h10"/></svg>
                <span>Tabela</span>
            </a>
            @endif
            <a href="{{ route('vendor.payout-requests.index') }}" class="nav-link {{ request()->routeIs('vendor.payout-requests.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Hakediş & Para Çekme</span>
            </a>
            <a href="{{ route('vendor.balance.index') }}" class="nav-link {{ request()->routeIs('vendor.balance.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
                <span>Görüşme Bakiyesi</span>
            </a>
            <a href="{{ route('vendor.messages.index') }}" class="nav-link {{ request()->routeIs('vendor.messages.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Müşteri Mesajları</span>
            </a>
            <a href="{{ route('vendor.subscriptions.index') }}" class="nav-link {{ request()->routeIs('vendor.subscriptions.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <span>Modüller & Abonelik</span>
            </a>
            @if(auth()->user()->vendor)
            <a href="{{ route('vendors.show', auth()->user()->vendor->slug) }}" target="_blank" rel="noopener" class="nav-link text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg>
                <span>Vitrin Mağazam ↗</span>
            </a>
            @endif
        </nav>
        @if($vendor = auth()->user()->vendor)
        <div class="balance-box">
            <div class="d-flex justify-content-between align-items-center">
                <span>Çekilebilir:</span>
                <strong>₺{{ number_format($vendor->balance, 2, ',', '.') }}</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <a href="{{ route('vendor.payout-requests.index') }}" class="small text-decoration-none">Para Çek →</a>
                <a href="{{ route('vendor.balance.index') }}" class="small text-muted text-decoration-none">Bakiye</a>
            </div>
        </div>
        @endif
        <div class="user-footer">
            <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">@csrf
                <button type="submit" class="btn-logout w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>Çıkış Yap</span>
                </button>
            </form>
        </div>
    </aside>
    <main class="vendor-main">
        <header class="vendor-header">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="panel-menu-button" data-panel-open aria-controls="panel-sidebar" aria-expanded="false" aria-label="Menüyü aç">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <h1 class="page-title mb-0">@yield('title', 'Panel')</h1>
            </div>
            <div class="user-menu">
                <a href="{{ route('home') }}" target="_blank" rel="noopener">Pazaryerine Git ↗</a>
                <span class="sep"></span>
                <span class="text-muted small fw-medium">{{ auth()->user()->name ?? '' }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf
                    <button type="submit" class="btn-out-vendor">Çıkış</button>
                </form>
            </div>
        </header>
        <div class="vendor-content">
            @if(session('success'))<div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if(session('error'))<div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @include('partials.validation-errors')
            @yield('content')
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
@include('partials.pwa-install')
</body>
</html>
