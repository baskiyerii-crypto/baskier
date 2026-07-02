@extends('layouts.vendor')
@section('title', 'Siparis ' . $order->order_number)
@section('content')
<div class="card p-4 mb-3">
    <p><strong>Siparis no:</strong> {{ $order->order_number }}</p>
    <p><strong>Musteri:</strong> {{ $order->user?->name }}</p>
    <p><strong>Tutar:</strong> TL {{ number_format($order->subtotal, 2, ',', '.') }}</p>
    <p><strong>Durum:</strong> {{ $order->status }}</p>
</div>
@if(in_array($order->status, ['paid', 'in_progress']) && $order->vendor_id === $vendor->id)
    <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}" class="d-inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" value="in_progress">
        <button type="submit" class="btn btn-success btn-sm">Uretimde</button>
    </form>
    <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}" class="d-inline ms-2">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" value="delivered">
        <button type="submit" class="btn btn-primary btn-sm">Teslim edildi</button>
    </form>
@endif
<a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary btn-sm mt-3">Listeye don</a>
@endsection
