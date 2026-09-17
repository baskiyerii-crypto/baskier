@extends('layouts.vendor')

@section('title', 'Ürünlerim')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('vendor.products.template') }}" class="btn btn-outline-secondary btn-sm">Excel Şablonu İndir</a>
        <form action="{{ route('vendor.products.import') }}" method="post" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control form-control-sm" required style="max-width:220px;">
            <button type="submit" class="btn btn-outline-primary btn-sm">Excel Yükle</button>
        </form>
    </div>
    <a href="{{ route('vendor.products.create') }}" class="btn btn-success btn-sm fw-semibold">+ Yeni Ürün Ekle</a>
</div>

<!-- Filtre & Arama Kartı -->
<div class="card p-3 mb-3 shadow-sm">
    <form method="GET" action="{{ route('vendor.products.index') }}" class="row g-2 align-items-center">
        <div class="col-md-6">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Ürün adı veya açıklama ile ara...">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">Tüm Durumlar</option>
                <option value="active" @selected(request('status') === 'active')>Yalnızca Aktif</option>
                <option value="passive" @selected(request('status') === 'passive')>Yalnızca Pasif</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-secondary btn-sm flex-fill">Filtrele</button>
            @if(request()->hasAny(['q', 'status']))
                <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary btn-sm">Temizle</a>
            @endif
        </div>
    </form>
</div>

<div class="card overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th style="width: 60px;">Görsel</th>
                    <th>Ürün Adı</th>
                    <th>Kategori</th>
                    <th>Fiyat</th>
                    <th>Stok</th>
                    <th>Tip</th>
                    <th>Durum</th>
                    <th class="text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>
                            @if($p->main_image)
                                <img src="{{ asset('storage/'.$p->main_image) }}" alt="" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                            @else
                                <div class="rounded border bg-light text-muted d-flex align-items-center justify-content-center small" style="width: 44px; height: 44px;">
                                    📷
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ Str::limit($p->name, 45) }}</div>
                            @if($p->variants && $p->variants->isNotEmpty())
                                <span class="badge bg-light text-muted border small">{{ $p->variants->count() }} varyant</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $p->category?->name ?? '—' }}</td>
                        <td class="fw-bold text-success">₺{{ number_format($p->price, 2, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $p->stock > 0 ? 'bg-light text-dark border' : 'bg-danger-subtle text-danger border border-danger' }}">
                                {{ $p->stock }} adet
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ ($p->product_type ?? 'physical') === 'digital' ? 'bg-info-subtle text-info border' : 'bg-secondary-subtle text-secondary' }}">
                                {{ ($p->product_type ?? 'physical') === 'digital' ? 'Dijital' : 'Fiziksel' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $mod = $p->moderation_status ?? 'approved';
                            @endphp
                            @if($mod === 'pending')
                                <span class="badge rounded-pill bg-warning text-dark">Onay bekliyor</span>
                            @elseif($mod === 'rejected')
                                <span class="badge rounded-pill bg-danger">Reddedildi</span>
                                @if($p->moderation_note)
                                    <div class="small text-muted">{{ $p->moderation_note }}</div>
                                @endif
                            @else
                                <span class="badge rounded-pill {{ $p->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $p->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('products.show', $p->slug) }}" class="btn btn-outline-secondary btn-sm" target="_blank">Vitrin ↗</a>
                            <a href="{{ route('vendor.products.edit', $p) }}" class="btn btn-outline-primary btn-sm">Düzenle</a>
                            <form action="{{ route('vendor.products.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            Henüz ürün bulunmuyor. <a href="{{ route('vendor.products.create') }}" class="fw-semibold">İlk ürünü hemen ekleyin</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
