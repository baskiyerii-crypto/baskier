@extends('layouts.admin')

@section('title', 'Kategoriler')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <span></span>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">Yeni Kategori</a>
</div>
<div class="card overflow-hidden">
        <table class="table table-hover mb-0">
            <thead><tr><th>Ad</th><th>Üst</th><th>Ürün sayısı</th><th>Durum</th><th></th></tr></thead>
            <tbody>
                @forelse($categories as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->parent?->name ?? '—' }}</td>
                        <td>{{ $c->products_count }}</td>
                        <td>{{ $c->is_active ? 'Aktif' : 'Pasif' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                            <form action="{{ route('admin.categories.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">Kategori yok.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
<div class="mt-3">{{ $categories->links() }}</div>
@endsection
