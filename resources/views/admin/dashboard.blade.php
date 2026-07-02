@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Kategori</div>
            <div class="h4 mb-0">{{ $stats['categories'] }}</div>
            <a href="{{ route('admin.categories.index') }}" class="small">Yönet →</a>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">İş kolu</div>
            <div class="h4 mb-0">{{ $stats['business_types'] }}</div>
            <a href="{{ route('admin.business-types.index') }}" class="small">Yönet →</a>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Satıcı</div>
            <div class="h4 mb-0">{{ $stats['vendors'] }}</div>
            <a href="{{ route('admin.vendors.index') }}" class="small">Yönet →</a>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Ürün</div>
            <div class="h4 mb-0">{{ $stats['products'] }}</div>
            <a href="{{ route('admin.products.index') }}" class="small">Yönet →</a>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Sipariş</div>
            <div class="h4 mb-0">{{ $stats['orders'] }}</div>
            <a href="{{ route('admin.payouts.index') }}" class="small">Hakediş →</a>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card p-3 h-100 border-0 shadow-sm">
            <div class="small text-muted">Kullanıcı</div>
            <div class="h4 mb-0">{{ $stats['users'] }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h6 mb-3">Son siparişler</h2>
            @if($recentOrders->isEmpty())
                <p class="text-muted small mb-0">Henüz sipariş yok.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>No</th><th>Müşteri</th><th>Satıcı</th><th>Tutar</th><th>Durum</th></tr></thead>
                        <tbody>
                            @foreach($recentOrders as $o)
                                <tr>
                                    <td class="small">#{{ $o->order_number }}</td>
                                    <td class="small">{{ $o->user?->name ?? '—' }}</td>
                                    <td class="small">{{ $o->vendor?->name ?? '—' }}</td>
                                    <td>₺{{ number_format($o->subtotal, 2, ',', '.') }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $o->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4">
            <h2 class="h6 mb-3">Son ürünler</h2>
            @if($recentProducts->isEmpty())
                <p class="text-muted small mb-0">Henüz ürün yok.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Ürün</th><th>Satıcı</th><th>Fiyat</th></tr></thead>
                        <tbody>
                            @foreach($recentProducts as $p)
                                <tr>
                                    <td class="small">{{ Str::limit($p->name, 28) }}</td>
                                    <td class="small">{{ $p->vendor?->name }}</td>
                                    <td>₺{{ number_format($p->price, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('admin.products.index') }}" class="small d-inline-block mt-2">Tüm ürünler →</a>
            @endif
        </div>
    </div>
</div>
@endsection
