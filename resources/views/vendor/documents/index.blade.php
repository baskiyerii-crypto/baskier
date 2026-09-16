@extends('layouts.vendor')
@section('title', 'Belgeler')
@section('content')
@php $v = auth()->user()->vendor; @endphp
@if($v?->hasPhysicalTrack() || empty($v?->registration_tracks))
<div class="card p-4 mb-4">
    <h2 class="h6">{{ __('panel.doc_tax_plate') }}</h2>
    <p class="small text-muted">Fiziksel ürün / teklif için onaylı vergi levhası zorunludur.</p>
    <form method="post" action="{{ route('vendor.documents.store') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
        @csrf
        <input type="hidden" name="document_type" value="tax_plate">
        <div class="col-md-8">
            <label class="form-label small">Dosya (PDF/JPG/PNG)</label>
            <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100">Yükle</button>
        </div>
    </form>
</div>
@endif
@if($v?->hasFreelancerTrack())
<div class="card p-4 mb-4">
    <h2 class="h6">Freelancer evrakları</h2>
    <p class="small text-muted">Sertifika, diploma veya kurs belgesi yükleyin. Onay sonrası seviye otomatik hesaplanır.</p>
    <form method="post" action="{{ route('vendor.documents.store') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-4">
            <label class="form-label small">Belge tipi</label>
            <select name="document_type" class="form-select" required>
                @foreach(\App\Support\UiLabels::documentTypes() as $key => $label)
                    @if($key !== 'tax_plate')
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label small">Dosya</label>
            <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">Yükle</button>
        </div>
    </form>
    @if($v->freelancer_tier)
        <p class="small mt-3 mb-0">Seviye: <strong>{{ \App\Support\UiLabels::freelancerTier($v->freelancer_tier) }}</strong></p>
    @endif
</div>
@endif
<div class="card p-4">
    <h2 class="h6">Yüklenen belgeler</h2>
    @forelse($documents as $doc)
        <div class="d-flex justify-content-between small border-bottom py-2">
            <span>{{ \App\Support\UiLabels::documentType($doc->document_type) }} · {{ $doc->created_at?->format('d.m.Y H:i') }}</span>
            <span class="badge bg-light text-dark border">{{ \App\Support\UiLabels::status($doc->status) }}</span>
        </div>
    @empty
        <p class="small text-muted mb-0">Belge yok.</p>
    @endforelse
</div>
@endsection
