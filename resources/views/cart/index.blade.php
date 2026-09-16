@extends('layouts.app')

@section('title', __('ui.cart_title') . ' - BaskıYeri')

@section('content')
<div class="content-shell py-4">
    <h1 class="h5 mb-4">{{ __('ui.cart_title') }}</h1>
    @if($items->isEmpty())
        <p class="text-muted">{{ __('ui.empty_cart') }} <a href="{{ route('products.index') }}">{{ __('ui.continue_shopping') }}</a></p>
    @else
        <div class="bg-white rounded-4 shadow-sm overflow-hidden mb-3">
            <table class="table table-hover mb-0">
                <thead><tr><th>{{ __('ui.products') }}</th><th>{{ __('ui.unit') }}</th><th>{{ __('ui.qty') }}</th><th>{{ __('ui.subtotal') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach($items as $item)
                        @php
                            $unit = (float) $item->product->price + (float) ($item->variant?->price_adjustment ?? 0);
                            $line = $item->lineTotal();
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $item->product->slug) }}" class="text-decoration-none text-dark fw-semibold">{{ $item->product->name }}</a>
                                @if($item->variant)
                                    <div class="small text-muted">{{ __('ui.variant') }}: {{ $item->variant->name }}</div>
                                @endif
                                <div class="small text-muted">{{ $item->product->vendor?->name }}</div>
                            </td>
                            <td>₺{{ number_format($unit, 2, ',', '.') }}</td>
                            <td style="width:140px;">
                                <form action="{{ route('cart.update', $item) }}" method="post" class="d-flex gap-1">
                                    @csrf @method('PUT')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="form-control form-control-sm">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">OK</button>
                                </form>
                            </td>
                            <td>₺{{ number_format($line, 2, ',', '.') }}</td>
                            <td class="text-end">
                                <form action="{{ route('cart.remove', $item) }}" method="post" onsubmit="return confirm('{{ __('ui.remove_confirm') }}');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger">{{ __('ui.remove') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="h6 mb-0">{{ __('ui.total') }}: <strong>₺{{ number_format($total, 2, ',', '.') }}</strong></span>
            <div class="text-end">
                <div class="small text-success mb-2">{{ __('ui.cart_ready') }}</div>
                <a href="{{ route('checkout.index') }}" class="btn btn-warning rounded-pill px-4 fw-semibold">{{ __('ui.secure_checkout') }}</a>
            </div>
        </div>
    @endif
</div>
@endsection
