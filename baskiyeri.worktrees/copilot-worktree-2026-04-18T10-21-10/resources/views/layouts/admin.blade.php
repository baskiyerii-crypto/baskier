<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Yönetim') – BaskıYeri Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
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
        }
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
        .admin-main {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        .admin-content { flex: 1; padding: 1.5rem; }
        .admin-content .card {
            border: 1px solid var(--side-border);
            border-radius: var(--card-radius);
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        @media (max-width: 991.98px) {
            .admin-sidebar { width: 72px; }
            .admin-sidebar .brand span:not(.accent), .admin-sidebar .nav-link span { display: none; }
            .admin-sidebar .nav-link { justify-content: center; padding: 12px; }
            .admin-sidebar .user-footer .btn-logout span { display: none; }
            .admin-main { margin-left: 72px; }
        }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="brand">
            <a href="{{ route('admin.dashboard') }}">Baskı<span class="accent">Yeri</span> <span class="accent">Admin</span></a>
        </div>
        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                <span>Kategoriler</span>
            </a>
            <a href="{{ route('admin.business-types.index') }}" class="nav-link {{ request()->routeIs('admin.business-types.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                <span>İş kolları</span>
            </a>
            <a href="{{ route('admin.vendors.index') }}" class="nav-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Satıcılar</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>Ürünler</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span>Ayarlar</span>
            </a>
            <a href="{{ route('admin.payouts.index') }}" class="nav-link {{ request()->routeIs('admin.payouts.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Hakedişler</span>
            </a>
        </nav>
        <div class="user-footer">
            <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                @csrf
                <button type="submit" class="btn-logout w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>Çıkış yap</span>
                </button>
            </form>
        </div>
    </aside>
    <main class="admin-main">
        <header class="admin-header">
            <h1 class="page-title mb-0">@yield('title', 'Yönetim')</h1>
            <div class="user-menu">
                <a href="{{ route('home') }}" target="_blank">Siteyi görüntüle</a>
                <span class="sep"></span>
                <span class="text-muted small">{{ auth()->user()->name ?? '' }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-out-admin">Çıkış</button>
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
</body>
</html>
