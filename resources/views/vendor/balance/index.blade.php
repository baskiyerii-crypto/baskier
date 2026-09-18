@extends($layout ?? 'layouts.vendor')
@section('title', 'Bakiye')
@section('content')
@php
    $isOutdoorPanel = $isOutdoorPanel ?? false;
    $iyzicoReady = $iyzicoReady ?? false;
    $shopierReady = $shopierReady ?? false;
    $action = $isOutdoorPanel ? 'outdoor-panel.balance.topup' : 'vendor.balance.topup';
@endphp
<div class="card p-4 mb-4">
    <h2 class="h6 mb-2">{{ $isOutdoorPanel ? 'Açık hava bakiyesi' : 'Satıcı bakiyesi' }}</h2>
    <p class="h4 text-success">₺{{ number_format($vendor->balance, 2, ',', '.') }}</p>
    @unless($isOutdoorPanel)
        <p class="small text-muted">Görüşme ücreti: ₺{{ number_format($meetingFee, 2, ',', '.') }}</p>
    @endunless
    <p class="small text-muted mb-3">Bakiye yükleme iyzico veya Shopier üzerinden tahsil edilir. Test API anahtarları yönetici panelinden açılır.</p>
    <form method="POST" action="{{ route($action) }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-auto">
            <label class="form-label small">Tutar (₺)</label>
            <input type="number" name="amount" min="10" max="10000" class="form-control form-control-sm" style="width:120px;" value="{{ old('amount', 100) }}" required>
        </div>
        <div class="col-auto">
            <label class="form-label small">Sağlayıcı</label>
            <select name="provider" class="form-select form-select-sm" required>
                <option value="iyzico" @disabled(! $iyzicoReady)>iyzico {{ $iyzicoReady ? '' : '(kapalı)' }}</option>
                <option value="shopier" @disabled(! $shopierReady)>Shopier {{ $shopierReady ? '' : '(kapalı)' }}</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-success btn-sm" @disabled(! $iyzicoReady && ! $shopierReady)>Öde ve yükle</button>
        </div>
    </form>
    @if(! $iyzicoReady && ! $shopierReady)
        <p class="small text-danger mt-2 mb-0">Ödeme sağlayıcısı henüz yapılandırılmamış. Yönetici API ayarlarından iyzico veya Shopier test/canlı anahtarlarını girin.</p>
    @endif
</div>
<div class="card p-4">
    <h3 class="h6 mb-3">Hareketler</h3>
    @if($transactions->isEmpty())
        <p class="text-muted small">Hareket yok.</p>
    @else
        <table class="table table-sm mb-0">
            <thead><tr><th>Tarih</th><th>Açıklama</th><th>Tutar</th></tr></thead>
            <tbody>
                @foreach($transactions as $t)
                    <tr>
                        <td>{{ $t->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $t->description ?? $t->type }}</td>
                        <td class="{{ $t->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $t->amount >= 0 ? '+' : '' }}₺{{ number_format($t->amount, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
