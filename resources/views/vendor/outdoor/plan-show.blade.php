@extends('layouts.outdoor')
@section('title', 'Kampanya planı')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<a href="{{ route('outdoor-panel.plans.index') }}" class="small text-muted">← Planlarım</a>
<h1 class="h5 mt-2">{{ $plan->title }}</h1>
<p class="small">Durum: {{ $plan->status }}</p>
<p class="small text-muted">Karşı satıcıya plan talebi. Kabul edilen teklif açık hava siparişine dönüşür.</p>

@foreach($plan->vendorRequests as $req)
    <div class="card p-3 mb-2">
        <div class="d-flex justify-content-between">
            <strong>{{ $req->vendor?->name }}</strong>
            <span class="small">{{ $req->status }}</span>
        </div>
        @if($req->latestQuote)
            <div class="mt-1">Teklif ₺{{ number_format($req->latestQuote->amount, 2, ',', '.') }}</div>
            @if($req->latestQuote->note)<p class="small text-muted mb-0">{{ $req->latestQuote->note }}</p>@endif
        @endif
        @if($req->status === 'quoted' && $req->latestQuote)
            <form method="POST" action="{{ route('outdoor-panel.plans.accept', [$plan, $req, $req->latestQuote]) }}" class="mt-3">
                @csrf
                <label class="d-flex gap-2 small mb-1"><input type="checkbox" name="share_my_contact" value="1" required> {{ __('panel.consent_share_my_contact') }}</label>
                <label class="d-flex gap-2 small mb-2"><input type="checkbox" name="accept_vendor_contact" value="1" required> {{ __('panel.consent_accept_vendor_contact') }}</label>
                @include('partials.legal-scroll-gate', [
                    'slug' => 'consent-ooh-'.$req->id,
                    'label' => __('panel.consent_legal'),
                    'field' => 'accept_consent',
                    'contractHtml' => $consentContract->content_html ?? null,
                ])
                <button class="btn btn-primary btn-sm mt-2">Bu satıcıyı onayla</button>
            </form>
        @endif
    </div>
@endforeach
@endsection
