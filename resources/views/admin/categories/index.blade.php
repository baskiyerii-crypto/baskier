@extends('layouts.admin')

@section('title', 'Kategoriler')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="btn-group btn-group-sm">
        <a href="{{ route('admin.categories.index', ['channel' => 'physical_quote']) }}" class="btn {{ ($channel ?? '') === 'physical_quote' ? 'btn-primary' : 'btn-outline-primary' }}">Fiziksel + Teklif</a>
        <a href="{{ route('admin.categories.index', ['channel' => 'freelancer']) }}" class="btn {{ ($channel ?? '') === 'freelancer' ? 'btn-primary' : 'btn-outline-primary' }}">Freelancer</a>
        <a href="{{ route('admin.categories.index', ['channel' => 'tabela']) }}" class="btn {{ ($channel ?? '') === 'tabela' ? 'btn-primary' : 'btn-outline-primary' }}">Tabela</a>
    </div>
    <a href="{{ route('admin.categories.create', ['channel' => $channel ?? 'physical_quote']) }}" class="btn btn-primary btn-sm">Yeni Kategori</a>
</div>
<div class="card overflow-hidden">
        <table class="table table-hover mb-0">
            <thead><tr><th>Ad</th><th>Kanal</th><th>Termin (gün)</th><th>Üst</th><th>Ürün</th><th>Durum</th><th></th></tr></thead>
            <tbody>
                @forelse($categories as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $c->channel ?? 'physical_quote' }}</span></td>
                        <td>{{ $c->termin_days ?? $c->delivery_days ?? '—' }}</td>
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
                    <tr><td colspan="7" class="text-muted">Kategori yok.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
<div class="mt-3">{{ $categories->links() }}</div>
@endsection
