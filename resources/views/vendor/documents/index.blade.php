@extends('layouts.vendor')
@section('title', 'Belgeler')
@section('content')
<div class="card p-4 mb-4">
    <h2 class="h6">Vergi levhası</h2>
    <p class="small text-muted">Fiziksel ürün yayınlamak için onaylı vergi levhası zorunludur.</p>
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
<div class="card p-4">
    <h2 class="h6">Yüklenen belgeler</h2>
    @forelse($documents as $doc)
        <div class="d-flex justify-content-between small border-bottom py-2">
            <span>{{ $doc->document_type }} · {{ $doc->created_at?->format('d.m.Y H:i') }}</span>
            <span class="badge bg-light text-dark border">{{ $doc->status }}</span>
        </div>
    @empty
        <p class="small text-muted mb-0">Belge yok.</p>
    @endforelse
</div>
@endsection
