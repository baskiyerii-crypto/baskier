<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'BaskıYeri – Matbaa & Reklam Pazaryeri')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --accent: #f97316;
            --accent-soft: #ffedd5;
            --muted: #6b7280;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f9fafb;
            color: #111827;
        }

        .main-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-brand {
            letter-spacing: .06em;
            text-transform: uppercase;
            font-size: .9rem;
            color: #e5e7eb;
        }

        .navbar-brand span { color: var(--accent); }

        .navbar-modern {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(18px);
            background: rgba(255,255,255,0.95);
            border-bottom: 1px solid rgba(209,213,219,0.8);
        }

        .nav-link {
            font-weight: 500;
            color: var(--muted) !important;
        }

        .nav-link.active {
            color: #111827 !important;
        }

        .btn-primary-soft {
            background: var(--accent);
            color: #111827;
            border-radius: 999px;
            padding-inline: 1.5rem;
            border: none;
            font-weight: 600;
        }

        .btn-primary-soft:hover {
            background: #fb923c;
            color: #020617;
        }

        .content-shell {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Anasayfa bölüm başlıkları – büyük, ortalı, modern */
        .section-head {
            text-align: center;
            margin-bottom: 2rem;
        }
        .section-title {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        .section-desc {
            font-size: 1.05rem;
            color: var(--muted);
            max-width: 560px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.5;
        }
        .section-head .section-link {
            font-size: 1rem;
            font-weight: 600;
            color: var(--accent);
            text-decoration: none;
            margin-top: 0.5rem;
            display: inline-block;
        }
        .section-head .section-link:hover { color: #ea580c; }

        /* Aestheta tarzı header: sol Categories+hamburger, ortada logo, sağda ikonlar */
        .header-print {
            padding: 0.875rem 0;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .header-print .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }
        .header-print .header-left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 140px;
        }
        .header-print .header-categories {
            font-weight: 600;
            font-size: 0.95rem;
            color: #111827;
        }
        .header-print .logo-wrap {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        .header-print .logo {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            text-decoration: none;
            letter-spacing: 0.02em;
        }
        .header-print .logo span { color: var(--accent); }
        .header-print .logo .reg {
            font-size: 0.65em;
            vertical-align: super;
            margin-left: 1px;
        }
        .header-print .header-right {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            min-width: 140px;
            justify-content: flex-end;
        }
        .header-print .nav-ico {
            color: #111827;
            text-decoration: none;
            padding: 0.25rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }
        .header-print .nav-ico svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
        }
        .header-print .cart-text {
            font-size: 0.9rem;
            font-weight: 500;
            color: #111827;
            margin-left: 0.25rem;
        }
        .header-print .navbar-toggler {
            padding: 0.25rem;
        }

        /* Tam genişlik hero, header bitişinde başlar – Aestheta tarzı */
        .hero-full {
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            width: 100vw;
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Anasayfa promo kartları: eşit yükseklik, overlay metin, yuvarlak köşe */
        .card-promo {
            min-height: 280px;
            display: block;
            position: relative;
        }
        .card-promo .card-promo-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-promo-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .card-promo-overlay {
            background: linear-gradient(to top, rgba(0,0,0,.2) 0%, transparent 50%);
        }

        /* Footer – modern, açık tema */
        .site-footer {
            background: #fff;
            color: #374151;
            margin-top: auto;
            border-top: 1px solid #e5e7eb;
        }
        .site-footer a {
            color: #4b5563;
            text-decoration: none;
            transition: color .2s;
        }
        .site-footer a:hover {
            color: var(--accent);
        }
        .site-footer .footer-logo {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.35rem;
            font-weight: 600;
            color: #111827;
        }
        .site-footer .footer-logo span { color: var(--accent); }
        .site-footer .footer-tagline {
            font-size: 0.875rem;
            color: var(--muted);
            margin-top: 0.5rem;
        }
        .site-footer .footer-heading {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 1rem;
        }
        .site-footer .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .site-footer .footer-links li { margin-bottom: 0.5rem; }
        .site-footer .footer-bottom {
            border-top: 1px solid #e5e7eb;
            padding-top: 1.25rem;
            margin-top: 2rem;
        }
        .site-footer .footer-bottom .text-muted { color: var(--muted) !important; }
    </style>
</head>
<body>
<div class="main-shell">
    <header class="header-print">
        <div class="header-inner">
            <div class="header-left">
                <span class="header-categories">Kategoriler</span>
                <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-label="Menü">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <div class="logo-wrap">
                <a class="logo" href="{{ route('home') }}">Baskı<span>Yeri</span><span class="reg">®</span></a>
            </div>
            <div class="header-right">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-ico" title="Yönetici Paneli">Yönetim</a>
                    @elseif(auth()->user()->isVendor())
                        <a href="{{ route('vendor.dashboard') }}" class="nav-ico" title="Satıcı Paneli">Satıcı</a>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="nav-ico" title="Hesabım">Hesabım</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="nav-ico" title="Giriş Yap">Giriş</a>
                @endauth
                @auth
                    <a href="{{ route('favorites.index') }}" class="nav-ico" title="Favoriler">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </a>
                    <a href="{{ route('cart.index') }}" class="nav-ico d-flex align-items-center" title="Sepet">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span class="cart-text">Sepet({{ auth()->user()->cartItems()->count() }})</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="nav-ico" title="Favoriler">♡</a>
                    <a href="{{ route('login') }}" class="nav-ico d-flex align-items-center" title="Sepet">
                        <span class="cart-text">Sepet(0)</span>
                    </a>
                @endauth
                <button class="navbar-toggler border-0 p-0 nav-ico" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Menü">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
        <div class="collapse navbar-collapse position-absolute top-100 start-0 w-100 bg-white shadow-sm py-3" id="mainNav" style="left:0;right:0;">
            <div class="content-shell">
                <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('home') }}">Anasayfa</a>
                <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('products.index') }}">Ürünler</a>
                <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('vendors.index') }}">Satıcılar</a>
                <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('freelancer-jobs.index') }}">İş ilanları</a>
                @auth
                    <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('cart.index') }}">Sepet</a>
                    <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('account.orders.index') }}">Siparişlerim</a>
                    @if(auth()->user()->isAdmin())
                        <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('admin.dashboard') }}">Yönetici Paneli</a>
                    @endif
                    @if(auth()->user()->isVendor())
                        <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('vendor.dashboard') }}">Satıcı Paneli</a>
                    @endif
                    @if(auth()->user()->isCustomer())
                        <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('customer.dashboard') }}">Hesabım</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 text-dark text-decoration-none">Çıkış</button>
                    </form>
                @else
                    <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('login') }}">Giriş Yap</a>
                    <a class="d-block py-2 text-decoration-none text-dark" href="{{ route('register') }}">Kayıt Ol</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="pt-0 pb-4 pb-md-5 flex-grow-1">
        @if(session('success'))
            <div class="content-shell px-3 pt-3"><div class="alert alert-success py-2 mb-0">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
            <div class="content-shell px-3 pt-3"><div class="alert alert-danger py-2 mb-0">{{ session('error') }}</div></div>
        @endif
        @if(session('info'))
            <div class="content-shell px-3 pt-3"><div class="alert alert-info py-2 mb-0">{{ session('info') }}</div></div>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer pt-5 pb-4">
        <div class="content-shell px-3 px-md-4">
            <div class="row g-4 g-lg-5">
                <div class="col-lg-4">
                    <a href="{{ route('home') }}" class="footer-logo">Baskı<span>Yeri</span>®</a>
                    <p class="footer-tagline mb-0">
                        Kartvizit, broşür, davetiye, tabela ve tüm baskı işleriniz için tek pazaryeri.
                    </p>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <h3 class="footer-heading">Keşfet</h3>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}">Anasayfa</a></li>
                        <li><a href="{{ route('products.index') }}">Ürünler</a></li>
                        <li><a href="{{ route('vendors.index') }}">Satıcılar</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <h3 class="footer-heading">Hesap</h3>
                    <ul class="footer-links">
                        <li><a href="{{ route('login') }}">Giriş Yap</a></li>
                        <li><a href="{{ route('register') }}">Kayıt Ol</a></li>
                        <li><a href="{{ route('admin.dashboard') }}">Yönetici Paneli</a></li>
                    </ul>
                </div>
                <div class="col-12 col-md-4 col-lg-4">
                    <h3 class="footer-heading">İletişim</h3>
                    <p class="small mb-1">info@baskiyeri.com</p>
                    <p class="small mb-0 text-muted">Türkiye</p>
                </div>
            </div>
            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <p class="small mb-0 text-muted">© {{ date('Y') }} BaskıYeri. Tüm hakları saklıdır.</p>
                <div class="d-flex flex-wrap gap-3 small">
                    <a href="{{ route('pages.privacy') }}" class="text-muted">Gizlilik</a>
                    <a href="{{ route('pages.terms') }}" class="text-muted">Kullanım koşulları</a>
                    <a href="{{ route('pages.about') }}" class="text-muted">Hakkımızda</a>
                </div>
            </div>
        </div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

