@extends('layouts.admin')

@section('title', 'Satıcılar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <span></span>
    <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary btn-sm">Yeni Satıcı</a>
</div>
<div class="card overflow-hidden">
        <table class="table table-hover mb-0">
            <thead><tr><th>Ad</th><th>İş kolu</th><th>E-posta</th><th>Ürün</th><th>Durum</th><th></th></tr></thead>
            <tbody>
                @forelse($vendors as $v)
                    <tr>
                        <td>{{ $v->name }}</td>
                        <td class="small">
                            @forelse($v->businessTypes as $bt)
                                <span class="badge bg-light text-dark border">{{ $bt->name }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>{{ $v->email }}</td>
                        <td>{{ $v->products_count }}</td>
                        <td>{{ $v->is_active ? 'Aktif' : 'Pasif' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.vendors.edit', $v) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                            <form action="{{ route('admin.vendors.destroy', $v) }}" method="POST" class="d-inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Satıcı yok.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
<div class="mt-3">{{ $vendors->links() }}</div>
@endsection
