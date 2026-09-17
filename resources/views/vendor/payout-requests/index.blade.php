@extends('layouts.vendor')

@section('title', 'Hakediş & Para Çekme')

@section('content')
@php use App\Support\UiLabels; @endphp

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
            <p class="small text-muted mb-3">Hakediş tutarınız onaylandıktan sonra belirttiğiniz IBAN hesabınıza havale/EFT ile transfer edilir.</p>

            <form method="post" action="{{ route('vendor.payout-requests.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Çekilecek Tutar (₺)</label>
                    <input type="number" name="amount" step="0.01" min="10" max="{{ $availableBalance }}" class="form-control rounded-3" required placeholder="ör. 500" value="{{ old('amount') }}">
                    <div class="form-text">Minimum çekim tutarı ₺10,00'dir.</div>
                    @error('amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Banka IBAN Numarası</label>
                    <input type="text" name="iban" class="form-control rounded-3 font-monospace" required placeholder="TR000000000000000000000000" maxlength="26" value="{{ old('iban') }}">
                    <div class="form-text">TR ile başlayan 26 haneli IBAN.</div>
                    @error('iban')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Banka Hesap Sahibi (Ad Soyad / Şirket)</label>
                    <input type="text" name="account_holder" class="form-control rounded-3" required placeholder="Hesap sahibinin tam adı" value="{{ old('account_holder', $vendor->name) }}">
                    @error('account_holder')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-success fw-semibold w-100 py-2" @disabled($availableBalance < 10)>
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
                            <th>Tarih</th>
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
                                </td>
                                <td class="text-muted small">
                                    {{ $r->admin_note ? Str::limit($r->admin_note, 45) : '—' }}
                                </td>
                                <td class="text-muted">{{ $r->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
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
@endsection
