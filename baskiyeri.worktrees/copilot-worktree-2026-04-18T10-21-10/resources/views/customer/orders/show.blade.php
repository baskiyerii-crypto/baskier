@extends('layouts.account')

@section('title', 'Sipariş #'.$order->order_number)

@section('content')
    <nav class="mb-3"><a href="{{ route('account.orders.index') }}" class="small text-muted text-decoration-none">← Siparişlerim</a></nav>
    <h1 class="h5 mb-3">Sipariş #{{ $order->order_number }}</h1>
    <div class="bg-white rounded-4 shadow-sm p-4 mb-3">
        <div class="row small">
            <div class="col-md-6 mb-2"><strong>Durum:</strong> {{ $order->status }}</div>
            <div class="col-md-6 mb-2"><strong>Tutar:</strong> ₺{{ number_format($order->subtotal, 2, ',', '.') }}</div>
            <div class="col-12 mb-2"><strong>Satıcı:</strong> {{ $order->vendor?->name ?? '—' }}</div>
            @if($order->contractor)
                <div class="col-12 mb-2"><strong>Freelancer:</strong> {{ $order->contractor->name }}</div>
            @endif
            @if($order->shipping_address)
                <div class="col-12"><strong>Adres / not:</strong> {{ $order->shipping_address }}</div>
            @endif
        </div>
    </div>
    <h2 class="h6">Kalemler</h2>
    <ul class="list-group mb-4">
        @foreach($order->items as $line)
            <li class="list-group-item d-flex justify-content-between">
                <span>{{ $line->name }} × {{ $line->quantity }}</span>
                <span>₺{{ number_format($line->price * $line->quantity, 2, ',', '.') }}</span>
            </li>
        @endforeach
    </ul>

    @if(in_array($order->status, ['delivered', 'completed'], true) && !$existingReview)
        <div class="bg-white rounded-4 shadow-sm p-4">
            <h2 class="h6 mb-3">Satıcıyı değerlendirin</h2>
            <form action="{{ route('account.orders.review', $order) }}" method="post">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Puan (1-5)</label>
                    <select name="rating" class="form-select" required>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }} yıldız</option>
                        @endfor
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Yorum (isteğe bağlı)</label>
                    <textarea name="comment" class="form-control" rows="3" maxlength="2000"></textarea>
                </div>
                <button type="submit" class="btn btn-warning rounded-pill">Gönder</button>
            </form>
        </div>
    @elseif($existingReview)
        <p class="small text-muted">Bu sipariş için değerlendirmeniz kayıtlı.</p>
    @endif
@endsection
