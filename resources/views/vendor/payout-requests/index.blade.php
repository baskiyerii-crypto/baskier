@extends($layout ?? 'layouts.vendor')

@section('title', 'Hakediş & Para Çekme')

@section('content')
@php
    use App\Support\UiLabels;
    $isOutdoorPanel = $isOutdoorPanel ?? false;
    $storeRoute = $isOutdoorPanel ? 'outdoor-panel.payout-requests.store' : 'vendor.payout-requests.store';
    $minAmount = $minAmount ?? 10;
    $defaultIban = old('iban', $vendor->payout_iban ?: 'TR');
@endphp

<div class="alert alert-light border small mb-4">
    <strong>Hakediş kuralları</strong>
    <p class="mb-0 mt-1">{{ $rulesText ?? '' }}</p>
</div>

<div class="row g-4">
    <!-- Sol Sütun: Yeni Ödeme / Para Çekme Talebi -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-3" style="background: linear-gradient(145deg, #ecfdf5, #ffffff);">
            <h2 class="h6 fw-bold mb-3">Cüzdan ve Bakiye Özeti</h2>
            <div class="d-flex justify-content-between align-items-center mb-2 small">
                <span class="text-muted">Toplam Bakiye:</span>
                <span class="fw-semibold">₺{{ number_format($vendor->balance, 2, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2 small">
                <span class="text-muted">Onay Bekleyen Talepler:</span>
                <span class="text-warning fw-semibold">-₺{{ number_format($pendingTotal, 2, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                <span class="fw-bold">Çekilebilir Net Tutar:</span>
                <span class="h5 mb-0 fw-bold text-success">₺{{ number_format($availableBalance, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="card border shadow-sm rounded-4 p-4">
            <h2 class="h6 fw-bold mb-2">Para Çekme Talebi Oluştur</h2>
            <p class="small text-muted mb-3">Hakediş cüzdanınıza düştükten sonra IBAN’ınıza havale yönetici onayından sonra yapılır.</p>
            @if(! ($isPayoutDay ?? true))
                <div class="alert alert-warning small">Bugün çekim günü değil. Talebinizi izin verilen günlerde gönderebilirsiniz.</div>
            @endif

            <form method="post" action="{{ route($storeRoute) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Çekilecek Tutar (₺)</label>
                    <input type="number" id="payout-amount" name="amount" step="0.01" min="{{ $minAmount }}" max="{{ $availableBalance }}" class="form-control rounded-3 number-only-input" required placeholder="ör. 500" value="{{ old('amount') }}">
                    <div class="form-text">Minimum çekim tutarı ₺{{ number_format($minAmount, 2, ',', '.') }}'dir.</div>
                    @error('amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Banka IBAN Numarası</label>
                    <input type="text" id="iban-input" name="iban" class="form-control rounded-3 font-monospace text-uppercase" required placeholder="TR000000000000000000000000" maxlength="26" value="{{ $defaultIban }}">
                    <div class="form-text">TR ile başlayan 26 haneli IBAN (sadece rakam giriniz).</div>
                    @error('iban')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Banka Hesap Sahibi (Ad Soyad / Şirket)</label>
                    <input type="text" name="account_holder" class="form-control rounded-3" required placeholder="Hesap sahibinin tam adı" value="{{ old('account_holder', $vendor->payout_account_holder ?: $vendor->name) }}">
                    @error('account_holder')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-success fw-semibold w-100 py-2" @disabled($availableBalance < $minAmount || !($isPayoutDay ?? true))>
                    Ödeme Talebini Gönder
                </button>
            </form>
        </div>
    </div>

    <!-- Sağ Sütun: Geçmiş Talepler -->
    <div class="col-lg-7">
        <div class="card border shadow-sm rounded-4 overflow-hidden h-100">
            <div class="p-3 border-bottom bg-light">
                <h2 class="h6 fw-bold mb-0">Geçmiş Para Çekme Talepleri</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th>Açıklama / Hesap</th>
                            <th>Talep tarihi</th>
                            <th>Onay / red tarihi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $r)
                            <tr>
                                <td class="fw-bold text-success fs-6">₺{{ number_format($r->amount, 2, ',', '.') }}</td>
                                <td>
                                    <span class="badge rounded-pill
                                        @if($r->status === 'approved') bg-success
                                        @elseif($r->status === 'rejected') bg-danger
                                        @else bg-warning text-dark @endif">
                                        {{ UiLabels::payoutRequestStatus($r->status) }}
                                    </span>
                                    @if(($r->source ?? 'manual') === 'auto')
                                        <div class="small text-muted">Otomatik</div>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $r->iban ?: '' }} {{ $r->account_holder ? '· '.$r->account_holder : '' }}
                                    @if($r->admin_note && $r->status !== 'pending')
                                        <div>{{ Str::limit($r->admin_note, 45) }}</div>
                                    @endif
                                </td>
                                <td class="text-muted">{{ optional($r->requested_at ?? $r->created_at)->format('d.m.Y H:i') }}</td>
                                <td class="text-muted">
                                    @if($r->approved_at)
                                        {{ $r->approved_at->format('d.m.Y H:i') }}
                                    @elseif($r->rejected_at)
                                        {{ $r->rejected_at->format('d.m.Y H:i') }}
                                    @elseif($r->processed_at)
                                        {{ $r->processed_at->format('d.m.Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    Henüz para çekme talebiniz bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $requests->links() }}</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ibanInput = document.getElementById('iban-input');
    if (ibanInput) {
        function formatIban() {
            let val = ibanInput.value.toUpperCase().replace(/\s+/g, '');
            if (!val.startsWith('TR')) {
                // TR ile başlamıyorsa ve rakam içeriyorsa başa TR koy
                val = 'TR' + val.replace(/^TR/i, '').replace(/[^0-9]/g, '');
            } else {
                val = 'TR' + val.substring(2).replace(/[^0-9]/g, '');
            }
            if (val.length > 26) {
                val = val.substring(0, 26);
            }
            ibanInput.value = val;
        }

        ibanInput.addEventListener('focus', function() {
            if (!ibanInput.value || ibanInput.value.trim() === '') {
                ibanInput.value = 'TR';
            }
        });

        ibanInput.addEventListener('input', formatIban);

        ibanInput.addEventListener('keydown', function(e) {
            // İlk iki karakter TR ise ve imleç oradaysa backspace ile silinmesini engelle
            if ((e.key === 'Backspace' || e.key === 'Delete') && ibanInput.selectionStart <= 2 && ibanInput.selectionEnd <= 2) {
                e.preventDefault();
            }
        });
    }

    // Tutar alanı harf girişini engelleme
    const amountInput = document.getElementById('payout-amount');
    if (amountInput) {
        amountInput.addEventListener('keydown', function(e) {
            const allowed = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', '.', ',', 'Enter'];
            if (allowed.includes(e.key)) return;
            if (e.ctrlKey || e.metaKey) return;
            if (!/^[0-9]$/.test(e.key)) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endsection
