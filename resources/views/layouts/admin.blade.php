<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Yönetim') – BaskıYeri Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --side-bg: #f8fafc;
            --side-border: #e2e8f0;
            --side-text: #475569;
            --side-text-hover: #0f172a;
            --side-active-bg: #eff6ff;
            --side-active-text: #1d4ed8;
            --side-accent: #6366f1;
            --header-bg: #ffffff;
            --content-bg: #f1f5f9;
            --card-radius: 14px;
            --nav-gap: 4px;
        }
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--content-bg); color: #0f172a; min-height: 100vh; }
        .admin-shell { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 280px;
            background: var(--side-bg);
            border-right: 1px solid var(--side-border);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 40;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease;
        }
        .admin-sidebar.is-collapsed { transform: translateX(-100%); }
        .admin-main { flex: 1; margin-left: 280px; min-height: 100vh; display: flex; flex-direction: column; transition: margin-left 0.2s ease; }
        .admin-shell.nav-collapsed .admin-main { margin-left: 0; }
        .admin-sidebar details.nav-acc { border-radius: 10px; }
        .admin-sidebar details.nav-acc > summary {
            list-style: none; cursor: pointer; padding: 10px 14px; color: var(--side-text); font-weight: 600; font-size: 0.875rem;
            border-radius: 10px; display: flex; justify-content: space-between; align-items: center;
        }
        .admin-sidebar details.nav-acc > summary::-webkit-details-marker { display: none; }
        .admin-sidebar details.nav-acc[open] > summary { background: rgba(99,102,241,.08); color: var(--side-active-text); }
        .admin-sidebar details.nav-acc .nav-acc-body { display: flex; flex-direction: column; gap: 2px; padding: 4px 0 8px 8px; }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.35); z-index: 35; }
        .sidebar-backdrop.show { display: block; }
        @media (max-width: 991.98px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.is-open { transform: translateX(0); }
            .admin-main { margin-left: 0; }
        }
        .metric-card, .card { transition: transform .15s ease, box-shadow .15s ease; }
        .metric-card:hover, .card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(15,23,42,.08) !important; }
        .admin-sidebar .brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid var(--side-border);
        }
        .admin-sidebar .brand a {
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--side-text-hover);
            text-decoration: none;
            letter-spacing: -0.02em;
        }
        .admin-sidebar .brand .accent { color: var(--side-accent); }
        .admin-sidebar .nav {
            flex: 1;
            padding: 1rem 0.75rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: var(--nav-gap);
        }
        .admin-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: var(--side-text);
            text-decoration: none;
            font-size: 0.9375rem;
            font-weight: 500;
            border-radius: 10px;
            transition: background 0.15s, color 0.15s;
        }
        .admin-sidebar .nav-link:hover {
            color: var(--side-text-hover);
            background: rgba(99, 102, 241, 0.06);
        }
        .admin-sidebar .nav-link.active {
            color: var(--side-active-text);
            background: var(--side-active-bg);
        }
        .admin-sidebar .nav-link svg {
            flex-shrink: 0;
            opacity: 0.85;
        }
        .admin-sidebar .nav-link.active svg { opacity: 1; }
        .admin-sidebar .user-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--side-border);
        }
        .admin-sidebar .btn-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px 14px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            background: #fff;
            border: 1px solid var(--side-border);
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
        }
        .admin-sidebar .btn-logout:hover {
            color: #dc2626;
            background: #fef2f2;
            border-color: #fecaca;
        }
        .admin-header {
            height: 64px;
            background: var(--header-bg);
            border-bottom: 1px solid var(--side-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .admin-header .page-title { font-size: 1.0625rem; font-weight: 600; color: #0f172a; }
        .admin-header .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .admin-header .user-menu .sep { width: 1px; height: 20px; background: var(--side-border); }
        .admin-header .user-menu a {
            color: #64748b;
            font-size: 0.875rem;
            text-decoration: none;
            font-weight: 500;
        }
        .admin-header .user-menu a:hover { color: var(--side-accent); }
        .admin-header .btn-out-admin {
            padding: 6px 12px;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #64748b;
            background: transparent;
            border: 1px solid var(--side-border);
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .admin-header .btn-out-admin:hover {
            color: #dc2626;
            background: #fef2f2;
            border-color: #fecaca;
        }
        .admin-nav-toggle {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 10px; border: 1px solid var(--side-border);
            background: #fff; color: #475569; cursor: pointer;
        }
        .admin-content { flex: 1; padding: 1.5rem; }
        .admin-content .card {
            border: 1px solid var(--side-border);
            border-radius: var(--card-radius);
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        }
    </style>
</head>
<body class="min-h-screen">
<div class="sidebar-backdrop" id="adminSidebarBackdrop" onclick="toggleAdminNav()"></div>
<div class="admin-shell" id="adminShell">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="brand d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.dashboard') }}">Baskı<span class="accent">Yeri</span> <span class="accent">Admin</span></a>
            <button type="button" class="btn-close d-lg-none" onclick="toggleAdminNav()" aria-label="Kapat"></button>
        </div>
        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span>{{ __('panel.nav_dashboard') }}</span>
            </a>
            <details class="nav-acc" @if(request()->routeIs('admin.vendors.*','admin.customers.*','admin.vendor-updates.*','admin.vendor-category-requests.*')) open @endif>
                <summary>{{ __('panel.nav_people') }} <span>▾</span></summary>
                <div class="nav-acc-body">
                    <a href="{{ route('admin.vendors.index') }}" class="nav-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}"><span>{{ __('panel.nav_vendors') }}</span></a>
                    <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"><span>{{ __('panel.nav_customers') }}</span></a>
                    <a href="{{ route('admin.vendor-updates.index') }}" class="nav-link {{ request()->routeIs('admin.vendor-updates.*') ? 'active' : '' }}"><span>{{ __('panel.nav_vendor_updates') }}</span></a>
                    @if(Route::has('admin.vendor-category-requests.index'))
                    <a href="{{ route('admin.vendor-category-requests.index') }}" class="nav-link {{ request()->routeIs('admin.vendor-category-requests.*') ? 'active' : '' }}"><span>{{ __('panel.nav_category_requests') }}</span></a>
                    @endif
                </div>
            </details>
            <details class="nav-acc" @if(request()->routeIs('admin.categories.*','admin.business-types.*','admin.products.*','admin.blog.*')) open @endif>
                <summary>{{ __('panel.nav_catalog') }} <span>▾</span></summary>
                <div class="nav-acc-body">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><span>{{ __('panel.nav_categories') }}</span></a>
                    <a href="{{ route('admin.business-types.index') }}" class="nav-link {{ request()->routeIs('admin.business-types.*') ? 'active' : '' }}"><span>{{ __('panel.nav_business_types') }}</span></a>
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><span>{{ __('panel.nav_products') }}</span></a>
                    @if(Route::has('admin.blog.index'))
                    <a href="{{ route('admin.blog.index') }}" class="nav-link {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}"><span>{{ __('panel.nav_blog') }}</span></a>
                    @endif
                </div>
            </details>
            <details class="nav-acc" @if(request()->routeIs('admin.finance.*','admin.payouts.*','admin.contracts.*','admin.settings.*','admin.api-management.*')) open @endif>
                <summary>{{ __('panel.nav_ops') }} <span>▾</span></summary>
                <div class="nav-acc-body">
                    @if(Route::has('admin.finance.index'))
                    <a href="{{ route('admin.finance.index') }}" class="nav-link {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}"><span>{{ __('panel.nav_finance') }}</span></a>
                    @endif
                    <a href="{{ route('admin.payouts.index') }}" class="nav-link {{ request()->routeIs('admin.payouts.*') ? 'active' : '' }}"><span>{{ __('panel.nav_payouts') }}</span></a>
                    <a href="{{ route('admin.contracts.index') }}" class="nav-link {{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}"><span>{{ __('panel.nav_contracts') }}</span></a>
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><span>{{ __('panel.nav_settings') }}</span></a>
                    @if(Route::has('admin.api-management.index'))
                    <a href="{{ route('admin.api-management.index') }}" class="nav-link {{ request()->routeIs('admin.api-management.*') ? 'active' : '' }}"><span>{{ __('panel.nav_api') }}</span></a>
                    @endif
                </div>
            </details>
        </nav>
        <div class="user-footer">
            <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                @csrf
                <button type="submit" class="btn-logout w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>{{ __('panel.logout') }}</span>
                </button>
            </form>
        </div>
    </aside>
    <main class="admin-main">
        <header class="admin-header">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="admin-nav-toggle" onclick="toggleAdminNav()" aria-label="{{ __('panel.toggle_nav') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <h1 class="page-title mb-0">@yield('title', 'Yönetim')</h1>
            </div>
            <div class="user-menu">
                @include('partials.locale-switcher')
                <span class="sep"></span>
                <span class="text-muted small">{{ auth()->user()?->publicCode() }}</span>
                <a href="{{ route('home') }}" target="_blank">{{ __('panel.view_site') }}</a>
                <span class="sep"></span>
                <span class="text-muted small">{{ auth()->user()->name ?? '' }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-out-admin">{{ __('panel.logout') }}</button>
                </form>
            </div>
        </header>
        <div class="admin-content">
            @if(session('success'))<div class="alert alert-success alert-dismissible fade show small" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if(session('error'))<div class="alert alert-danger alert-dismissible fade show small" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @yield('content')
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleAdminNav() {
        const sidebar = document.getElementById('adminSidebar');
        const shell = document.getElementById('adminShell');
        const backdrop = document.getElementById('adminSidebarBackdrop');
        const isMobile = window.matchMedia('(max-width: 991.98px)').matches;
        if (isMobile) {
            sidebar.classList.toggle('is-open');
            backdrop.classList.toggle('show');
        } else {
            sidebar.classList.toggle('is-collapsed');
            shell.classList.toggle('nav-collapsed');
        }
    }
</script>
@stack('scripts')
@include('partials.pwa-install')
</body>
</html>
