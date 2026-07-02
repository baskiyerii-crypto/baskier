@extends('layouts.app')

@section('title', $product->name . ' - BaskıYeri Pazaryeri')

@section('content')
    <div class="content-shell">
    <div class="row g-4">
        <div class="col-md-5">
            <div class="rounded-4 shadow-sm bg-white overflow-hidden">
                <div class="ratio ratio-4x3 bg-light">
                    @if($product->main_image)
                        <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-100 h-100 rounded-4" style="object-fit:cover;">
                    @else
                        <img src="https://picsum.photos/800/600?random=detay{{ $product->id }}" alt="{{ $product->name }}" class="w-100 h-100 rounded-4" style="object-fit:cover;">
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="bg-white rounded-4 shadow-sm p-4">
                <div class="small text-muted mb-1">
                    {{ $product->category?->name ?? 'Kategori Yok' }}
                </div>
                <h1 class="h4 mb-2">{{ $product->name }}</h1>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="h4 mb-0 text-danger">
                        ₺{{ number_format($product->price, 2, ',', '.') }}
                    </span>
                    @if($product->stock > 0)
                        <span class="badge bg-success-subtle text-success">Stokta var</span>
                    @else
                        <span class="badge bg-secondary">Stokta yok</span>
                    @endif
                </div>

                @if($product->vendor)
                    <div class="border rounded-4 p-3 d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="small text-muted mb-1">Satıcı</div>
                            <a href="{{ route('vendors.show', $product->vendor->slug) }}" class="fw-semibold text-decoration-none text-dark">
                                {{ $product->vendor->name }}
                            </a>
                        </div>
                        <a href="{{ route('vendors.show', $product->vendor->slug) }}" class="btn btn-outline-dark btn-sm">
                            Satıcı profilini gör
                        </a>
                    </div>
                @endif

                <p class="text-muted">
                    {{ $product->short_description }}
                </p>

                @auth
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @if($product->stock > 0)
                            <form action="{{ route('cart.add', $product) }}" method="post" class="d-flex gap-2 align-items-center">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-warning rounded-pill px-4">Sepete ekle</button>
                            </form>
                        @endif
                        <form action="{{ route('favorites.toggle', $product) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary rounded-pill">
                                {{ $isFavorited ? '♥ Favoride' : '♡ Favorilere ekle' }}
                            </button>
                        </form>
                        <a href="{{ route('checkout.index') }}" class="btn btn-dark rounded-pill">Ödemeye geç</a>
                    </div>
                @else
                    <p class="small text-muted mt-3 mb-0">Sepete eklemek için <a href="{{ route('login') }}">giriş yapın</a>.</p>
                @endauth
            </div>
        </div>
    </div>

    @if($related->isNotEmpty())
        <hr class="my-4">
        <h2 class="h6 mb-3">Benzer ürünler</h2>
        <div class="row g-3">
            @foreach($related as $item)
                <div class="col-6 col-md-3">
                    <a href="{{ route('products.show', $item->slug) }}" class="text-decoration-none text-dark">
                        <div class="rounded-4 shadow-sm bg-white h-100 overflow-hidden">
                            <div class="ratio ratio-4x3 bg-light">
                                @if($item->main_image)
                                    <img src="{{ asset('storage/'.$item->main_image) }}" alt="{{ $item->name }}" class="w-100 h-100" style="object-fit:cover;">
                                @else
                                    <img src="https://picsum.photos/400/300?random=benzer{{ $item->id }}" alt="{{ $item->name }}" class="w-100 h-100" style="object-fit:cover;">
                                @endif
                            </div>
                            <div class="p-2">
                                <div class="small fw-semibold">
                                    {{ $item->name }}
                                </div>
                                <span class="small text-danger fw-bold">
                                    ₺{{ number_format($item->price, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
    </div>
@endsection

