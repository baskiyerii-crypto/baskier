@extends('layouts.vendor')

@section('title', 'Hakediş & Para Çekme - Satıcı Paneli')

@section('content')
@php use App\Support\UiLabels; @endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Sol Sütun: Yeni Ödeme / Para Çekme Talebi -->
    <div class="lg:col-span-5 space-y-6">
        <div class="by-card p-6 bg-surface border border-border">
            <h2 class="font-heading text-base font-bold text-ink mb-4 pb-2 border-b border-border">Cüzdan ve Bakiye Özeti</h2>
            <div class="space-y-2 text-xs mb-4">
                <div class="flex justify-between items-center">
                    <span class="text-muted">Toplam Bakiye:</span>
                    <span class="font-bold text-ink">₺{{ number_format($vendor->balance, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-muted">Onay Bekleyen Talepler:</span>
                    <span class="font-bold text-amber-600">-₺{{ number_format($pendingTotal, 2, ',', '.') }}</span>
                </div>
            </div>
            <div class="pt-3 border-t border-border flex justify-between items-baseline">
                <span class="text-xs font-bold uppercase text-ink">Çekilebilir Net Tutar:</span>
                <span class="text-2xl font-extrabold text-emerald-600">₺{{ number_format($availableBalance, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="by-card p-6 bg-surface border border-border">
            <h2 class="font-heading text-base font-bold text-ink mb-1">Para Çekme Talebi Oluştur</h2>
            <p class="text-xs text-muted mb-4">Talebiniz yönetici onayından sonra belirttiğiniz IBAN hesabınıza EFT/Havale olarak aktarılır.</p>

            <form method="post" action="{{ route('vendor.payout-requests.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Çekilecek Tutar (₺) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" step="0.01" min="10" max="{{ $availableBalance }}" class="form-control text-xs" required placeholder="Örn: 500" value="{{ old('amount') }}">
                    <div class="text-[11px] text-muted mt-1">Minimum çekim tutarı ₺10,00'dir.</div>
                    @error('amount')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Banka IBAN Numarası <span class="text-red-500">*</span></label>
                    <input type="text" name="iban" class="form-control text-xs font-mono" required placeholder="TR000000000000000000000000" maxlength="26" value="{{ old('iban') }}">
                    <div class="text-[11px] text-muted mt-1">TR ile başlayan 26 haneli IBAN numarası.</div>
                    @error('iban')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Banka Hesap Sahibi (Ad Soyad / Şirket) <span class="text-red-500">*</span></label>
                    <input type="text" name="account_holder" class="form-control text-xs" required placeholder="Hesap sahibinin tam adı" value="{{ old('account_holder', $vendor->name) }}">
                    @error('account_holder')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-cta w-full text-xs py-2.5 font-bold" @disabled($availableBalance < 10)>
                    Ödeme Talebini Gönder
                </button>
            </form>
        </div>
    </div>

    <!-- Sağ Sütun: Geçmiş Talepler -->
    <div class="lg:col-span-7">
        <div class="by-card bg-surface border border-border overflow-hidden h-full flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-border">
                    <h2 class="font-heading text-base font-bold text-ink">Geçmiş Para Çekme Talepleri</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                                <th class="px-5 py-3">Tutar</th>
                                <th class="px-5 py-3">Durum</th>
                                <th class="px-5 py-3">Açıklama</th>
                                <th class="px-5 py-3 text-right">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse($requests as $r)
                                <tr class="hover:bg-canvas/30 transition-colors">
                                    <td class="px-5 py-3.5 font-bold text-xs text-ink">₺{{ number_format($r->amount, 2, ',', '.') }}</td>
                                    <td class="px-5 py-3.5">
                                        <x-badge :variant="$r->status === 'approved' ? 'success' : ($r->status === 'rejected' ? 'danger' : 'warning')">
                                            {{ UiLabels::payoutRequestStatus($r->status) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-muted">
                                        {{ $r->admin_note ? Str::limit($r->admin_note, 35) : '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-muted text-right">{{ $r->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted text-xs py-8">
                                        Henüz para çekme talebiniz bulunmuyor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-4 border-t border-border">{{ $requests->links() }}</div>
        </div>
    </div>
</div>
@endsection
