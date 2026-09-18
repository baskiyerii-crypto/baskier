<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Panel') – BaskıYeri Açık Hava</title>
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
            --side-bg: #f8fafc; --side-border: #e2e8f0; --side-text: #475569;
            --side-text-hover: #0f172a; --side-active-bg: #ecfdf5; --side-active-text: #047857;
            --side-accent: #059669; --header-bg: #fff; --content-bg: #f8fafc; --card-radius: 14px;
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
        .vendor-sidebar details.nav-acc .nav-acc-body { display: flex; flex-direction: column; gap: 2px; padding: 4px 0 8px 6px; width: 100%; }
        .vendor-sidebar .brand { padding: 1.25rem 1.25rem; border-bottom: 1px solid var(--side-border); }
        .vendor-sidebar .brand a { font-weight: 700; font-size: 1.05rem; color: var(--side-text-hover); text-decoration: none; }
        .vendor-sidebar .brand .accent { color: var(--side-accent); }
        .vendor-sidebar .nav { flex: 1; min-height: 0; padding: 0.75rem 0.65rem; overflow-y: auto; display: flex; flex-direction: column; gap: 3px; width: 100%; }
        .vendor-sidebar .nav-link {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px;
            color: var(--side-text); text-decoration: none; font-size: 0.875rem; font-weight: 500; border-radius: 8px;
        }
        .vendor-sidebar .nav-link:hover { color: var(--side-text-hover); background: rgba(5, 150, 105, 0.06); }
        .vendor-sidebar .nav-link.active { color: var(--side-active-text); background: var(--side-active-bg); font-weight: 600; }
        .vendor-sidebar .user-footer { padding: 0.75rem 1rem; border-top: 1px solid var(--side-border); }
        .vendor-sidebar .btn-logout {
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
            padding: 8px 12px; font-size: 0.8125rem; font-weight: 500; color: #64748b;
            background: #fff; border: 1px solid var(--side-border); border-radius: 8px; cursor: pointer;
        }
        .vendor-main { flex: 1; margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        .vendor-header {
            height: 60px; background: var(--header-bg); border-bottom: 1px solid var(--side-border);
            display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; position: sticky; top: 0; z-index: 30;
        }
        .vendor-header .page-title { font-size: 1.0625rem; font-weight: 600; }
        .vendor-header .user-menu { display: flex; align-items: center; gap: 1rem; }
        .vendor-header .user-menu a { color: #64748b; font-size: 0.8125rem; text-decoration: none; font-weight: 500; }
        .vendor-header .btn-out-vendor { padding: 4px 10px; font-size: 0.75rem; color: #64748b; background: transparent; border: 1px solid var(--side-border); border-radius: 6px; cursor: pointer; }
        .vendor-content { flex: 1; padding: 1.5rem; }
        .vendor-content .card { border: 1px solid var(--side-border); border-radius: var(--card-radius); box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1030; }
        @media (max-width: 991.98px) {
            .vendor-sidebar { transform: translateX(-100%); }
            .vendor-sidebar.show { transform: translateX(0); }
            .sidebar-backdrop.show { display: block; }
            .vendor-main { margin-left: 0; }
            .vendor-content { padding: 0.75rem !important; }
        }
    </style>
</head>
<body>
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>
<div class="vendor-shell" id="vendorShell">
    <aside class="vendor-sidebar" id="vendorSidebar">
        <div class="brand">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('outdoor-panel.dashboard') }}">Baskı<span class="accent">Yeri</span> <span class="accent">Açık Hava</span></a>
                <button type="button" class="btn-close d-lg-none" onclick="toggleSidebar()" aria-label="Kapat"></button>
            </div>
            @php $sidebarVendor = auth()->user()?->vendor; @endphp
            <div class="mt-2 small">
                <span class="badge bg-success-subtle text-success border">{{ $sidebarVendor?->isOutdoorAgency() ? 'Ajans' : ($sidebarVendor?->isMunicipalityOwner() ? 'Belediye' : 'Mecra sahibi') }}</span>
                @if($sidebarVendor)<span class="badge bg-light text-dark border">#{{ $sidebarVendor->id }}</span>@endif
            </div>
        </div>
        <nav class="nav">
            @include('partials.outdoor-sidebar-nav')
        </nav>
        <div class="user-footer">
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" class="btn-logout w-100">Çıkış Yap</button>
            </form>
        </div>
    </aside>
    <main class="vendor-main">
        <header class="vendor-header">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSidebar()">Menü</button>
                <h1 class="page-title mb-0">@yield('title', 'Panel')</h1>
            </div>
            <div class="user-menu">
            @php
                $hybridLink = false;
                try {
                    $hybridLink = $sidebarVendor && ! $sidebarVendor->prefersOutdoorPanel();
                } catch (\Throwable) {
                }
            @endphp
            @if($hybridLink)
                    <a href="{{ route('vendor.dashboard') }}">Satıcı paneli</a>
                @endif
                <span class="badge bg-light text-dark border">{{ auth()->user()?->publicCode() }}</span>
                <a href="{{ route('home') }}" target="_blank">Site ↗</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf
                    <button type="submit" class="btn-out-vendor">{{ __('panel.logout') }}</button>
                </form>
            </div>
        </header>
        <div class="vendor-content">
            @php
                $moduleInactive = false;
                try {
                    $moduleInactive = $sidebarVendor && ! $sidebarVendor->hasActiveOutdoorModule();
                } catch (\Throwable) {
                }
            @endphp
            @if($moduleInactive)
                <div class="alert alert-warning">Açık hava modülü henüz aktif değil. Hesap → Modüller üzerinden açabilirsiniz.</div>
            @endif
            @if(session('success'))<div class="alert alert-success alert-dismissible fade show small mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if(session('error'))<div class="alert alert-danger alert-dismissible fade show small mb-3">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
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
</script>
@stack('scripts')
</body>
</html>
