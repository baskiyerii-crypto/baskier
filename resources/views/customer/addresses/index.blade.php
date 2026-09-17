@extends('layouts.account')

@section('title', 'Adreslerim')

@section('content')
    @php($activeTab = request('tab', 'teslimat'))
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h5 mb-0">Adreslerim</h1>
        <a href="{{ route('account.adresler.create') }}" class="btn btn-warning btn-sm rounded-pill">+ Yeni adres</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'teslimat' ? 'active' : '' }}" href="{{ route('account.adresler.index', ['tab' => 'teslimat']) }}">Teslimat Adresleri</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'fatura' ? 'active' : '' }}" href="{{ route('account.adresler.index', ['tab' => 'fatura']) }}">Fatura Adresleri</a>
        </li>
    </ul>
    @if($activeTab === 'fatura')
        <div class="mb-3">
            <input type="text" class="form-control form-control-sm" id="billingAddressSearch" placeholder="Fatura adreslerinde ara (etiket, kişi, telefon, adres)">
        </div>
        @forelse($billingAddresses as $a)
            <div class="bg-white rounded-4 shadow-sm p-4 mb-3 d-flex justify-content-between flex-wrap gap-2 billing-address-item" data-search="{{ Str::lower(trim(($a->label ?? '').' '.$a->full_name.' '.$a->phone.' '.$a->formatted)) }}">
                <div>
                    @if($a->is_billing_default)<span class="badge bg-secondary mb-1">Varsayılan fatura</span>@endif
                    <div class="fw-semibold">{{ $a->label }} — {{ $a->full_name }}</div>
                    <div class="small text-muted">{{ $a->formatted }}</div>
                    <div class="small">{{ $a->phone }}</div>
                </div>
                <div>
                    @if(! $a->is_billing_default)
                        <form action="{{ route('account.adresler.set-billing-default', $a) }}" method="post" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-dark">Varsayılan adres olarak seç</button>
                        </form>
                    @endif
                    <a href="{{ route('account.adresler.edit', $a) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                </div>
            </div>
        @empty
            <p class="text-muted">Fatura adresi bulunmuyor.</p>
        @endforelse
        <p class="text-muted small d-none" id="billingAddressEmptyHint">Aramaya uygun fatura adresi bulunamadı.</p>
    @else
        @forelse($addresses as $a)
            <div class="bg-white rounded-4 shadow-sm p-4 mb-3 d-flex justify-content-between flex-wrap gap-2">
                <div>
                    @if($a->is_default)<span class="badge bg-secondary mb-1">Varsayılan</span>@endif
                    <div class="fw-semibold">{{ $a->label }} — {{ $a->full_name }}</div>
                    <div class="small text-muted">{{ $a->formatted }}</div>
                    <div class="small">{{ $a->phone }}</div>
                </div>
                <div>
                    @if(! $a->is_default)
                        <form action="{{ route('account.adresler.set-default', $a) }}" method="post" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-dark">Varsayılan adres olarak seç</button>
                        </form>
                    @endif
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
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('billingAddressSearch');
            if (!input) return;
            const items = Array.from(document.querySelectorAll('.billing-address-item'));
            const emptyHint = document.getElementById('billingAddressEmptyHint');
            input.addEventListener('input', function () {
                const term = (input.value || '').trim().toLocaleLowerCase('tr-TR');
                let visibleCount = 0;
                items.forEach(function (item) {
                    const haystack = item.getAttribute('data-search') || '';
                    const visible = term === '' || haystack.includes(term);
                    item.classList.toggle('d-none', !visible);
                    if (visible) visibleCount += 1;
                });
                if (emptyHint) emptyHint.classList.toggle('d-none', visibleCount > 0);
            });
        });
    </script>
@endsection
