@extends('layouts.admin')

@section('title', 'İş kolları')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted small mb-0">Pazaryerinde satıcıların bağlandığı iş kolu etiketleri (matbaa, tabela vb.).</p>
    <a href="{{ route('admin.business-types.create') }}" class="btn btn-primary btn-sm">Yeni iş kolu</a>
</div>
<div class="card table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Sıra</th>
                <th>Ad</th>
                <th>Slug</th>
                <th>Satıcı</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($types as $t)
                <tr>
                    <td>{{ $t->sort_order }}</td>
                    <td class="fw-semibold">{{ $t->name }}</td>
                    <td><code class="small">{{ $t->slug }}</code></td>
                    <td>{{ $t->vendors_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.business-types.edit', $t) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                        <form action="{{ route('admin.business-types.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" @if($t->vendors_count > 0) disabled title="Önce satıcılardan kaldırın" @endif>Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted p-4">Kayıt yok. Seeder çalıştırın veya yeni ekleyin.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $types->links() }}</div>
@endsection
