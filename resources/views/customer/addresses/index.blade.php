@extends('layouts.account')

@section('title', 'Adreslerim - BaskıYeri')

@section('content')
    @php($activeTab = request('tab', 'teslimat'))
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Kayıtlı Adreslerim</h1>
            <p class="text-xs text-muted mt-0.5">Sipariş teslimatı ve kurumsal/bireysel faturalandırma adresleri</p>
        </div>
        <div>
            <a href="{{ route('account.adresler.create') }}" class="btn btn-cta text-xs">
                + Yeni Adres Ekle
            </a>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    <div class="flex border-b border-border mb-6">
        <a class="px-4 py-2.5 text-xs font-semibold border-b-2 transition-colors {{ $activeTab === 'teslimat' ? 'border-cta text-cta' : 'border-transparent text-muted hover:text-ink' }}" href="{{ route('account.adresler.index', ['tab' => 'teslimat']) }}">
            Teslimat Adresleri
        </a>
        <a class="px-4 py-2.5 text-xs font-semibold border-b-2 transition-colors {{ $activeTab === 'fatura' ? 'border-cta text-cta' : 'border-transparent text-muted hover:text-ink' }}" href="{{ route('account.adresler.index', ['tab' => 'fatura']) }}">
            Fatura Adresleri
        </a>
    </div>

    @if($activeTab === 'fatura')
        <div class="mb-4">
            <input type="text" class="form-control text-xs" id="billingAddressSearch" placeholder="Fatura adreslerinde filtrele (etiket, kişi, telefon, adres...)">
        </div>
        <div class="space-y-3">
            @forelse($billingAddresses as $a)
                <div class="by-card p-5 bg-surface border border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4 billing-address-item" data-search="{{ Str::lower(trim(($a->label ?? '').' '.$a->full_name.' '.$a->phone.' '.$a->formatted)) }}">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-sm text-ink">{{ $a->label ?: 'Fatura Adresi' }}</span>
                            @if($a->is_billing_default)
                                <x-badge variant="info">Varsayılan Fatura</x-badge>
                            @endif
                        </div>
                        <div class="text-xs font-semibold text-ink">{{ $a->full_name }}</div>
                        <div class="text-xs text-muted mt-0.5">{{ $a->formatted }}</div>
                        <div class="text-xs text-muted mt-0.5">{{ $a->phone }}</div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        @if(! $a->is_billing_default)
                            <form action="{{ route('account.adresler.set-billing-default', $a) }}" method="post" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-secondary text-xs py-1.5 px-3">Varsayılan Yap</button>
                            </form>
                        @endif
                        <a href="{{ route('account.adresler.edit', $a) }}" class="btn btn-secondary text-xs py-1.5 px-3">Düzenle</a>
                    </div>
                </div>
            @empty
                <div class="by-card p-8 text-center bg-surface border border-border text-xs text-muted">
                    Fatura adresi bulunmuyor.
                </div>
            @endforelse
            <p class="text-muted text-xs text-center d-none" id="billingAddressEmptyHint">Aramaya uygun fatura adresi bulunamadı.</p>
        </div>
    @else
        <div class="space-y-3">
            @forelse($addresses as $a)
                <div class="by-card p-5 bg-surface border border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-sm text-ink">{{ $a->label ?: 'Teslimat Adresi' }}</span>
                            @if($a->is_default)
                                <x-badge variant="info">Varsayılan Teslimat</x-badge>
                            @endif
                        </div>
                        <div class="text-xs font-semibold text-ink">{{ $a->full_name }}</div>
                        <div class="text-xs text-muted mt-0.5">{{ $a->formatted }}</div>
                        <div class="text-xs text-muted mt-0.5">{{ $a->phone }}</div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        @if(! $a->is_default)
                            <form action="{{ route('account.adresler.set-default', $a) }}" method="post" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-secondary text-xs py-1.5 px-3">Varsayılan Yap</button>
                            </form>
                        @endif
                        <a href="{{ route('account.adresler.edit', $a) }}" class="btn btn-secondary text-xs py-1.5 px-3">Düzenle</a>
                        <form action="{{ route('account.adresler.destroy', $a) }}" method="post" class="inline" onsubmit="return confirm('Bu adresi silmek istediğinize emin misiniz?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-secondary text-xs py-1.5 px-3 text-red-600 hover:bg-red-50">Sil</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="by-card p-8 text-center bg-surface border border-border text-xs text-muted">
                    Kayıtlı teslimat adresi bulunmuyor.
                </div>
            @endforelse
        </div>
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
