@extends('layouts.admin')

@section('title', 'Sözleşme Düzenle')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-0">Sözleşme düzenle</h2>
            <div class="text-muted small"><code>{{ $contract->key }}</code></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('contracts.show', $contract->key) }}" class="btn btn-outline-secondary" target="_blank">Görüntüle</a>
            <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">Geri</a>
        </div>
    </div>

    <div class="card p-3">
        <form method="POST" action="{{ route('admin.contracts.update', $contract) }}">
            @csrf
            @method('PUT')

            @include('admin.contracts._form', ['contract' => $contract])

            <div class="mt-3">
                <button class="btn btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
@endsection

