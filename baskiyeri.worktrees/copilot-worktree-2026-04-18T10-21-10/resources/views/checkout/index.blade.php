@extends('layouts.app')

@section('title', 'Ödeme - BaskıYeri')

@section('content')
<div class="content-shell py-4">
    <h1 class="h5 mb-4">Ödeme</h1>
    <p class="small text-muted">Demo ortamında sipariş, seçtiğiniz adres ile <strong>anında onaylı</strong> oluşturulur (gerçek ödeme entegrasyonu yok).</p>

    @if($items->isEmpty())
        <p><a href="{{ route('cart.index') }}">Sepete dön</a></p>
    @elseif($addresses->isEmpty())
        <div class="alert alert-warning">Önce <a href="{{ route('account.adresler.create') }}">bir teslimat adresi ekleyin</a>.</div>
    @else
        <div class="row g-4">
            <div class="col-lg-7">
                <form action="{{ route('checkout.store') }}" method="post" class="bg-white rounded-4 shadow-sm p-4">
                    @csrf
                    <h2 class="h6 mb-3">Teslimat adresi</h2>
                    @foreach($addresses as $a)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="address_id" id="addr{{ $a->id }}" value="{{ $a->id }}" @checked($a->is_default) required>
                            <label class="form-check-label" for="addr{{ $a->id }}">
                                <strong>{{ $a->label }}</strong> — {{ $a->full_name }}, {{ $a->formatted }}
                            </label>
                        </div>
                    @endforeach
                    <a href="{{ route('account.adresler.create') }}" class="small">+ Yeni adres</a>
                    <hr>
                    <button type="submit" class="btn btn-warning rounded-pill px-5">Siparişi tamamla</button>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="bg-white rounded-4 shadow-sm p-4">
                    <h2 class="h6 mb-3">Özet</h2>
                    <ul class="list-unstyled small mb-0">
                        @foreach($items as $item)
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span>{{ Str::limit($item->product->name, 32) }} × {{ $item->quantity }}</span>
                                <span>₺{{ number_format(bcmul((string)$item->product->price, (string)$item->quantity, 2), 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="d-flex justify-content-between mt-3 fw-bold">
                        <span>Toplam</span>
                        <span>₺{{ number_format($total, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
