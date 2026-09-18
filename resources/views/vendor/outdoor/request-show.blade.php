@extends('layouts.outdoor')
@section('title', 'Plan talebi')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<a href="{{ route('outdoor-panel.requests.index') }}" class="small">← Liste</a>
<h1 class="h5 mt-2">{{ $vendorRequest->plan?->title }}</h1>
<p class="small text-muted">Müşteri: {{ $vendorRequest->plan?->planner?->name }} · durum {{ $vendorRequest->status }}</p>
<ul>
    @foreach(($quotedItems ?? $vendorRequest->plan->items) as $item)
        <li>{{ $item->inventory?->title }} · {{ $item->starts_on->toDateString() }} – {{ $item->ends_on->toDateString() }}</li>
    @endforeach
</ul>
@if(in_array($vendorRequest->status, ['pending','quoted']))
<form method="POST" action="{{ route('outdoor-panel.requests.quote', $vendorRequest) }}" class="card p-3 mb-3">
    @csrf
    <div class="mb-2"><label class="form-label">Tutar (₺)</label><input type="number" step="0.01" min="1" name="amount" class="form-control" required></div>
    <div class="mb-2"><label class="form-label">Not</label><textarea name="note" class="form-control" rows="2"></textarea></div>
    <label class="small d-flex gap-2 mb-2"><input type="checkbox" name="share_my_contact" value="1" required> {{ __('panel.consent_share_vendor_to_customer') }}</label>
    <button class="btn btn-primary btn-sm">Teklif gönder</button>
</form>
<form method="POST" action="{{ route('outdoor-panel.requests.decline', $vendorRequest) }}" onsubmit="return confirm('Reddedilsin mi?');">@csrf<button class="btn btn-outline-danger btn-sm">Reddet</button></form>
@endif
@if($vendorRequest->status === 'accepted')
    @php $share = \App\Models\ContactShare::query()->where('context_type','ooh_vendor_request')->where('context_id',$vendorRequest->id)->first(); @endphp
    @if($share?->isFullyConsented())
        <div class="alert alert-info mt-3">Müşteri tel: {{ $vendorRequest->plan?->planner?->phone ?: '—' }} · e-posta: {{ $vendorRequest->plan?->planner?->email }}</div>
    @endif
@endif
@endsection
