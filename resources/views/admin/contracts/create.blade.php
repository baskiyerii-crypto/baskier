@extends('layouts.admin')

@section('title', 'Sözleşme Ekle')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-0">Yeni sözleşme</h2>
            <div class="text-muted small">Kayıt/checkout onaylarında kullanılacak metin.</div>
        </div>
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">Geri</a>
    </div>

    <div class="card p-3">
        <form method="POST" action="{{ route('admin.contracts.store') }}">
            @csrf

            @include('admin.contracts._form')

            <div class="mt-3">
                <button class="btn btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
@endsection

