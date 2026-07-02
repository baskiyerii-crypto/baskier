@extends('layouts.app')

@section('title', 'BaskıYeri – Anasayfa')

@section('content')
    {{-- HERO: Slider – oklarla kayar, referans görseller <img> ile --}}
    <section class="position-relative overflow-hidden hero-full mb-4" style="min-height:70vh;">
        <div id="heroCarousel" class="carousel slide position-absolute top-0 start-0 w-100 h-100" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                <div class="carousel-item active h-100">
                    <img src="https://picsum.photos/1600/900?random=slider1" alt="Baskı" class="d-block w-100 h-100" style="object-fit:cover;">
                </div>
                <div class="carousel-item h-100">
                    <img src="https://picsum.photos/1600/900?random=slider2" alt="Baskı" class="d-block w-100 h-100" style="object-fit:cover;">
                </div>
                <div class="carousel-item h-100">
                    <img src="https://picsum.photos/1600/900?random=slider3" alt="Baskı" class="d-block w-100 h-100" style="object-fit:cover;">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Önceki</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sonraki</span>
            </button>
        </div>

        <div class="position-absolute top-0 start-0 w-100 h-100" style="background:linear-gradient(to bottom, rgba(0,0,0,.4), rgba(0,0,0,.2)); pointer-events:none;"></div>
        <div class="position-absolute top-50 start-50 translate-middle w-100" style="pointer-events:auto;">
            <div class="content-shell">
                <div class="row justify-content-center">
                    <div class="col-lg-9 text-center">
                        <p class="text-uppercase small mb-2 text-white" style="letter-spacing:0.15em;">İhtiyacın olan her şey</p>
                        <h1 class="hero-title mb-4 text-white" style="font-family:Georgia,serif;font-size:clamp(2.2rem,4.5vw,3rem);">
                            En İyi Baskı Kategorilerimizi Keşfet
                        </h1>
                        <form action="{{ route('products.index') }}" class="hero-search mb-4">
                            <div class="d-flex align-items-center bg-white rounded-pill shadow-sm overflow-hidden" style="max-width:640px;margin:0 auto;">
                                <select name="category" class="form-select border-0 bg-transparent text-muted px-3" style="max-width:190px;">
                                    <option value="">Tüm kategoriler</option>
                                    @foreach($featuredCategories as $category)
                                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="q" class="form-control border-0 flex-grow-1" placeholder="Ürün veya kategori ara…" value="{{ request('q') }}">
                                <button type="submit" class="btn btn-link text-dark px-4" aria-label="Ara">
                                    <span class="fs-5">🔍</span>
                                </button>
                            </div>
                        </form>
                        <p class="small mb-0 text-white-50">
                            Kartvizit, broşür, davetiye, tabela, branda ve tüm baskı işleriniz için tek adres.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Üçlü promo kartlar: <img> ile referans görseller – hepsi farklı --}}
    <section class="mb-5 mt-2">
        <div class="content-shell">
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="{{ route('products.index') }}" class="card-promo rounded-4 overflow-hidden position-relative d-block text-decoration-none text-dark">
                        <img src="https://picsum.photos/800/500?random=kart1" alt="Kartvizit Broşür" class="card-promo-img w-100 h-100" style="object-fit:cover;">
                        <div class="card-promo-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-between p-4">
                            <p class="text-uppercase mb-0 fw-semibold" style="letter-spacing:0.12em; font-size:0.9rem;">Özel Koleksiyon</p>
                            <div>
                                <p class="h4 fw-bold mb-2">Kartvizit & Broşür</p>
                                <span class="btn btn-outline-dark rounded-pill px-3">Hemen incele</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('products.index') }}" class="card-promo rounded-4 overflow-hidden position-relative d-block text-decoration-none text-dark">
                        <img src="https://picsum.photos/400/500?random=kart2" alt="Kampanya" class="card-promo-img w-100 h-100" style="object-fit:cover;">
                        <div class="card-promo-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-between p-4">
                            <p class="text-uppercase mb-0 fw-semibold text-white" style="letter-spacing:0.12em; font-size:0.9rem;">Kampanya</p>
                            <div>
                                <p class="h3 fw-bold text-white mb-1">%34</p>
                                <p class="mb-2 text-white" style="font-size:1rem;">Baskı indirimi</p>
                                <span class="btn btn-outline-light rounded-pill px-3">Hemen incele</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('products.index') }}" class="card-promo rounded-4 overflow-hidden position-relative d-block text-decoration-none text-dark">
                        <img src="https://picsum.photos/400/500?random=kart3" alt="Tasarım" class="card-promo-img w-100 h-100" style="object-fit:cover;">
                        <div class="card-promo-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-between p-4">
                            <p class="text-uppercase mb-0 fw-semibold" style="letter-spacing:0.12em; font-size:0.9rem;">Tasarım</p>
                            <div>
                                <p class="h5 fw-bold mb-2">Davetiye & Katalog</p>
                                <span class="btn btn-outline-dark rounded-pill px-3">Hemen incele</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Teklif al – modern, göze batan CTA kartı --}}
    <section class="mb-5">
        <div class="content-shell">
            <a href="{{ route('quote-requests.create') }}" class="teklif-cta-card text-decoration-none d-block rounded-4 overflow-hidden shadow-lg position-relative border-0" style="transition: transform .25s ease, box-shadow .25s ease;">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-6 order-lg-2 position-relative bg-dark">
                        <img src="https://picsum.photos/800/500?random=teklif" alt="" class="w-100 h-100 d-none d-lg-block opacity-90" style="object-fit:cover; min-height:280px;">
                        <div class="d-lg-none">
                            <div class="ratio ratio-21x9">
                                <img src="https://picsum.photos/800/340?random=teklif" alt="" class="w-100 h-100 opacity-90" style="object-fit:cover;">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 order-lg-1 d-flex align-items-center bg-white">
                        <div class="p-4 p-md-5 py-5 text-center text-lg-start w-100">
                            <span class="d-inline-block text-uppercase small fw-bold text-warning mb-2" style="letter-spacing:0.2em;">Tabela · Folyo · Branda</span>
                            <h2 class="h3 fw-bold text-dark mb-2" style="line-height:1.2;">İhtiyacınız olan işe<br><span class="text-warning">teklif alın</span></h2>
                            <p class="text-muted mb-4 small">Ne istediğinizi yazın, satıcılar size teklif versin. Üye olmadan formu doldurabilirsiniz.</p>
                            <span class="btn btn-warning rounded-pill px-4 py-3 fw-semibold shadow-sm">
                                Teklif al →
                            </span>
                        </div>
                    </div>
                </div>
            </a>
            <style>
                .teklif-cta-card:hover { transform: translateY(-6px); box-shadow: 0 24px 48px rgba(0,0,0,.12), 0 0 0 1px rgba(249,115,22,.15) !important; }
                .teklif-cta-card .btn-warning { font-size: 1.05rem; }
                .teklif-cta-card .btn-warning:hover { background: #ea580c !important; color: #fff !important; transform: scale(1.02); }
            </style>
        </div>
    </section>

    {{-- Dijital ürünler – her zaman görünsün, görselli kartlar --}}
    <section class="mb-5">
        <div class="content-shell">
            <div class="section-head">
                <h2 class="section-title">Dijital ürünler</h2>
                <p class="section-desc mb-0">Şablonlar, grafik paketleri, dijital dosyalar – anında indir, hemen kullan.</p>
                <a href="{{ route('products.index', ['type' => 'digital']) }}" class="section-link">Tümünü gör →</a>
            </div>
            @if(isset($digitalProducts) && $digitalProducts->isNotEmpty())
                <div class="row g-3 g-md-4">
                    @foreach($digitalProducts as $product)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark">
                                <div class="bg-white border rounded-4 h-100 d-flex flex-column overflow-hidden shadow-sm">
                                    <div class="ratio ratio-4x3 rounded-top-4 bg-light overflow-hidden">
                                        @if($product->main_image)
                                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-100 h-100" style="object-fit:cover;">
                                        @else
                                            <img src="https://picsum.photos/600/450?random=digital{{ $product->id }}" alt="{{ $product->name }}" class="w-100 h-100" style="object-fit:cover;">
                                        @endif
                                    </div>
                                    <div class="p-3 flex-grow-1">
                                        <span class="badge bg-warning text-dark mb-2">Dijital</span>
                                        <div class="fw-semibold mb-1" style="font-size:1rem;">{{ Str::limit($product->name, 35) }}</div>
                                        <div class="text-muted" style="font-size:0.95rem;">{{ $product->vendor?->name }}</div>
                                        <div class="fw-bold text-warning mt-2" style="font-size:1.1rem;">₺{{ number_format($product->price, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-4 overflow-hidden border bg-white shadow-sm">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-5">
                            <img src="https://picsum.photos/600/400?random=dijital" alt="Dijital ürünler" class="w-100 h-100" style="object-fit:cover; min-height:220px;">
                        </div>
                        <div class="col-md-7 p-4 p-md-5 text-center text-md-start d-flex flex-column justify-content-center">
                            <h3 class="h5 fw-bold mb-2">Dijital ürünler</h3>
                            <p class="text-muted mb-3" style="font-size:1rem;">Şablonlar, grafik paketleri ve dijital dosyalar yakında eklenecek.</p>
                            <a href="{{ route('products.index', ['type' => 'digital']) }}" class="btn btn-outline-warning rounded-pill px-4">Dijital ürünlere göz at</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Freelancer / İş yapanlar (Armut tarzı) – hizmet türü kartları, her birinde Teklif al --}}
    <section class="mb-5">
        <div class="content-shell">
            <div class="section-head">
                <h2 class="section-title">İş yapanlar & Freelancer</h2>
                <p class="section-desc mb-0">Tasarım, baskı, web, tabela – ihtiyacınız olan işi seçin, teklif alın.</p>
                <a href="{{ route('freelancer-jobs.index') }}" class="section-link">Tüm ilanlar →</a>
            </div>

            <div class="row g-3 g-md-4">
                @foreach($freelancerCategories ?? [] as $cat)
                    <div class="col-6 col-md-4 col-lg">
                        <div class="bg-white border rounded-4 overflow-hidden h-100 shadow-sm d-flex flex-column" style="transition: transform .2s, box-shadow .2s;">
                            <a href="{{ route('freelancer-jobs.index', ['category' => $cat['key']]) }}" class="text-decoration-none text-dark flex-grow-1 d-flex flex-column">
                                <div class="ratio ratio-4x3 bg-light overflow-hidden">
                                    <img src="https://picsum.photos/600/450?random={{ $cat['seed'] }}" alt="{{ $cat['label'] }}" class="w-100 h-100" style="object-fit:cover;">
                                </div>
                                <div class="p-3 flex-grow-1">
                                    <h3 class="mb-2" style="font-size:1.05rem; font-weight:600;">{{ $cat['label'] }}</h3>
                                    <p class="text-muted mb-0" style="font-size:0.95rem;">
                                        <span class="d-inline-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                                            {{ $cat['count'] }} açık ilan
                                        </span>
                                    </p>
                                </div>
                            </a>
                            <div class="p-3 pt-0">
                                <a href="{{ route('quote-requests.create', ['category' => $cat['key']]) }}" class="btn btn-warning rounded-pill w-100 btn-sm">Teklif al</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(isset($freelancerJobs) && $freelancerJobs->isNotEmpty())
                <div class="mt-4 pt-4 border-top">
                    <p class="fw-semibold text-muted mb-3" style="font-size:1.05rem;">Son eklenen ilanlar</p>
                    <div class="row g-2 g-md-3">
                        @foreach($freelancerJobs->take(3) as $job)
                            <div class="col-12 col-md-4">
                                <a href="{{ route('freelancer-jobs.show', $job) }}" class="text-decoration-none text-dark d-flex align-items-center gap-3 bg-white border rounded-3 p-3 shadow-sm">
                                    <img src="https://picsum.photos/120/80?random=job{{ $job->id }}" alt="" class="rounded-2 flex-shrink-0" style="width:80px;height:56px;object-fit:cover;">
                                    <div class="min-w-0">
                                        <span class="fw-semibold d-block" style="font-size:1rem;">{{ Str::limit($job->title, 40) }}</span>
                                        <span class="text-muted" style="font-size:0.95rem;">{{ $job->category }} · ₺{{ $job->budget_min ? number_format($job->budget_min, 0, ',', '.') : '?' }}+</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Tüm kategorilerin kartları (ürün kartlarının üstünde) --}}
    <section class="mb-5">
        <div class="content-shell">
            <div class="section-head">
                <h2 class="section-title">Kategoriler</h2>
                <p class="section-desc mb-0">Baskı ve reklam ürünlerinde aradığınız kategoriye göz atın.</p>
            </div>
            @if($categories->isEmpty())
                <p class="text-muted text-center">Kategori bulunamadı.</p>
            @else
                <div class="row g-3">
                    @foreach($categories as $category)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="text-decoration-none text-dark d-block rounded-4 overflow-hidden border bg-white shadow-sm" style="transition:transform .2s;">
                                <div class="ratio ratio-4x3 bg-light">
                                    <img src="https://picsum.photos/400/300?random=kategori{{ $category->id }}" alt="{{ $category->name }}" class="w-100 h-100" style="object-fit:cover;">
                                </div>
                                <div class="p-3 text-center">
                                    <span class="fw-semibold" style="font-size:1rem;">{{ $category->name }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Ürün grid: new collection (50 ürün) – en altta --}}
    <section class="mb-5">
        <div class="content-shell">
            <div class="section-head">
                <h2 class="section-title">Yeni koleksiyon ürünleri</h2>
                <p class="section-desc mb-0">En güncel baskı ve reklam ürünleri.</p>
                <a href="{{ route('products.index') }}" class="section-link">Tüm ürünleri gör →</a>
            </div>

        @if($featuredProducts->isEmpty())
            <div class="alert alert-light border">
                Henüz ürün yok.
            </div>
        @else
            <div class="row g-3 g-md-4">
                @foreach($featuredProducts as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark">
                            <div class="bg-white border rounded-4 h-100 d-flex flex-column overflow-hidden shadow-sm">
                                <div class="ratio ratio-4x3 rounded-top-4 bg-light">
                                    @if($product->main_image)
                                        <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-100 h-100 rounded-top-4" style="object-fit:cover;">
                                    @else
                                        <img src="https://picsum.photos/600/450?random=urun{{ $product->id ?? $loop->index }}" alt="{{ $product->name }}" class="w-100 h-100 rounded-top-4" style="object-fit:cover;">
                                    @endif
                                </div>
                                <div class="p-3 flex-grow-1 d-flex flex-column">
                                    <div class="text-muted mb-1" style="font-size:0.95rem;">
                                        {{ $product->vendor?->name ?? 'Satıcı' }}
                                    </div>
                                    <div class="fw-semibold mb-1" style="font-size:1rem;">
                                        {{ $product->name }}
                                    </div>
                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-warning" style="font-size:1.1rem;">
                                            ₺{{ number_format($product->price, 2, ',', '.') }}
                                        </span>
                                        <span class="text-muted" style="font-size:0.9rem;">Sepete ekle</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
        </div>
    </section>

@endsection

