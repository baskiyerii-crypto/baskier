<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Panel') – BaskıYeri Satıcı</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="theme-color" content="#059669">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
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
        .vendor-sidebar.is-collapsed { transform: translateX(-100%); }
        .vendor-shell.nav-collapsed .vendor-main { margin-left: 0; }
        .vendor-sidebar details.nav-acc > summary {
            list-style: none; cursor: pointer; padding: 9px 12px; color: var(--side-text); font-weight: 600; font-size: 0.8125rem;
            border-radius: 8px; display: flex; justify-content: space-between; align-items: center;
        }
        .vendor-sidebar details.nav-acc > summary::-webkit-details-marker { display: none; }
        .vendor-sidebar details.nav-acc[open] > summary { background: rgba(5,150,105,.08); color: var(--side-active-text); }
        .vendor-sidebar details.nav-acc .nav-acc-body { display: flex; flex-direction: column; flex-wrap: nowrap; gap: 2px; padding: 4px 0 8px 6px; width: 100%; }
        .vendor-content .card { transition: transform .15s ease, box-shadow .15s ease; background: linear-gradient(180deg,#fff,#f8fafc); }
        .vendor-content .card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(15,23,42,.08) !important; }
        .vendor-sidebar .brand { padding: 1.25rem 1.25rem; border-bottom: 1px solid var(--side-border); }
        .vendor-sidebar .brand a { font-weight: 700; font-size: 1.15rem; color: var(--side-text-hover); text-decoration: none; }
        .vendor-sidebar .brand .accent { color: var(--side-accent); }
        .vendor-sidebar .nav {
            flex: 1;
            min-height: 0;
            padding: 0.75rem 0.65rem;
            overflow-x: hidden;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            flex-wrap: nowrap;
            gap: 3px;
            width: 100%;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        .vendor-sidebar .nav::-webkit-scrollbar { width: 6px; }
        .vendor-sidebar .nav::-webkit-scrollbar-track { background: transparent; }
        .vendor-sidebar .nav::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }
        .vendor-sidebar .nav::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .vendor-sidebar details.nav-acc {
            width: 100%;
            flex: 0 0 auto;
        }
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
            .vendor-content { padding: 0.75rem !important; min-width: 0; overflow-x: hidden; }
            .vendor-header { padding: 0 0.75rem; height: 56px; }
            .vendor-header .page-title { font-size: 0.95rem; max-width: 42vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .vendor-header .user-menu a:not(.btn-out-vendor) { display: none; }
            .vendor-header .user-menu .sep { display: none; }
            .vendor-content .table-responsive { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .vendor-content .d-flex.gap-2 { flex-wrap: wrap; }
            .vendor-content .btn { white-space: normal; }
        }
    </style>
</head>
<body class="min-h-screen">
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>
<div class="vendor-shell" id="vendorShell">
    <aside class="vendor-sidebar" id="vendorSidebar">
        <div class="brand">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('vendor.dashboard') }}">Baskı<span class="accent">Yeri</span> <span class="accent">Satıcı</span></a>
                <button type="button" class="btn-close d-lg-none" onclick="toggleSidebar()" aria-label="Kapat"></button>
            </div>
            @php $sidebarVendor = auth()->user()?->vendor; @endphp
            <div class="mt-2 small">
                <span class="badge bg-success-subtle text-success border">Satıcı ID: {{ auth()->user()?->publicCode() }}</span>
                @if($sidebarVendor)<span class="badge bg-light text-dark border">#{{ $sidebarVendor->id }}</span>@endif
            </div>
        </div>
        @php
            $v = auth()->user()->vendor;
            $oohRole = null;
            try {
                if ($v && \Illuminate\Support\Facades\Schema::hasTable('vendor_members') && ($v->hasActiveOutdoorModule() || $v->hasOutdoorTrack())) {
                    $oohRole = app(\App\Services\OutdoorStaffService::class)->roleFor(auth()->user(), $v);
                }
            } catch (\Throwable) {
                $oohRole = null;
            }
        @endphp
        <nav class="nav">
            @include('partials.vendor-sidebar-nav')
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
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSidebar()" aria-label="{{ __('panel.toggle_nav') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <h1 class="page-title mb-0">@yield('title', 'Panel')</h1>
            </div>
            <div class="user-menu">
                @include('partials.locale-switcher')
                <span class="sep"></span>
                @include('partials.notification-bell', ['variant' => 'bootstrap'])
                <span class="sep"></span>
                <span class="badge bg-light text-dark border">{{ auth()->user()?->publicCode() }}</span>
                @if($sidebarVendor && $sidebarVendor->hasActiveOutdoorModule() && \Illuminate\Support\Facades\Route::has('outdoor-panel.dashboard'))
                    <a href="{{ route('outdoor-panel.dashboard') }}">Açık hava paneli</a>
                    <span class="sep"></span>
                @endif
                <a href="{{ route('home') }}" target="_blank">{{ __('panel.view_site') }} ↗</a>
                <span class="sep"></span>
                <span class="text-muted small fw-medium">{{ auth()->user()->name ?? '' }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf
                    <button type="submit" class="btn-out-vendor">{{ __('panel.logout') }}</button>
                </form>
            </div>
        </header>
        <div class="vendor-content">
            @php $sidebarVendor = $sidebarVendor ?? auth()->user()?->vendor; @endphp
            @if($sidebarVendor?->is_suspended)
                <div class="alert alert-danger">Hesabınız askıya alındı: {{ $sidebarVendor->suspension_reason ?: 'Yönetim incelemesi devam ediyor.' }} Ürün, teklif ve mesaj gönderemezsiniz.</div>
            @elseif($sidebarVendor?->contract_suspended_at)
                <div class="alert alert-warning">Bekleyen sözleşmeleri onaylayana kadar faaliyetiniz donduruldu.</div>
            @endif
            @if(session('success'))<div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if(session('error'))<div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @include('partials.validation-errors')
            @yield('content')
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('vendorSidebar');
        const shell = document.getElementById('vendorShell');
        const backdrop = document.getElementById('sidebarBackdrop');
        const isMobile = window.matchMedia('(max-width: 991.98px)').matches;
        if (isMobile) {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        } else {
            sidebar.classList.toggle('is-collapsed');
            shell.classList.toggle('nav-collapsed');
        }
    }

    // Global: Sayısal ve ölçü/birim alanlarına harf girişini engelleme
    document.addEventListener('keydown', function(e) {
        const target = e.target;
        if (target && (target.classList.contains('number-only-input') || target.type === 'number')) {
            const allowedKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', '.', ',', '-', 'Enter'];
            if (allowedKeys.includes(e.key)) return;
            if (e.ctrlKey || e.metaKey) return;
            if (!/^[0-9]$/.test(e.key)) {
                e.preventDefault();
            }
        }
    });
</script>
@stack('scripts')
@include('partials.pwa-install')
</body>
</html>
