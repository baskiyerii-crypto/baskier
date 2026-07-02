@extends('layouts.admin')

@section('title', 'Sözleşmeler')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-0">Sözleşmeler</h2>
            <div class="text-muted small">Kayıt/checkout gibi akışlarda kullanılan metinler.</div>
        </div>
        <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">+ Yeni sözleşme</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                <tr class="text-muted small">
                    <th class="py-3 px-3">Key</th>
                    <th class="py-3">Başlık</th>
                    <th class="py-3">Hedef</th>
                    <th class="py-3">Sürüm</th>
                    <th class="py-3">Aktif</th>
                    <th class="py-3 text-end px-3">İşlem</th>
                </tr>
                </thead>
                <tbody>
                @forelse($contracts as $c)
                    <tr>
                        <td class="px-3"><code>{{ $c->key }}</code></td>
                        <td>{{ $c->title }}</td>
                        <td><span class="badge bg-light text-dark">{{ $c->audience }}</span></td>
                        <td>v{{ (int) $c->version }}</td>
                        <td>
                            @if($c->is_active)
                                <span class="badge bg-success">aktif</span>
                            @else
                                <span class="badge bg-secondary">pasif</span>
                            @endif
                        </td>
                        <td class="text-end px-3">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.contracts.edit', $c) }}">Düzenle</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('contracts.show', $c->key) }}" target="_blank">Görüntüle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-muted">Henüz sözleşme yok.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $contracts->links() }}
    </div>
@endsection

