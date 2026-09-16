@extends('layouts.admin')

@section('title', 'Satıcı Düzenle')

@section('content')
<div class="card p-4" style="max-width:540px;">
    <form method="POST" action="{{ route('admin.vendors.update', $vendor) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label fw-semibold">Ad</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $vendor->name) }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">E-posta</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email) }}" required>
            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Telefon</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor->phone) }}">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">İl</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $vendor->city) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">İlçe</label>
                <input type="text" name="district" class="form-control" value="{{ old('district', $vendor->district) }}">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Adres</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $vendor->address) }}">
        </div>

        <div class="card p-3 mb-4">
            <div class="fw-semibold mb-2">Vergi bilgileri ve doğrulama</div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label small">Firma / Ünvan</label>
                    <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $vendor->company_name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Vergi dairesi</label>
                    <input type="text" name="tax_office" class="form-control" value="{{ old('tax_office', $vendor->tax_office) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Vergi no / TCKN</label>
                    <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $vendor->tax_number) }}">
                </div>
                <div class="col-12 small text-muted">
                    Doğrulama durumu:
                    <span class="badge bg-light text-dark border">{{ \App\Support\UiLabels::verificationStatus($vendor->verification_status ?? 'pending') }}</span>
                </div>
                @php
                    $taxDoc = $vendor->documents()->where('document_type', 'tax_plate')->latest()->first();
                @endphp
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="small text-muted">Vergi levhası:</span>
                        @if($taxDoc)
                            <a class="btn btn-outline-secondary btn-sm" href="{{ asset('storage/'.$taxDoc->path) }}" target="_blank" rel="noopener">Görüntüle</a>
                            <span class="badge {{ $taxDoc->status === 'approved' ? 'bg-success-subtle text-success' : ($taxDoc->status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-light text-muted border') }}">
                                {{ \App\Support\UiLabels::status($taxDoc->status) }}
                            </span>
                        @else
                            <span class="text-muted small">Yüklenmemiş</span>
                        @endif
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <form method="POST" action="{{ route('admin.vendors.verify.approve', $vendor) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Onayla</button>
                    </form>
                    <form method="POST" action="{{ route('admin.vendors.verify.reject', $vendor) }}" onsubmit="return confirm('Reddetmek istediğinize emin misiniz?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Reddet</button>
                    </form>
                </div>
            </div>
        </div>
        @if(isset($businessTypes) && $businessTypes->isNotEmpty())
            <div class="mb-3">
                <label class="form-label fw-semibold">İş kolu</label>
                <select name="business_types[]" class="form-select" multiple size="6">
                    @foreach($businessTypes as $bt)
                        <option value="{{ $bt->id }}" @selected(in_array($bt->id, old('business_types', $vendor->businessTypes->pluck('id')->toArray())))>{{ $bt->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">Pazaryerinde “matbaa / tabela” gibi etiketler.</div>
            </div>
        @endif
        <div class="mb-3">
            <label class="form-label fw-semibold">Teklif verebileceği kategoriler (tabela/reklam)</label>
            <select name="quote_categories[]" class="form-select" multiple size="5">
                @foreach($quoteCategories as $c)
                    <option value="{{ $c->id }}" @selected(in_array($c->id, old('quote_categories', $vendor->quoteCategories->pluck('id')->toArray())))>{{ $c->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Bu satıcı hangi teklif kategorilerinde teklif verebilsin (çoklu seçim).</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Logo / Görsel</label>
            @if($vendor->logo)
                <div class="mb-2"><img src="{{ asset('storage/'.$vendor->logo) }}" alt="" class="rounded" style="max-height:80px; max-width:160px; object-fit:contain;"></div>
            @endif
            <input type="file" name="logo" class="form-control" accept="image/*">
            <div class="form-text">Değiştirmek için yeni dosya seçin. JPG, PNG, en fazla 2 MB.</div>
            @error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Açıklama</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $vendor->description) }}</textarea>
        </div>
        <div class="mb-4 form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $vendor->is_active))>
            <label class="form-check-label">Aktif</label>
        </div>
        <div class="card p-3 mb-4">
            <div class="fw-semibold mb-2">Modül ve abonelik yönetimi</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="freelancer_enabled" value="1" class="form-check-input" @checked(old('freelancer_enabled', $vendor->freelancer_enabled))>
                        <label class="form-check-label">Freelancer modülü aktif</label>
                    </div>
                    <label class="form-label small">Freelancer bitiş tarihi</label>
                    <input type="datetime-local" name="freelancer_expires_at" class="form-control"
                        value="{{ old('freelancer_expires_at', optional($vendor->freelancer_expires_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="quotes_enabled" value="1" class="form-check-input" @checked(old('quotes_enabled', $vendor->quotes_enabled))>
                        <label class="form-check-label">Teklif sistemi aktif</label>
                    </div>
                    <label class="form-label small">Teklif sistemi bitiş tarihi</label>
                    <input type="datetime-local" name="quotes_expires_at" class="form-control"
                        value="{{ old('quotes_expires_at', optional($vendor->quotes_expires_at)->format('Y-m-d\TH:i')) }}">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Güncelle</button>
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">İptal</a>
    </form>
</div>
@endsection
