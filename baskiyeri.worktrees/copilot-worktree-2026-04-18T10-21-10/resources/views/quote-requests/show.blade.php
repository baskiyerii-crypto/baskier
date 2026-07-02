@extends('layouts.app')
@section('title', 'Talep #' . $quoteRequest->id)
@section('content')
<div class="container py-5">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
        <h1 class="h5 mb-2">{{ $quoteRequest->title }}</h1>
        <p class="text-muted small mb-0">Kategori: {{ $quoteRequest->category?->name }} | Durum: {{ $quoteRequest->status === 'open' ? 'Açık' : 'Kapalı' }}</p>
        @if($quoteRequest->description)<p class="mt-2 mb-0">{{ $quoteRequest->description }}</p>@endif
    </div>
    <h2 class="h6 mb-3">Gelen teklifler</h2>
    @if($quoteRequest->quotes->isEmpty())
        <p class="text-muted">Henüz teklif gelmedi.</p>
    @else
        <div class="row g-3">
            @foreach($quoteRequest->quotes as $q)
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-4 p-4 h-100">
                        <strong>{{ $q->vendor?->name }}</strong>
                        <p class="mb-1 mt-2">₺{{ number_format($q->amount, 2, ',', '.') }} @if($q->delivery_days)| {{ $q->delivery_days }} gün @endif</p>
                        @if($q->note)<p class="small text-muted mb-2">{{ $q->note }}</p>@endif
                        @if($quoteRequest->status === 'open' && $q->status === 'pending')
                            <form method="POST" action="{{ route('quote-requests.select-quote', [$quoteRequest, $q]) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">Bu teklifi seç</button>
                            </form>
                        @elseif($q->status === 'selected')
                            <span class="badge bg-success">Seçilen teklif</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <a href="{{ route('quote-requests.index') }}" class="btn btn-outline-secondary mt-4">← Taleplerime dön</a>
</div>
@endsection
