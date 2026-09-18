@extends('layouts.outdoor')
@section('title', 'Açık hava modülü')
@section('content')
<div class="card p-4 mb-4">
    <h2 class="h6 mb-3">Açık hava aboneliği</h2>
    <p class="small text-muted mb-0">Envanter, plan talepleri ve asım işleri bu modülle açılır. Matbaa / ürün satışı bu panelde yoktur. Sipariş komisyonu alınmaz.</p>
</div>
<div class="card p-4" style="max-width:420px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="h6 mb-0">Açık hava</h3>
        <span class="badge {{ $vendor->hasActiveOutdoorModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
            {{ $vendor->hasActiveOutdoorModule() ? 'Aktif' : 'Pasif' }}
        </span>
    </div>
    <p class="small mb-2">Aylık ücret: <strong>₺{{ number_format($outdoorMonthlyFee ?? 249, 2, ',', '.') }}</strong></p>
    <p class="small text-muted">Bitiş: {{ optional($vendor->outdoor_expires_at)->format('d.m.Y H:i') ?? '-' }}</p>
    <p class="small text-muted">Bakiye: ₺{{ number_format($vendor->balance, 2, ',', '.') }} · <a href="{{ route('outdoor-panel.balance.index') }}">Yükle</a></p>
    <form method="POST" action="{{ route('outdoor-panel.subscriptions.activate') }}" class="mt-2 js-subscription-form" data-module="outdoor">
        @csrf
        <input type="hidden" name="module" value="outdoor">
        <button class="btn btn-outline-primary btn-sm w-100 js-subscription-btn" type="submit">1 Ay Aktif Et / Uzat</button>
    </form>
</div>
<script>
document.querySelectorAll('.js-subscription-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        var btn = form.querySelector('.js-subscription-btn');
        if (btn.dataset.submitted === '1') { e.preventDefault(); return; }
        if (!confirm('Açık hava modülü için aylık ücret bakiyenizden düşülecek. Onaylıyor musunuz?')) {
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
