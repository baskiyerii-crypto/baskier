@extends('layouts.app')

@section('title', 'Ürünler - BaskıYeri Pazaryeri')

@section('content')
    <div class="content-shell">
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="bg-white rounded-4 shadow-sm h-100 overflow-hidden">
                <img src="https://picsum.photos/400/220?random=kategoriler" alt="Kategoriler" class="w-100 rounded-top-4" style="height:180px;object-fit:cover;">
                <div class="p-3 p-md-4">
                <h2 class="h6 mb-3">Kategoriler</h2>
                @if($categories->isEmpty())
                    <p class="text-muted small mb-0">Kategori bulunamadı.</p>
                @else
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <a href="{{ route('products.index') }}"
                               class="text-decoration-none d-flex align-items-center gap-2 {{ request('category') ? 'text-muted' : 'fw-semibold text-danger' }}">
                                <img src="https://picsum.photos/64/64?random=tumu" alt="" class="rounded-3 flex-shrink-0" style="width:32px;height:32px;object-fit:cover;">
                                <span>Tümü</span>
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li class="mb-2">
                                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                                   class="text-decoration-none d-flex align-items-center gap-2 {{ request('category') === $category->slug ? 'fw-semibold text-danger' : 'text-muted' }}">
                                    <img src="https://picsum.photos/64/64?random=cat{{ $loop->index }}" alt="" class="rounded-3 flex-shrink-0" style="width:32px;height:32px;object-fit:cover;">
                                    <span>{{ $category->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h5 mb-0">{{ request('type') === 'digital' ? 'Dijital ürünler' : 'Ürünler' }}</h1>
                @if(request('q'))
                    <span class="small text-muted">"{{ request('q') }}" için sonuçlar</span>
                @endif
                @if(request('type') === 'digital')
                    <a href="{{ route('products.index') }}" class="small text-decoration-none text-muted">Tüm ürünler</a>
                @endif
            </div>

            @if($products->isEmpty())
                <div class="alert alert-light border">
                    Ürün bulunamadı.
                </div>
            @else
                <div class="row g-3 g-md-4 mb-3">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4">
                            <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark">
                                <div class="h-100 rounded-4 overflow-hidden" style="border:1px solid rgba(15,23,42,0.06);background:#fff;transition:all .2s;">
                                    <div class="ratio ratio-4x3 bg-light">
                                        @if($product->main_image)
                                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-100 h-100" style="object-fit:cover;">
                                        @else
                                            <img src="https://picsum.photos/500/400?random=liste{{ $product->id ?? $loop->index }}" alt="{{ $product->name }}" class="w-100 h-100" style="object-fit:cover;">
                                        @endif
                                    </div>
                                    <div class="p-3">
                                        <div class="small text-muted mb-1">
                                            {{ $product->category?->name ?? 'Kategori Yok' }}
                                        </div>
                                        <div class="fw-semibold mb-1">
                                            {{ $product->name }}
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-danger">
                                                ₺{{ number_format($product->price, 2, ',', '.') }}
                                            </span>
                                            @if($product->vendor)
                                                <span class="badge bg-light text-muted">
                                                    {{ $product->vendor->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                {{ $products->links() }}
            @endif
        </div>
    </div>
    </div>
@endsection

