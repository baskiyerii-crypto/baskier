@extends('layouts.vendor')
@section('title', $directQuote->title)
@section('content')
<div class="card border-0 shadow-sm p-4">
    <h1 class="h5">{{ $directQuote->title }}</h1>
    <p class="small text-muted">{{ $directQuote->customer?->name }} · {{ \App\Support\UiLabels::status($directQuote->status) }}</p>
    <p>{{ $directQuote->body }}</p>
    @if(in_array($directQuote->status, ['open', 'offered'], true))
        <form method="post" action="{{ route('vendor.direct-quotes.offer', $directQuote) }}" class="mt-3 row g-2">
            @csrf
            <div class="col-md-4"><input type="number" step="0.01" min="1" name="offer_amount" class="form-control" placeholder="₺" value="{{ old('offer_amount', $directQuote->offer_amount) }}" required></div>
            <div class="col-md-8"><input name="vendor_note" class="form-control" placeholder="{{ __('panel.note') }}" value="{{ old('vendor_note', $directQuote->vendor_note) }}"></div>
            <div class="col-12">
                <label class="small d-flex gap-2"><input type="checkbox" name="share_my_contact" value="1" required> {{ __('panel.consent_share_vendor_to_customer') }}</label>
            </div>
            <div class="col-12"><button class="btn btn-primary btn-sm">{{ __('panel.send_offer') }}</button></div>
        </form>
    @endif
</div>
@endsection
