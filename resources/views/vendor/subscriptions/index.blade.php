@extends('layouts.vendor')
@section('title', 'Modüller ve Abonelik')
@section('content')
<div class="card p-4 mb-4">
    <h2 class="h6 mb-3">Satış ve modül yapısı</h2>
    <ul class="small text-muted mb-0">
        <li>Fiziksel ürün satışı aboneliksiz çalışır, satış başına komisyon uygulanır.</li>
        <li>Freelancer (dijital) ürün satışı aylık abonelik ile açılır.</li>
        <li>Teklif verme sistemi (teklif talepleri) aylık abonelik ile açılır.</li>
    </ul>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Fiziksel Ürün</h3>
                <span class="badge bg-success-subtle text-success">Her zaman aktif</span>
            </div>
            <p class="small text-muted mb-0">Aylık ücret yok. Sipariş başına komisyon kesintisi ile çalışır.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Freelancer Modülü</h3>
                <span class="badge {{ $vendor->hasActiveFreelancerModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveFreelancerModule() ? 'Aktif' : 'Pasif' }}
                </span>
            </div>
            <p class="small mb-2">Aylık ücret: <strong>₺{{ number_format($freelancerMonthlyFee, 2, ',', '.') }}</strong></p>
            <p class="small text-muted">
                Bitiş:
                {{ optional($vendor->freelancer_expires_at)->format('d.m.Y H:i') ?? '-' }}
            </p>
            <form method="POST" action="{{ route('vendor.subscriptions.activate') }}" class="mt-auto">
                @csrf
                <input type="hidden" name="module" value="freelancer">
                <button class="btn btn-outline-primary btn-sm w-100" type="submit">1 Ay Aktif Et / Uzat</button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Teklif Verme Modülü</h3>
                <span class="badge {{ $vendor->hasActiveQuotesModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveQuotesModule() ? 'Aktif' : 'Pasif' }}
                </span>
            </div>
            <p class="small mb-2">Aylık ücret: <strong>₺{{ number_format($quotesMonthlyFee, 2, ',', '.') }}</strong></p>
            <p class="small text-muted">
                Bitiş:
                {{ optional($vendor->quotes_expires_at)->format('d.m.Y H:i') ?? '-' }}
            </p>
            <form method="POST" action="{{ route('vendor.subscriptions.activate') }}" class="mt-auto">
                @csrf
                <input type="hidden" name="module" value="quotes">
                <button class="btn btn-outline-primary btn-sm w-100" type="submit">1 Ay Aktif Et / Uzat</button>
            </form>
        </div>
    </div>
</div>
@endsection
