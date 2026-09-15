@extends('layouts.vendor')

@section('title', 'Özet')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 border-0 shadow-sm" style="background:linear-gradient(135deg,#ecfdf5,#fff);">
            <div class="small text-muted">Ürün</div>
            <div class="h4 mb-0">{{ $productsCount }}</div>
            <a href="{{ route('vendor.products.index') }}" class="small text-decoration-none">Yönet →</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 border-0 shadow-sm" style="background:linear-gradient(135deg,#eff6ff,#fff);">
            <div class="small text-muted">Sipariş (toplam)</div>
            <div class="h4 mb-0">{{ $ordersCount }}</div>
            <a href="{{ route('vendor.orders.index') }}" class="small text-decoration-none">Listele →</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 border-0 shadow-sm" style="background:linear-gradient(135deg,#fff7ed,#fff);">
            <div class="small text-muted">Bakiye</div>
            <div class="h4 mb-0">₺{{ number_format($vendor->balance, 2, ',', '.') }}</div>
            <a href="{{ route('vendor.balance.index') }}" class="small text-decoration-none">Yükle →</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 h-100 border-0 shadow-sm" style="background:linear-gradient(135deg,#faf5ff,#fff);">
            <div class="small text-muted">Yaklaşan hakediş</div>
            <div class="h5 mb-0">₺{{ number_format($upcomingPayouts ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>
</div>

@if(($moduleEnds ?? collect())->isNotEmpty())
<div class="alert alert-warning small">Yaklaşan abonelik bitişleri:
    @foreach($moduleEnds as $mod => $date)
        <strong>{{ $mod }}</strong> {{ $date->format('d.m.Y') }}{{ !$loop->last ? ',' : '' }}
    @endforeach
</div>
@endif

<div class="card p-4 mb-4">
    <h2 class="h6 mb-3">Modül Durumu</h2>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Fiziksel ürün satışı</div>
                <span class="badge bg-success-subtle text-success">Aktif</span>
                <div class="small text-muted mt-2">Aylık abonelik gerektirmez, satış başına komisyon uygulanır.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Freelancer modülü</div>
                <span class="badge {{ $vendor->hasActiveFreelancerModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveFreelancerModule() ? 'Aktif' : 'Pasif' }}
                </span>
                <div class="small text-muted mt-2">Bitiş: {{ optional($vendor->freelancer_expires_at)->format('d.m.Y H:i') ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted mb-1">Teklif verme modülü</div>
                <span class="badge {{ $vendor->hasActiveQuotesModule() ? 'bg-success-subtle text-success' : 'bg-warning text-dark' }}">
                    {{ $vendor->hasActiveQuotesModule() ? 'Aktif' : 'Pasif' }}
                </span>
                <div class="small text-muted mt-2">Bitiş: {{ optional($vendor->quotes_expires_at)->format('d.m.Y H:i') ?? '-' }}</div>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <a href="{{ route('vendor.subscriptions.index') }}" class="btn btn-outline-primary btn-sm">Modül ve abonelikleri yönet</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 mb-0">Mağaza</h2>
                @if(!$vendor->is_active)
                    <span class="badge bg-warning text-dark">Onay bekliyor — yönetici onayından sonra vitrinde görünürsünüz</span>
                @else
                    <span class="badge bg-success-subtle text-success">Aktif</span>
                @endif
            </div>
            <div class="fw-semibold">{{ $vendor->name }}</div>
            @if($vendor->businessTypes->isNotEmpty())
                <div class="mt-2">
                    @foreach($vendor->businessTypes as $bt)
                        <span class="badge bg-light text-dark border me-1">{{ $bt->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-4 d-grid gap-2">
        <a href="{{ route('vendor.products.create') }}" class="btn btn-success py-3">+ Yeni ürün</a>
        <a href="{{ route('vendor.products.template') }}" class="btn btn-outline-secondary btn-sm">Excel şablonu indir</a>
    </div>
</div>

<div class="card p-4">
    <h2 class="h6 mb-3">Son ürünlerim</h2>
    @if($products->isEmpty())
        <p class="text-muted small mb-0">Henüz ürün eklemediniz. <a href="{{ route('vendor.products.create') }}">İlk ürünü ekleyin</a></p>
    @else
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead><tr><th>Ürün</th><th>Kategori</th><th>Fiyat</th><th>Stok</th><th></th></tr></thead>
                <tbody>
                    @foreach($products as $p)
                        <tr>
                            <td>{{ Str::limit($p->name, 35) }}</td>
                            <td>{{ $p->category?->name }}</td>
                            <td>₺{{ number_format($p->price, 2, ',', '.') }}</td>
                            <td>{{ $p->stock }}</td>
                            <td><a href="{{ route('vendor.products.edit', $p) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3"><a href="{{ route('vendor.products.index') }}" class="small fw-semibold text-decoration-none">Tüm ürünler →</a></div>
    @endif
</div>
@endsection
