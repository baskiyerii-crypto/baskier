@extends('layouts.account')

@section('title', 'Favorilerim')

@section('content')
    <h1 class="h5 mb-4">Favorilerim</h1>
    <div class="row g-3">
        @forelse($products as $p)
            <div class="col-6 col-md-3">
                <a href="{{ route('products.show', $p->slug) }}" class="text-decoration-none text-dark">
                    <div class="bg-white rounded-4 shadow-sm h-100 overflow-hidden">
                        <div class="ratio ratio-4x3 bg-light">
                            @if($p->displayImageUrl())
                                <img src="{{ $p->displayImageUrl() }}" alt="" class="w-100 h-100" style="object-fit:cover;">
                            @else
                                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="opacity-50 mb-1"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span style="font-size:0.7rem;">Görsel yok</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-2">
                            <div class="small fw-semibold">{{ Str::limit($p->name, 40) }}</div>
                            <div class="small text-danger fw-bold">₺{{ number_format($p->price, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <p class="text-muted">Favori ürün yok.</p>
        @endforelse
    </div>
    <div class="mt-3">{{ $products->links() }}</div>
@endsection
