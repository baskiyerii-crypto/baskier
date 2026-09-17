@extends('layouts.admin')

@section('title', 'Satıcı 360 — '.$vendor->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h5 mb-1">{{ $vendor->name }}</h2>
        <div class="small text-muted">{{ $vendor->email }} · {{ $vendor->city }}</div>
        @php
            $riskLabel = match($vendor->risk_band) {
                'safe' => 'Risksiz', 'medium' => 'Orta risk', 'risky' => 'Riskli', default => 'Hesaplanmadı'
            };
            $riskClass = match($vendor->risk_band) {
                'safe' => 'bg-success-subtle text-success', 'medium' => 'bg-warning text-dark', 'risky' => 'bg-danger-subtle text-danger', default => 'bg-light text-muted'
            };
        @endphp
        <span class="badge {{ $riskClass }} mt-2">{{ $riskLabel }} · {{ number_format($vendor->risk_score ?? 0, 1) }}</span>
    </div>
    <div class="d-flex gap-2">
        @if($vendor->is_suspended)
            <form method="post" action="{{ route('admin.verifications.unsuspend', $vendor) }}">
                @csrf
                <button class="btn btn-success btn-sm">Askıyı kaldır</button>
            </form>
        @else
            <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#vendorSuspendModal">Askıya al</button>
        @endif
        <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
        <a href="{{ route('vendors.show', $vendor->slug) }}" target="_blank" class="btn btn-outline-primary btn-sm">Vitrin</a>
    </div>
</div>

@if($vendor->is_suspended)
    <div class="alert alert-danger">Askıda: {{ $vendor->suspension_reason ?: 'Gerekçe belirtilmedi.' }}</div>
@endif

<div class="modal fade" id="vendorSuspendModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="{{ route('admin.verifications.suspend', $vendor) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Satıcıyı askıya al</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Gerekçe</label>
                <textarea name="suspension_reason" class="form-control" rows="3" required minlength="5"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning">Askıya al</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Bakiye</div><div class="h5 mb-0">₺{{ number_format($vendor->balance, 2, ',', '.') }}</div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Ürün</div><div class="h5 mb-0">{{ $vendor->products->count() }}</div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Puan</div><div class="h5 mb-0">{{ $vendor->rating_average ? number_format($vendor->rating_average, 1) : '—' }}</div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Doğrulama</div><div class="h6 mb-0">{{ \App\Support\UiLabels::verificationStatus($vendor->verification_status) }}</div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4 mb-4">
            <h3 class="h6">Modüller</h3>
            <ul class="small mb-0">
                <li>Freelancer: {{ $vendor->hasActiveFreelancerModule() ? 'Aktif' : 'Pasif' }} ({{ optional($vendor->freelancer_expires_at)->format('d.m.Y') ?? '-' }})</li>
                <li>Teklif: {{ $vendor->hasActiveQuotesModule() ? 'Aktif' : 'Pasif' }} ({{ optional($vendor->quotes_expires_at)->format('d.m.Y') ?? '-' }})</li>
                <li>Tabela: {{ $vendor->hasActiveTabelaModule() ? 'Aktif' : 'Pasif' }} ({{ optional($vendor->tabela_expires_at)->format('d.m.Y') ?? '-' }})</li>
                <li>Ozalit: {{ $vendor->hasActiveOzalitModule() ? 'Aktif' : 'Pasif' }} ({{ optional($vendor->ozalit_expires_at)->format('d.m.Y') ?? '-' }})</li>
            </ul>
        </div>
        <div class="card p-4 mb-4">
            <h3 class="h6">Belgeler</h3>
            @forelse($vendor->documents as $doc)
                <div class="small mb-2 d-flex justify-content-between">
                    <span>{{ \App\Support\UiLabels::documentType($doc->document_type) }}</span>
                    <span class="badge bg-light text-dark border">{{ \App\Support\UiLabels::status($doc->status) }}</span>
                </div>
            @empty
                <p class="small text-muted mb-0">Belge yok.</p>
            @endforelse
        </div>
        <div class="card p-4">
            <h3 class="h6">Ürünler</h3>
            @forelse($vendor->products->take(10) as $p)
                <div class="small mb-1">{{ $p->name }} · ₺{{ number_format($p->price, 2, ',', '.') }}</div>
            @empty
                <p class="small text-muted mb-0">Ürün yok.</p>
            @endforelse
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4 mb-4">
            <h3 class="h6">Siparişler</h3>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>No</th><th>Tutar</th><th>Durum</th><th>Termin</th></tr></thead>
                    <tbody>
                    @foreach($orders as $o)
                        <tr>
                            <td class="small">#{{ $o->order_number }}</td>
                            <td>₺{{ number_format($o->subtotal, 2, ',', '.') }}</td>
                            <td>{{ \App\Support\UiLabels::orderStatus($o->status) }}</td>
                            <td class="small">{{ optional($o->termin_due_at)->format('d.m.Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $orders->links() }}
        </div>
        <div class="card p-4">
            <h3 class="h6">Bakiye hareketleri</h3>
            @forelse($transactions as $tx)
                <div class="small d-flex justify-content-between mb-2">
                    <span>{{ $tx->created_at?->format('d.m.Y H:i') }} · {{ $tx->description }}</span>
                    <strong>₺{{ number_format($tx->amount, 2, ',', '.') }}</strong>
                </div>
            @empty
                <p class="small text-muted mb-0">Hareket yok.</p>
            @endforelse
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
