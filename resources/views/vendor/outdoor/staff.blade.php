@extends('layouts.outdoor')
@section('title', 'Ekip')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
    <div class="alert alert-danger">
        <strong>Lütfen aşağıdaki form bilgilerini kontrol edin:</strong>
        <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif

<h1 class="h5 mb-3">Açık hava ekibi</h1>
<p class="small text-muted">Ekipler (Adana, Ankara…) oluşturun. Pano ekleme yetkisi ekibe veya tek kişiye tarih aralığıyla verilir; istediğiniz an kaldırılır. Yetkili yalnız kendi eklediği panoları görür.</p>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <form method="POST" action="{{ route('outdoor-panel.staff.crews.store') }}" class="card p-3 h-100">
            @csrf
            <h2 class="h6">Ekip oluştur</h2>
            <input name="name" class="form-control mb-2" placeholder="Örn. Adana" required>
            <button class="btn btn-outline-primary btn-sm">Kaydet</button>
        </form>
    </div>
    <div class="col-md-8">
        <form method="POST" action="{{ route('outdoor-panel.staff.invite') }}" class="card p-3 h-100">
            @csrf
            <h2 class="h6">Kişi ekle</h2>
            <p class="small text-muted mb-2">Saha personeli <strong>/giris</strong> sayfasından e-posta ve şifre ile girer.</p>
            <div class="row g-2">
                <div class="col-md-6"><input name="name" class="form-control" placeholder="Ad" value="{{ old('name') }}" required></div>
                <div class="col-md-6"><input name="email" type="email" class="form-control" placeholder="E-posta" value="{{ old('email') }}" required></div>
                <div class="col-md-6"><input name="password" type="password" class="form-control" placeholder="Şifre (en az 8 karakter)" required></div>
                <div class="col-md-6"><input name="phone" type="tel" class="form-control" placeholder="Telefon (opsiyonel)" value="{{ old('phone') }}"></div>
                <div class="col-md-6">
                    <select name="crew_id" class="form-select">
                        <option value="">Ekipsiz saha</option>
                        @foreach($crews as $crew)
                            <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-primary btn-sm mt-2">Ekle</button>
        </form>
    </div>
</div>

<div class="card table-responsive mb-4">
    <table class="table mb-0">
        <thead><tr><th>Kişi</th><th>Ekip</th><th>Rol</th></tr></thead>
        <tbody>
        @forelse($members as $m)
            <tr>
                <td>{{ $m->user?->name }} <span class="small text-muted">({{ $m->user?->email }})</span></td>
                <td>{{ $m->crew?->name ?: '—' }}</td>
                <td>{{ $m->staff_role === 'owner' ? 'Sahip' : 'Saha' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-muted">Kayıt yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<form method="POST" action="{{ route('outdoor-panel.staff.grants.store') }}" class="card p-3 mb-4">
    @csrf
    <h2 class="h6">Pano ekleme yetkisi</h2>
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Hedef</label>
            <select name="target" class="form-select" id="grant-target">
                <option value="crew">Ekip</option>
                <option value="user">Kişi</option>
            </select>
        </div>
        <div class="col-md-3" id="grant-crew-wrap">
            <label class="form-label small">Ekip</label>
            <select name="crew_id" class="form-select">
                <option value="">Seçin</option>
                @foreach($crews as $crew)
                    <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-none" id="grant-user-wrap">
            <label class="form-label small">Kişi</label>
            <select name="user_id" class="form-select">
                <option value="">Seçin</option>
                @foreach($members->where('staff_role', '!=', 'owner') as $m)
                    <option value="{{ $m->user_id }}">{{ $m->user?->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Başlangıç</label>
            <input type="datetime-local" name="starts_at" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Bitiş (opsiyonel)</label>
            <input type="datetime-local" name="ends_at" class="form-control">
        </div>
        <div class="col-md-12">
            <button class="btn btn-warning text-dark btn-sm">Yetki ver</button>
        </div>
    </div>
</form>

<div class="card table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Yetki</th><th>Başlangıç</th><th>Bitiş</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        @forelse($grants as $g)
            <tr>
                <td>{{ $g->crew?->name ?: ($g->user?->name ?: '#'.$g->id) }}</td>
                <td>{{ $g->starts_at?->format('d.m.Y H:i') }}</td>
                <td>{{ $g->ends_at?->format('d.m.Y H:i') ?: 'İptale kadar' }}</td>
                <td>
                    @if($g->revoked_at)
                        <span class="badge bg-secondary">Kaldırıldı</span>
                    @elseif($g->isActive())
                        <span class="badge bg-success">Açık</span>
                    @else
                        <span class="badge bg-warning text-dark">Süresi doldu</span>
                    @endif
                </td>
                <td class="text-end">
                    @if(! $g->revoked_at)
                        <form method="POST" action="{{ route('outdoor-panel.staff.grants.revoke', $g) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Yetkiyi kaldır</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">Yetki yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<script>
document.getElementById('grant-target')?.addEventListener('change', function () {
    const crew = document.getElementById('grant-crew-wrap');
    const user = document.getElementById('grant-user-wrap');
    const isCrew = this.value === 'crew';
    crew.classList.toggle('d-none', !isCrew);
    user.classList.toggle('d-none', isCrew);
});
</script>
@endsection
