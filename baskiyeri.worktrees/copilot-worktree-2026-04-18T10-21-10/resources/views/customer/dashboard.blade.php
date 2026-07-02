@extends('layouts.account')

@section('title', 'Hesabım')

@section('content')
    <h1 class="h5 mb-4">Merhaba, {{ $user->name }}</h1>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="border rounded-3 p-3 text-center h-100" style="background:linear-gradient(135deg,#fff7ed,#fff);">
                <div class="small text-muted">Sipariş</div>
                <div class="h4 mb-0 text-dark">{{ $ordersCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded-3 p-3 text-center h-100" style="background:linear-gradient(135deg,#f0fdf4,#fff);">
                <div class="small text-muted">Sepet</div>
                <div class="h4 mb-0 text-dark">{{ $cartCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded-3 p-3 text-center h-100" style="background:linear-gradient(135deg,#eff6ff,#fff);">
                <div class="small text-muted">Favori</div>
                <div class="h4 mb-0 text-dark">{{ $favoritesCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('products.index') }}" class="d-block border rounded-3 p-3 text-center text-decoration-none text-dark h-100 d-flex flex-column justify-content-center" style="background:#fef3c7;">
                <span class="small text-muted">Alışverişe</span>
                <span class="fw-semibold">Devam et →</span>
            </a>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100">
                <h2 class="h6 mb-3">Sipariş &amp; ödeme</h2>
                <div class="d-grid gap-2">
                    <a href="{{ route('account.orders.index') }}" class="btn btn-outline-dark btn-sm text-start">Siparişlerim</a>
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-dark btn-sm text-start">Sepetim</a>
                    <a href="{{ route('checkout.index') }}" class="btn btn-warning btn-sm">Ödemeye git</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100">
                <h2 class="h6 mb-3">Talepler &amp; ilanlar</h2>
                <div class="d-grid gap-2">
                    <a href="{{ route('quote-requests.index') }}" class="btn btn-outline-dark btn-sm text-start">Teklif taleplerim</a>
                    <a href="{{ route('quote-requests.create') }}" class="btn btn-outline-dark btn-sm text-start">Yeni teklif talebi</a>
                    <a href="{{ route('freelancer-jobs.my') }}" class="btn btn-outline-dark btn-sm text-start">İş ilanlarım</a>
                </div>
            </div>
        </div>
    </div>
@endsection
