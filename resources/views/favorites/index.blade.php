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
                            @if($p->main_image)
                                <img src="{{ asset('storage/'.$p->main_image) }}" alt="" class="w-100 h-100" style="object-fit:cover;">
                            @else
                                <img src="https://picsum.photos/400/300?random=fav{{ $p->id }}" alt="" class="w-100 h-100" style="object-fit:cover;">
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
