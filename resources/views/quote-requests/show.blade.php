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

    @if($quoteRequest->items && $quoteRequest->items->isNotEmpty())
        <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
            <h2 class="h6 mb-3">İş kalemleri</h2>
            <div class="row g-3">
                @foreach($quoteRequest->items as $it)
                    <div class="col-12">
                        <div class="rounded-4 border p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $it->product?->name ?? $it->category?->name ?? 'Kalem' }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $it->category?->name }}
                                        @if($it->quantity)
                                            · {{ $it->quantity }}{{ $it->unit ? ' ' . $it->unit : ' adet' }}
                                        @endif
                                    </div>
                                </div>
                                @if($it->files && $it->files->isNotEmpty())
                                    <span class="badge">{{ $it->files->count() }} dosya</span>
                                @endif
                            </div>
                            @if($it->spec)
                                <div class="small mt-2">{{ $it->spec }}</div>
                            @endif
                            @if($it->files && $it->files->isNotEmpty())
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach($it->files as $f)
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ asset('storage/'.$f->path) }}" target="_blank" rel="noopener">
                                            {{ \Illuminate\Support\Str::limit($f->original_name ?? basename($f->path), 28) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <h2 class="h6 mb-3">Gelen teklifler</h2>
    @if($quoteRequest->quotes->isEmpty())
        <p class="text-muted">Henüz teklif gelmedi.</p>
    @else
        <div class="row g-3">
            @foreach($quoteRequest->quotes as $q)
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-4 p-4 h-100">
                        <strong>{{ $q->vendor?->name }}</strong>
                        @if($q->vendor)
                            <a href="{{ route('vendors.show', $q->vendor->slug) }}" class="small ms-2">Mağaza profili</a>
                        @endif
                        <p class="mb-1 mt-2">₺{{ number_format($q->amount, 2, ',', '.') }} @if($q->delivery_days)| Termin: {{ $q->delivery_days }} gün @endif</p>
                        @if($q->note)<p class="small text-muted mb-2">{{ $q->note }}</p>@endif
                        @if($quoteRequest->status === 'open' && $q->status === 'pending')
                            <form method="POST" action="{{ route('quote-requests.select-quote', [$quoteRequest, $q]) }}" class="mt-2">
                                @csrf
                                <label class="d-flex gap-2 small mb-1"><input type="checkbox" name="share_my_contact" value="1" required> {{ __('panel.consent_share_my_contact') }}</label>
                                <label class="d-flex gap-2 small mb-2"><input type="checkbox" name="accept_vendor_contact" value="1" required> {{ __('panel.consent_accept_vendor_contact') }}</label>
                                <div class="mb-2">
                                    @include('partials.legal-scroll-gate', [
                                        'slug' => 'consent-'.$q->id,
                                        'label' => __('panel.consent_legal'),
                                        'field' => 'accept_consent',
                                        'contractHtml' => $consentContract->content_html ?? null,
                                    ])
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm">{{ __('panel.select_this_quote') }}</button>
                            </form>
                        @elseif($q->status === 'selected')
                            <span class="badge bg-success">{{ __('panel.selected_quote') }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <a href="{{ route('quote-requests.index') }}" class="btn btn-outline-secondary mt-4">← Taleplerime dön</a>
</div>
@endsection
