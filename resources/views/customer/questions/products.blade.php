@extends('layouts.account')
@section('title', 'Ürün sorularım')
@section('content')
<div class="by-card p-5">
    <h1 class="text-xl font-bold mb-4">Ürün sorularım</h1>
    @forelse($questions as $q)
        <div class="rounded-2xl border border-slate-200 p-4 mb-3">
            <div class="text-sm font-semibold">{{ $q->product?->name }}</div>
            <p class="text-sm mt-1">{{ $q->question }}</p>
            @if($q->answer)
                <p class="text-sm mt-2 rounded-xl bg-emerald-50 p-3"><strong>Satıcı:</strong> {{ $q->answer }}</p>
            @else
                <p class="text-xs text-slate-500 mt-2">Yanıt bekleniyor</p>
            @endif
        </div>
    @empty
        <p class="text-sm text-slate-500">Soru yok.</p>
    @endforelse
    {{ $questions->links() }}
</div>
@endsection
