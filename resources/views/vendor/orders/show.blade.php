@extends('layouts.vendor')

@section('title', 'Sipariş #' . $order->order_number . ' - Satıcı Paneli')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;

    $badgeVariant = match($order->status) {
        OrderStatus::CONFIRMED, OrderStatus::PENDING => 'info',
        OrderStatus::DESIGN_REVIEW, OrderStatus::IN_PRODUCTION => 'warning',
        OrderStatus::READY_TO_SHIP => 'neutral',
        OrderStatus::SHIPPED => 'info',
        OrderStatus::DELIVERED, OrderStatus::COMPLETED => 'success',
        OrderStatus::CANCELLED => 'danger',
        default => 'neutral',
    };
@endphp

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('vendor.orders.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Siparişlere Dön
        </a>
        <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Sipariş #{{ $order->order_number }}</h1>
        <span class="text-xs text-muted">Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }}</span>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if($order->termin_due_at)
            @php
                $isLate = now()->gt($order->termin_due_at) && !in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED, OrderStatus::COMPLETED, OrderStatus::CANCELLED]);
                $daysLeft = (int) ceil(now()->diffInDays($order->termin_due_at, false));
            @endphp
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $isLate ? 'bg-red-50 text-red-700 border border-red-200' : ($daysLeft <= 1 ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-canvas text-ink border border-border') }}">
                ⏱️ Termin: {{ $order->termin_due_at->format('d.m.Y') }}
                @if($isLate)
                    (Gecikmede!)
                @elseif($daysLeft === 0)
                    (Bugün son gün!)
                @elseif($daysLeft > 0)
                    ({{ $daysLeft }} gün kaldı)
                @endif
            </span>
        @endif
        <x-badge :variant="$badgeVariant" class="py-1.5 px-3 text-xs">
            {{ UiLabels::orderStatus($order->status) }}
        </x-badge>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Sol Sütun: Sipariş Kalemleri ve Prova Süreci -->
    <div class="lg:col-span-8 space-y-6">
        <!-- 1. Sipariş Kalemleri -->
        <div class="by-card bg-surface border border-border overflow-hidden">
            <div class="p-5 border-b border-border">
                <h2 class="font-heading text-base font-bold text-ink">Sipariş Kalemleri</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                            <th class="px-5 py-3">Ürün</th>
                            <th class="px-5 py-3">Varyant</th>
                            <th class="px-5 py-3">Adet</th>
                            <th class="px-5 py-3">Birim Fiyat</th>
                            <th class="px-5 py-3 text-right">Toplam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($order->items as $item)
                            <tr>
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-xs text-ink">{{ $item->name }}</div>
                                    @if($item->sku) <span class="inline-block text-[10px] text-muted bg-canvas px-1.5 py-0.5 rounded border border-border mt-0.5">SKU: {{ $item->sku }}</span> @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs text-muted">{{ $item->variant_name ?? 'Standart' }}</td>
                                <td class="px-5 py-3.5 text-xs font-bold text-ink">{{ $item->quantity }} adet</td>
                                <td class="px-5 py-3.5 text-xs text-muted">₺{{ number_format($item->price, 2, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-xs font-bold text-ink text-right">₺{{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted text-center py-4 text-xs">Bu siparişe ait ürün kalemi bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t border-border bg-canvas/40 text-xs">
                        <tr>
                            <td colspan="4" class="px-5 py-2 text-right font-medium text-muted">Ara Toplam:</td>
                            <td class="px-5 py-2 text-right font-bold text-ink">₺{{ number_format($order->subtotal, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-5 py-2 text-right font-medium text-muted">Pazaryeri Komisyonu (%{{ $order->commission_rate ?? 0 }}):</td>
                            <td class="px-5 py-2 text-right font-bold text-red-600">-₺{{ number_format($order->commission_amount ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="border-t border-border font-bold">
                            <td colspan="4" class="px-5 py-3 text-right text-ink text-sm">Net Satıcı Hakedişi:</td>
                            <td class="px-5 py-3 text-right text-emerald-600 text-base">₺{{ number_format($order->vendor_amount ?? $order->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 2. Dijital Prova ve Tasarım Onay Modülü -->
        <div class="by-card p-6 bg-surface border border-border">
            <div class="mb-4 border-b border-border pb-3">
                <h2 class="font-heading text-base font-bold text-ink">Dijital Baskı Provası (Proofing)</h2>
                <p class="text-xs text-muted mt-0.5">Özel baskılı siparişlerde müşteri onayına dijital prova sunarak hatalı baskı riskini sıfırlayın.</p>
            </div>

            <!-- Geçmiş Provalar Listesi -->
            @if($order->designApprovals && $order->designApprovals->isNotEmpty())
                <div class="space-y-3 mb-6">
                    @foreach($order->designApprovals as $approval)
                        <div class="p-4 rounded-xl border border-border bg-canvas/40 text-xs">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-ink">Prova Turu #{{ $approval->round }}</span>
                                <x-badge :variant="$approval->status === 'approved' ? 'success' : ($approval->status === 'revision_requested' ? 'danger' : 'warning')">
                                    {{ UiLabels::designApprovalStatus($approval->status) }}
                                </x-badge>
                            </div>
                            @if($approval->design_file_path)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $approval->design_file_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-surface text-xs font-medium text-ink hover:border-cta hover:text-cta transition-colors">
                                        📄 Yüklenen Prova Dosyasını İndir / Görüntüle
                                    </a>
                                </div>
                            @endif
                            @if($approval->vendor_note)
                                <div class="text-muted mt-2 p-2.5 rounded-lg bg-surface border border-border">
                                    <strong class="text-ink">Satıcı Notu:</strong> {{ $approval->vendor_note }}
                                </div>
                            @endif
                            @if($approval->customer_feedback)
                                <div class="text-red-900 mt-2 p-2.5 rounded-lg bg-red-50 border border-red-200">
                                    <strong>Müşteri Revizyon Talebi:</strong> {{ $approval->customer_feedback }}
                                </div>
                            @endif
                            <div class="text-muted text-[11px] text-right mt-2">{{ $approval->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-4 rounded-xl border border-border bg-canvas text-xs text-muted mb-6">
                    Bu sipariş için henüz dijital prova yüklenmedi.
                </div>
            @endif

            <!-- Yeni Prova Yükleme Formu -->
            <form method="POST" action="{{ route('vendor.orders.design.store', $order) }}" enctype="multipart/form-data" class="rounded-xl border border-border p-4 bg-canvas/50 space-y-3">
                @csrf
                <div class="font-bold text-xs uppercase tracking-wider text-ink">Müşteriye Yeni Prova Gönder</div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Prova Dosyası (PDF, JPG, PNG, ZIP - Max 10MB) <span class="text-red-500">*</span></label>
                    <input type="file" name="file" class="form-control text-xs" required accept=".pdf,.jpg,.jpeg,.png,.zip">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Satıcı Açıklaması / Yönerge</label>
                    <textarea name="vendor_note" rows="2" class="form-control text-xs" placeholder="Örn: Çalışmanız baskı ölçülerine uyarlandı. Taşma payları eklendi, lütfen onaylayınız."></textarea>
                </div>
                <button type="submit" class="btn btn-cta text-xs py-2 px-4">Provayı Müşteri Onayına Gönder</button>
            </form>
        </div>
    </div>

    <!-- Sağ Sütun: Durum Yönetimi, Kargo ve Müşteri Bilgileri -->
    <div class="lg:col-span-4 space-y-6">
        <!-- 1. Durum Geçişleri ve Kargo Paneli -->
        <div class="by-card p-6 bg-surface border border-border space-y-4">
            <h2 class="font-heading text-base font-bold text-ink border-b border-border pb-3">Sipariş Durumunu Yönet</h2>

            <!-- Mevcut Kargo Bilgisi (Varsa) -->
            @if($order->latestShipment)
                @php
                    $trackingUrl = UiLabels::carrierTrackingUrl($order->latestShipment->carrier, $order->latestShipment->tracking_number);
                @endphp
                <div class="p-4 rounded-xl bg-sky-50 border border-sky-200 text-xs text-sky-950 space-y-1.5">
                    <div class="font-bold text-sky-900">🚚 Kargo Bilgisi:</div>
                    <div><strong>Firma:</strong> {{ $order->latestShipment->carrier }}</div>
                    <div><strong>Takip No:</strong> <span class="font-mono font-bold select-all">{{ $order->latestShipment->tracking_number }}</span></div>
                    @if($order->latestShipment->shipped_at)
                        <div class="text-sky-800 text-[11px]">Çıkış: {{ $order->latestShipment->shipped_at->format('d.m.Y H:i') }}</div>
                    @endif
                    @if($trackingUrl)
                        <div class="pt-2">
                            <a href="{{ $trackingUrl }}" target="_blank" rel="noopener" class="btn btn-secondary text-xs py-1 px-3">
                                Kargoyu Canlı Sorgula ↗
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Üretime Al Butonu -->
            @if(in_array(OrderStatus::IN_PRODUCTION, $allowedTransitions))
                <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ OrderStatus::IN_PRODUCTION }}">
                    <button type="submit" class="btn btn-cta w-full text-xs py-2.5 font-bold" onclick="return confirm('Siparişi üretime almak istediğinize emin misiniz?');">
                        ⚙️ Üretime Al
                    </button>
                </form>
            @endif

            <!-- Kargoya Hazır Butonu -->
            @if(in_array(OrderStatus::READY_TO_SHIP, $allowedTransitions))
                <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ OrderStatus::READY_TO_SHIP }}">
                    <button type="submit" class="btn btn-secondary w-full text-xs py-2.5 font-bold">
                        📦 Üretim Bitti, Pakete / Kargoya Hazır
                    </button>
                </form>
            @endif

            <!-- Kargoya Ver Formu (Takip No Girişi) -->
            @if(in_array(OrderStatus::SHIPPED, $allowedTransitions))
                <div class="rounded-xl border border-border p-4 bg-canvas/40 space-y-3">
                    <div class="font-bold text-xs uppercase tracking-wider text-ink">🚚 Kargoya Teslim Et</div>
                    <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="{{ OrderStatus::SHIPPED }}">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Kargo Firması</label>
                            <select name="carrier" class="form-control text-xs" id="bk-carrier" required>
                                @forelse(($carriers ?? []) as $carrier)
                                    @php
                                        $code = is_array($carrier) ? (string) ($carrier['code'] ?? $carrier['name'] ?? '') : (string) $carrier;
                                        $name = is_array($carrier) ? (string) ($carrier['name'] ?? $code) : (string) $carrier;
                                    @endphp
                                    @if($code !== '')
                                        <option value="{{ $name }}" data-code="{{ $code }}">{{ $name }}</option>
                                    @endif
                                @empty
                                    <option value="Basit Kargo" data-code="basitkargo">Basit Kargo</option>
                                @endforelse
                            </select>
                            <input type="hidden" name="carrier_code" id="bk-carrier-code" value="">
                            <script>
                            (function () {
                                var sel = document.getElementById('bk-carrier');
                                var code = document.getElementById('bk-carrier-code');
                                function sync() {
                                    if (!sel || !code) return;
                                    var opt = sel.options[sel.selectedIndex];
                                    code.value = opt ? (opt.getAttribute('data-code') || '') : '';
                                }
                                sel?.addEventListener('change', sync);
                                sync();
                            })();
                            </script>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Takip Numarası</label>
                            <input type="text" name="tracking_number" class="form-control text-xs" required placeholder="Kargo barkod numarası">
                        </div>
                        <button type="submit" class="btn btn-cta w-full text-xs py-2 font-bold">
                            Kargoya Verildi Olarak İşaretle
                        </button>
                    </form>
                </div>
            @endif

            <!-- Teslim Edildi Butonu -->
            @if(in_array(OrderStatus::DELIVERED, $allowedTransitions))
                <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ OrderStatus::DELIVERED }}">
                    <button type="submit" class="btn btn-secondary w-full text-xs py-2 text-emerald-700 hover:bg-emerald-50" onclick="return confirm('Alıcının ürünü teslim aldığını onaylıyor musunuz?');">
                        ✅ Teslim Edildi Olarak Tamamla
                    </button>
                </form>
            @endif

            @if(empty($allowedTransitions))
                <p class="text-muted text-xs mb-0">Bu sipariş için şu anda yapılabilecek durum geçişi bulunmuyor.</p>
            @endif
        </div>

        <!-- 2. Müşteri & Teslimat Bilgileri -->
        <div class="by-card p-6 bg-surface border border-border text-xs space-y-3">
            <h2 class="font-heading text-base font-bold text-ink border-b border-border pb-3">Müşteri ve Teslimat</h2>
            <div>
                <span class="text-muted block mb-0.5">Alıcı Adı:</span>
                <strong class="text-ink font-semibold">{{ $order->user?->name ?? 'Misafir Alıcı' }}</strong>
            </div>
            <div>
                <span class="text-muted block mb-0.5">E-posta:</span>
                <span class="text-ink">{{ $order->user?->email ?? '-' }}</span>
            </div>
            <div>
                <span class="text-muted block mb-1">Teslimat Adresi:</span>
                <div class="p-3 bg-canvas/60 rounded-lg border border-border text-ink leading-relaxed">
                    {{ $order->shipping_address ?? ($order->shippingAddress?->full_address ?? 'Adres belirtilmemiş.') }}
                </div>
            </div>
            @if($order->notes)
                <div>
                    <span class="text-muted block mb-1">Müşteri Sipariş Notu:</span>
                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-amber-950 leading-relaxed">
                        {{ $order->notes }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
