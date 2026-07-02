@extends('layouts.vendor')

@section('title', 'Ürünlerim')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('vendor.products.template') }}" class="btn btn-outline-secondary btn-sm">Excel şablonu indir</a>
        <form action="{{ route('vendor.products.import') }}" method="post" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control form-control-sm" required style="max-width:220px;">
            <button type="submit" class="btn btn-outline-primary btn-sm">Excel yükle</button>
        </form>
    </div>
    <a href="{{ route('vendor.products.create') }}" class="btn btn-success btn-sm">+ Yeni Ürün</a>
</div>
<div class="card overflow-hidden">
        <table class="table table-hover mb-0">
            <thead><tr><th>Ürün</th><th>Kategori</th><th>Fiyat</th><th>Stok</th><th>Durum</th><th></th></tr></thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>{{ Str::limit($p->name, 40) }}</td>
                        <td>{{ $p->category?->name }}</td>
                        <td>₺{{ number_format($p->price, 2, ',', '.') }}</td>
                        <td>{{ $p->stock }}</td>
                        <td>{{ $p->is_active ? 'Aktif' : 'Pasif' }}</td>
                        <td class="text-end">
                            <a href="{{ route('products.show', $p->slug) }}" class="btn btn-outline-secondary btn-sm" target="_blank">Görüntüle</a>
                            <a href="{{ route('vendor.products.edit', $p) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                            <form action="{{ route('vendor.products.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Ürün yok. <a href="{{ route('vendor.products.create') }}">İlk ürünü ekleyin</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
