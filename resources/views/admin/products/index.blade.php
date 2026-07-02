@extends('layouts.admin')

@section('title', 'Ürünler')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <span></span>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Yeni ürün</a>
</div>
<div class="mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Ürün ara..." value="{{ request('q') }}">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select form-select-sm">
                <option value="">Tüm kategoriler</option>
                @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="vendor" class="form-select form-select-sm">
                <option value="">Tüm satıcılar</option>
                @foreach($vendors as $v)<option value="{{ $v->id }}" @selected(request('vendor') == $v->id)>{{ $v->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Filtrele</button>
        </div>
    </form>
</div>
<div class="card overflow-hidden">
        <table class="table table-hover mb-0">
            <thead><tr><th>Ürün</th><th>Satıcı</th><th>Kategori</th><th>Fiyat</th><th>Stok</th><th></th></tr></thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>{{ Str::limit($p->name, 40) }}</td>
                        <td>{{ $p->vendor?->name }}</td>
                        <td>{{ $p->category?->name }}</td>
                        <td>₺{{ number_format($p->price, 2, ',', '.') }}</td>
                        <td>{{ $p->stock }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                            <form action="{{ route('admin.products.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Ürün yok.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
