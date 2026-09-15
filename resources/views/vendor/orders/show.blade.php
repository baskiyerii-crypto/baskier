@extends('layouts.vendor')
@section('title', 'Sipariş '.$order->order_number)
@section('content')
<div class="card p-4 mb-3">
    <div class="row g-3">
        <div class="col-md-8">
            <p class="mb-1"><strong>Sipariş no:</strong> {{ $order->order_number }}</p>
            <p class="mb-1"><strong>Müşteri:</strong> {{ $order->user?->name }}</p>
            <p class="mb-1"><strong>Tutar:</strong> ₺{{ number_format($order->subtotal, 2, ',', '.') }}</p>
            <p class="mb-1"><strong>Durum:</strong> <span class="badge bg-light text-dark border">{{ $order->status }}</span></p>
            <p class="mb-1"><strong>Termin:</strong> {{ optional($order->termin_due_at)->format('d.m.Y H:i') ?? '—' }}</p>
            <p class="mb-0"><strong>Kargo takip:</strong> {{ $order->tracking_number ?? '—' }}</p>
        </div>
        <div class="col-md-4">
            <p class="small text-muted mb-1">Teslimat</p>
            <p class="small">{{ $order->shipping_address }}</p>
        </div>
    </div>
</div>

<div class="card p-4 mb-3">
    <h2 class="h6">Kalemler</h2>
    <div class="row g-3">
        @foreach($order->items as $item)
            <div class="col-md-6">
                <div class="d-flex gap-3 border rounded-3 p-2">
                    <div class="bg-light rounded" style="width:72px;height:72px;overflow:hidden;">
                        @if($item->product?->main_image)
                            <img src="{{ asset('storage/'.$item->product->main_image) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        @endif
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $item->name }}</div>
                        <div class="small text-muted">{{ $item->variant_name }} · {{ $item->quantity }} adet</div>
                        <div class="small">₺{{ number_format($item->price, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if(!empty($nextStatuses))
<div class="card p-4 mb-3">
    <h2 class="h6">Sipariş aksiyonları</h2>
    <div class="d-flex flex-wrap gap-2">
        @foreach($nextStatuses as $status)
            <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="{{ $status }}">
                @if(in_array($status, ['ready_to_ship', 'shipped'], true))
                    <select name="carrier_code" class="form-select form-select-sm mb-2">
                        @foreach($carriers as $c)
                            <option value="{{ $c['code'] ?? ($c['name'] ?? '') }}">{{ $c['name'] ?? ($c['code'] ?? 'Kargo') }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="submit" class="btn btn-sm btn-primary">{{ str_replace('_', ' ', $status) }}</button>
            </form>
        @endforeach
    </div>
</div>
@endif

<a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary btn-sm">Listeye dön</a>
@endsection
