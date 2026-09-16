@extends('layouts.admin')
@section('title', $customer->name)
@section('content')
<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between flex-wrap gap-2">
        <div>
            <div class="h5 mb-1">{{ $customer->name }}</div>
            <div class="small text-muted">{{ $customer->email }} · <code>{{ $customer->publicCode() }}</code></div>
            <div class="small mt-1">{{ __('panel.email') }}: {{ $customer->email_verified_at ? __('panel.verified') : __('panel.not_verified') }}
                · WhatsApp: {{ $customer->phone_verified_at ? __('panel.verified') : __('panel.not_verified') }}</div>
        </div>
        <form method="post" action="{{ route('admin.customers.toggle', $customer) }}">@csrf
            <button class="btn btn-sm {{ $customer->is_active ? 'btn-outline-danger' : 'btn-success' }}">
                {{ $customer->is_active ? __('panel.deactivate') : __('panel.activate') }}
            </button>
        </form>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm p-3">
            <h2 class="h6">{{ __('panel.orders') }}</h2>
            @forelse($orders as $o)
                <div class="small border-bottom py-2 d-flex justify-content-between">
                    <span>#{{ $o->order_number }} · {{ $o->vendor?->name }}</span>
                    <span>{{ \App\Support\UiLabels::orderStatus($o->status) }} · ₺{{ number_format($o->subtotal, 2, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-muted small mb-0">{{ __('panel.no_orders') }}</p>
            @endforelse
            <div class="mt-2">{{ $orders->links() }}</div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm p-3">
            <h2 class="h6">{{ __('panel.quote_requests') }}</h2>
            @forelse($quotes as $q)
                <div class="small border-bottom py-2">{{ $q->title }} · {{ \App\Support\UiLabels::status($q->status) }}</div>
            @empty
                <p class="text-muted small mb-0">{{ __('panel.no_results') }}</p>
            @endforelse
            <div class="mt-2">{{ $quotes->links() }}</div>
        </div>
    </div>
</div>
@endsection
