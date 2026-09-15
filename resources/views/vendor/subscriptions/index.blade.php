@extends('layouts.vendor')
@section('title', 'Modüller ve Abonelik')
@section('content')
<div class="card p-4 mb-4">
    <h2 class="h6 mb-3">Satış ve modül yapısı</h2>
    <ul class="small text-muted mb-0">
        <li>Fiziksel ürün satışı aboneliksiz çalışır; vergi levhası onayı gerekir, satış başına komisyon uygulanır.</li>
        <li>Freelancer hizmet teklifleri aylık abonelik ile açılır.</li>
        <li>Teklif verme (toplu baskı / üretim) aylık abonelik ile açılır.</li>
        <li>Tabela görüşme modülü aylık abonelik + görüşme başına ücret ile çalışır.</li>
    </ul>
</div>

<div class="row g-3">
    <div class="col-md-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Fiziksel Ürün</h3>
                <span class="badge bg-success-subtle text-success">Her zaman aktif</span>
            </div>
            <p class="small text-muted mb-0">Aylık ücret yok. Sipariş başına komisyon kesintisi ile çalışır.</p>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Freelancer Modülü</h3>
                <span class="badge {{ $vendor->hasActiveFreelancerModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveFreelancerModule() ? 'Aktif' : 'Pasif' }}
                </span>
            </div>
            <p class="small mb-2">Aylık ücret: <strong>₺{{ number_format($freelancerMonthlyFee, 2, ',', '.') }}</strong></p>
            <p class="small text-muted">Bitiş: {{ optional($vendor->freelancer_expires_at)->format('d.m.Y H:i') ?? '-' }}</p>
            <form method="POST" action="{{ route('vendor.subscriptions.activate') }}" class="mt-auto js-subscription-form" data-module="freelancer">
                @csrf
                <input type="hidden" name="module" value="freelancer">
                <button class="btn btn-outline-primary btn-sm w-100 js-subscription-btn" type="submit">1 Ay Aktif Et / Uzat</button>
            </form>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Teklif Verme Modülü</h3>
                <span class="badge {{ $vendor->hasActiveQuotesModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveQuotesModule() ? 'Aktif' : 'Pasif' }}
                </span>
            </div>
            <p class="small mb-2">Aylık ücret: <strong>₺{{ number_format($quotesMonthlyFee, 2, ',', '.') }}</strong></p>
            <p class="small text-muted">Bitiş: {{ optional($vendor->quotes_expires_at)->format('d.m.Y H:i') ?? '-' }}</p>
            <form method="POST" action="{{ route('vendor.subscriptions.activate') }}" class="mt-auto js-subscription-form" data-module="quotes">
                @csrf
                <input type="hidden" name="module" value="quotes">
                <button class="btn btn-outline-primary btn-sm w-100 js-subscription-btn" type="submit">1 Ay Aktif Et / Uzat</button>
            </form>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Tabela Modülü</h3>
                <span class="badge {{ $vendor->hasActiveTabelaModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveTabelaModule() ? 'Aktif' : 'Pasif' }}
                </span>
            </div>
            <p class="small mb-2">Aylık ücret: <strong>₺{{ number_format($tabelaMonthlyFee ?? 149, 2, ',', '.') }}</strong></p>
            <p class="small text-muted">Bitiş: {{ optional($vendor->tabela_expires_at)->format('d.m.Y H:i') ?? '-' }}</p>
            <form method="POST" action="{{ route('vendor.subscriptions.activate') }}" class="mt-auto js-subscription-form" data-module="tabela">
                @csrf
                <input type="hidden" name="module" value="tabela">
                <button class="btn btn-outline-primary btn-sm w-100 js-subscription-btn" type="submit">1 Ay Aktif Et / Uzat</button>
            </form>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.js-subscription-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        var btn = form.querySelector('.js-subscription-btn');
        if (btn.dataset.submitted === '1') {
            e.preventDefault();
            return;
        }
        if (!confirm('Bu modül için aylık ücret bakiyenizden düşülecek. Onaylıyor musunuz?')) {
            e.preventDefault();
            return;
        }
        btn.dataset.submitted = '1';
        btn.disabled = true;
        btn.textContent = 'İşleniyor...';
    });
});
</script>
@endsection
