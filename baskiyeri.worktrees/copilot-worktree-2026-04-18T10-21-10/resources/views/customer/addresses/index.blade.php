@extends('layouts.account')

@section('title', 'Adreslerim')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h5 mb-0">Adreslerim</h1>
        <a href="{{ route('account.adresler.create') }}" class="btn btn-warning btn-sm rounded-pill">+ Yeni adres</a>
    </div>
    @forelse($addresses as $a)
        <div class="bg-white rounded-4 shadow-sm p-4 mb-3 d-flex justify-content-between flex-wrap gap-2">
            <div>
                @if($a->is_default)<span class="badge bg-secondary mb-1">Varsayılan</span>@endif
                <div class="fw-semibold">{{ $a->label }} — {{ $a->full_name }}</div>
                <div class="small text-muted">{{ $a->formatted }}</div>
                <div class="small">{{ $a->phone }}</div>
            </div>
            <div>
                <a href="{{ route('account.adresler.edit', $a) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                <form action="{{ route('account.adresler.destroy', $a) }}" method="post" class="d-inline" onsubmit="return confirm('Silinsin mi?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">Kayıtlı adres yok.</p>
    @endforelse
@endsection
