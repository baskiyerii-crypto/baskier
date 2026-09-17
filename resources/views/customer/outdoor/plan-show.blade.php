@extends('layouts.account')
@section('title', 'Plan #'.$plan->id)
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<a href="{{ route('customer.outdoor.plans.index') }}" class="text-sm text-slate-500">← Planlarım</a>
<h1 class="mt-2 text-2xl font-extrabold">{{ $plan->title }}</h1>
<p class="text-sm text-slate-600">Durum: {{ $plan->status }}</p>

@php
    $quoted = $plan->vendorRequests->filter(fn ($r) => $r->latestQuote);
    $waiting = $plan->vendorRequests->where('status', 'pending');
@endphp
<div class="mt-4 by-card p-4">
    <p>Tahmini toplam: ₺{{ number_format($plan->estimatedTotal(), 2, ',', '.') }}</p>
    <p>Gelen teklif toplamı: {{ $quoted->isEmpty() ? 'bekleniyor' : '₺'.number_format($quoted->sum(fn($r)=>(float)$r->latestQuote->amount), 2, ',', '.') }}</p>
    @if($waiting->isNotEmpty())<p class="text-amber-700 text-sm">{{ $waiting->count() }} satıcı henüz teklif vermedi.</p>@endif
</div>

@foreach($plan->vendorRequests as $req)
    <div class="mt-4 by-card p-4">
        <div class="flex justify-between">
            <strong>{{ $req->vendor?->name }}</strong>
            <span class="text-xs">{{ $req->status }}</span>
        </div>
        <ul class="mt-2 text-sm text-slate-600">
            @foreach($plan->items->where('owner_vendor_id', $req->vendor_id) as $item)
                <li>{{ $item->inventory?->title }} · {{ $item->starts_on->toDateString() }} – {{ $item->ends_on->toDateString() }}</li>
            @endforeach
        </ul>
        @if($req->latestQuote)
            <p class="mt-2 font-semibold">Teklif: ₺{{ number_format($req->latestQuote->amount, 2, ',', '.') }}</p>
            @if($req->latestQuote->note)<p class="text-sm text-slate-500">{{ $req->latestQuote->note }}</p>@endif
        @endif
        @if($req->status === 'quoted' && $req->latestQuote)
            <form method="POST" action="{{ route('customer.outdoor.plans.accept', [$plan, $req, $req->latestQuote]) }}" class="mt-3">
                @csrf
                <label class="d-flex gap-2 small mb-1"><input type="checkbox" name="share_my_contact" value="1" required> {{ __('panel.consent_share_my_contact') }}</label>
                <label class="d-flex gap-2 small mb-2"><input type="checkbox" name="accept_vendor_contact" value="1" required> {{ __('panel.consent_accept_vendor_contact') }}</label>
                @include('partials.legal-scroll-gate', [
                    'slug' => 'consent-ooh-'.$req->id,
                    'label' => __('panel.consent_legal'),
                    'field' => 'accept_consent',
                    'contractHtml' => $consentContract->content_html ?? null,
                ])
                <button class="by-btn-cta mt-2">Bu satıcıyı onayla</button>
            </form>
        @endif
        @if(($contactShares[$req->id] ?? null)?->isFullyConsented())
            <div class="mt-3 text-sm by-surface-cyan p-3 rounded-2xl">
                <p class="font-semibold">İletişim (açık rıza ile paylaşıldı)</p>
                <p>Satıcı tel: {{ $req->vendor?->phone ?: '—' }}</p>
                <p>Satıcı e-posta: {{ $req->vendor?->email ?: '—' }}</p>
            </div>
        @endif
    </div>
@endforeach
@endsection
