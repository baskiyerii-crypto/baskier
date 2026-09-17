@extends('layouts.account')
@section('title', $directQuote->title)
@section('content')
<div class="by-card p-6 max-w-2xl space-y-3">
    <h1 class="text-xl font-bold">{{ $directQuote->title }}</h1>
    <p class="text-sm text-slate-600">{{ $directQuote->vendor?->name }} · {{ \App\Support\UiLabels::status($directQuote->status) }}</p>
    <p>{{ $directQuote->body }}</p>
    @if($directQuote->offer_amount)
        <p class="font-semibold">{{ __('panel.offer') }}: ₺{{ number_format($directQuote->offer_amount, 2, ',', '.') }}</p>
        <p class="text-sm">{{ $directQuote->vendor_note }}</p>
    @endif
    @if($directQuote->status === 'offered')
        <form method="post" action="{{ route('customer.direct-quotes.accept', $directQuote) }}" class="space-y-2 border-t pt-4">
            @csrf
            <label class="flex gap-2 text-sm"><input type="checkbox" name="share_my_contact" value="1" required> {{ __('panel.consent_share_my_contact') }}</label>
            <label class="flex gap-2 text-sm"><input type="checkbox" name="accept_vendor_contact" value="1" required> {{ __('panel.consent_accept_vendor_contact') }}</label>
            <p class="text-xs text-slate-500">{{ __('panel.consent_legal') }}</p>
            <button class="by-btn-primary">{{ __('panel.accept_and_share') }}</button>
        </form>
    @endif
</div>
@endsection
