@extends('layouts.admin')

@section('title', 'Sözleşmeler')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-0">Sözleşmeler</h2>
            <div class="text-muted small">Kayıt/checkout gibi akışlarda kullanılan metinler.</div>
        </div>
        <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">+ Yeni sözleşme</a>
        <form method="POST" action="{{ route('admin.contracts.generate') }}">@csrf<button class="btn btn-outline-primary">Şablonları oluştur / güncelle</button></form>
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

    <div class="card mt-4">
        <div class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <span class="fw-semibold">Satıcı sözleşme durumları</span>
            <div class="btn-group btn-group-sm">
                <a class="btn btn-outline-secondary {{ request('status') === null ? 'active' : '' }}" href="{{ route('admin.contracts.index') }}">Tümü</a>
                <a class="btn btn-outline-secondary {{ request('status') === 'accepted' ? 'active' : '' }}" href="{{ route('admin.contracts.index', ['status' => 'accepted']) }}">Onaylı</a>
                <a class="btn btn-outline-secondary {{ request('status') === 'pending' ? 'active' : '' }}" href="{{ route('admin.contracts.index', ['status' => 'pending']) }}">Bekleyen</a>
                <a class="btn btn-outline-secondary {{ request('status') === 'missing' ? 'active' : '' }}" href="{{ route('admin.contracts.index', ['status' => 'missing']) }}">Eksik</a>
                <a class="btn btn-outline-secondary {{ request('status') === 'suspended' ? 'active' : '' }}" href="{{ route('admin.contracts.index', ['status' => 'suspended']) }}">Askı</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Satıcı</th><th>Onaylı</th><th>Bekleyen</th><th>Eksik</th><th>Askı</th></tr></thead>
                <tbody>
                @forelse($vendorStatuses ?? [] as $row)
                    <tr>
                        <td>{{ $row['vendor']->name }} <code>#{{ $row['vendor']->id }}</code></td>
                        <td>{{ $row['accepted'] }}</td>
                        <td>{{ $row['pending'] }}</td>
                        <td>{{ !empty($row['missing']) ? 'Evet' : 'Hayır' }}</td>
                        <td>{{ $row['suspended'] ? 'Evet' : 'Hayır' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted px-3 py-3">Kayıt yok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

