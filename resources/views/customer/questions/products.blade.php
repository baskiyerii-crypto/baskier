@extends('layouts.account')
@section('title', 'Ürün sorularım')
@section('content')
<div class="by-card p-5">
    <h1 class="mb-4 text-xl font-bold">Ürün sorularım</h1>
    @if(session('success'))<p class="mb-3 text-sm text-emerald-700">{{ session('success') }}</p>@endif
    @forelse($questions as $q)
        @php $product = $q->product; @endphp
        <div class="mb-3 rounded-2xl border border-slate-200 p-4">
            <div class="mb-3 flex gap-3">
                @if($product?->displayImageUrl())
                    <img src="{{ $product->displayImageUrl() }}" alt="" class="h-16 w-16 rounded-xl object-cover">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100 text-xs text-slate-400">Ürün</div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="font-semibold">{{ $product?->name ?? 'Ürün' }}</div>
                    @if($product)
                        <a href="{{ route('products.show', $product) }}" class="mt-1 inline-block text-sm font-semibold text-amber-700">Satın al</a>
                    @endif
                </div>
            </div>
            <p class="text-sm">{{ $q->question }}</p>
            @if($q->answer)
                <p class="mt-2 rounded-xl bg-emerald-50 p-3 text-sm"><strong>Satıcı:</strong> {{ $q->answer }}</p>
            @else
                <p class="mt-2 text-xs text-slate-500">Yanıt bekleniyor</p>
            @endif
        </div>
    @empty
        <p class="text-sm text-slate-500">Soru yok.</p>
    @endforelse
    {{ $questions->links() }}
</div>
@endsection
