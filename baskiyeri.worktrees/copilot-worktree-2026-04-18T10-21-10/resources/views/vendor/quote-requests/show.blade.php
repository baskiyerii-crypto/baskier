@extends('layouts.vendor')

@section('title', 'Talep #' . $quoteRequest->id)

@section('content')
<div class="card p-4 mb-3">
    <h2 class="h6 mb-3">{{ $quoteRequest->title }}</h2>
    <p class="small text-muted mb-1">Kategori: {{ $quoteRequest->category?->name }}</p>
    @if($quoteRequest->description)
        <p class="mb-2">{{ $quoteRequest->description }}</p>
    @endif
    @if($hasPaidMeeting)
        <p class="small mb-1"><strong>Bölge:</strong> {{ $quoteRequest->city ?? '—' }} {{ $quoteRequest->district ?? '' }}</p>
        <p class="small mb-1"><strong>Adres:</strong> {{ $quoteRequest->address ?? '—' }}</p>
        <p class="small mb-2"><strong>İletişim:</strong> {{ $quoteRequest->contact_phone ?? '—' }}</p>
    @endif
</div>

@if(!$hasPaidMeeting)
    <div class="card p-4 mb-3">
        <p class="small mb-2">Talep detayını (adres, iletişim) görmek ve teklif vermek için görüşme ücreti (₺{{ number_format(\App\Models\Setting::meetingFee(), 2, ',', '.') }}) bakiyenizden düşülür.</p>
        <form method="POST" action="{{ route('vendor.quote-requests.accept-meeting', $quoteRequest) }}">
            @csrf
            <button type="submit" class="btn btn-success">Bu talebe gidiyorum / Teklif vereceğim</button>
        </form>
    </div>
@else
    <div class="card p-4">
        <h3 class="h6 mb-3">Teklif ver</h3>
        @if($myQuote)
            <p class="small text-muted">Verdiğiniz teklif: ₺{{ number_format($myQuote->amount, 2, ',', '.') }} @if($myQuote->delivery_days)({{ $myQuote->delivery_days }} gün)@endif</p>
            <p class="small">Güncellemek için aşağıdan yeni değer gönderin.</p>
        @endif
        <form method="POST" action="{{ route('vendor.quote-requests.submit-quote', $quoteRequest) }}">
            @csrf
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small">Tutar (₺)</label>
                    <input type="number" name="amount" step="0.01" min="0" class="form-control form-control-sm" value="{{ old('amount', $myQuote->amount ?? '') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Teslim süresi (gün)</label>
                    <input type="number" name="delivery_days" min="1" class="form-control form-control-sm" value="{{ old('delivery_days', $myQuote->delivery_days ?? '') }}">
                </div>
            </div>
            <div class="mt-2">
                <label class="form-label small">Not</label>
                <textarea name="note" class="form-control form-control-sm" rows="2">{{ old('note', $myQuote->note ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-success btn-sm mt-2">{{ $myQuote ? 'Teklifi güncelle' : 'Teklif gönder' }}</button>
        </form>
    </div>
@endif
<a href="{{ route('vendor.quote-requests.index') }}" class="btn btn-outline-secondary btn-sm mt-3">← Listeye dön</a>
@endsection
