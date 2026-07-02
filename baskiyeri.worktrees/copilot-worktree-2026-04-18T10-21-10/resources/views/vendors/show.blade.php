@extends('layouts.app')

@section('title', $vendor->name . ' - Satıcı - BaskıYeri Pazaryeri')

@section('content')
    <div class="content-shell">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center me-3" style="width:56px;height:56px;">
                        {{ mb_substr($vendor->name, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="h5 mb-1">{{ $vendor->name }}</h1>
                        <div class="small text-muted">Pazaryeri satıcısı</div>
                        @if($vendor->rating_average)
                            <div class="small text-warning mt-1">★ {{ number_format($vendor->rating_average, 1) }} · {{ $vendor->reviews_count }} değerlendirme</div>
                        @endif
                    </div>
                </div>
                @if($vendor->businessTypes->isNotEmpty())
                    <div class="mb-3">
                        @foreach($vendor->businessTypes as $bt)
                            <span class="badge bg-warning bg-opacity-25 text-dark me-1 mb-1">{{ $bt->name }}</span>
                        @endforeach
                    </div>
                @endif
                <p class="small text-muted">
                    {{ $vendor->description }}
                </p>
            </div>
        </div>
        <div class="col-md-8">
            <h2 class="h6 mb-3">Bu satıcının ürünleri</h2>
            @if($products->isEmpty())
                <p class="text-muted small">Bu satıcıya ait ürün bulunamadı.</p>
            @else
                <div class="row g-3 g-md-4 mb-3">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4">
                            <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark">
                                <div class="h-100 bg-white rounded-4 shadow-sm overflow-hidden">
                                    <div class="ratio ratio-4x3 bg-light">
                                        @if($product->main_image)
                                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-100 h-100" style="object-fit:cover;">
                                        @else
                                            <img src="https://picsum.photos/400/300?random=satici{{ $product->id }}" alt="{{ $product->name }}" class="w-100 h-100" style="object-fit:cover;">
                                        @endif
                                    </div>
                                    <div class="p-3">
                                        <div class="small text-muted mb-1">
                                            {{ $product->category?->name ?? 'Kategori Yok' }}
                                        </div>
                                        <div class="fw-semibold mb-1">
                                            {{ $product->name }}
                                        </div>
                                        <span class="fw-bold text-danger small">
                                            ₺{{ number_format($product->price, 2, ',', '.') }}
                                        </span>
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

