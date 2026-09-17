@extends('layouts.vendor')

@section('title', 'Sipariş #' . $order->order_number)

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;

    $statusBadgeClass = match($order->status) {
        OrderStatus::CONFIRMED, OrderStatus::PENDING => 'bg-primary text-white',
        OrderStatus::DESIGN_REVIEW => 'bg-warning text-dark',
        OrderStatus::IN_PRODUCTION => 'bg-info text-dark',
        OrderStatus::READY_TO_SHIP => 'bg-secondary text-white',
        OrderStatus::SHIPPED => 'bg-primary-subtle text-primary border border-primary',
        OrderStatus::DELIVERED, OrderStatus::COMPLETED => 'bg-success text-white',
        OrderStatus::CANCELLED => 'bg-danger text-white',
        default => 'bg-secondary text-white',
    };
@endphp

<div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
        <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary btn-sm mb-1">← Siparişlere Dön</a>
        <h1 class="h4 mb-0 fw-bold">Sipariş #{{ $order->order_number }}</h1>
        <span class="text-muted small">Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }}</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($order->termin_due_at)
            @php
                $isLate = now()->gt($order->termin_due_at) && !in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED, OrderStatus::COMPLETED, OrderStatus::CANCELLED]);
                $daysLeft = (int) ceil(now()->diffInDays($order->termin_due_at, false));
            @endphp
            <span class="badge px-2.5 py-2 fs-6 rounded-pill {{ $isLate ? 'bg-danger text-white' : ($daysLeft <= 1 ? 'bg-warning text-dark' : 'bg-info-subtle text-info-emphasis border border-info-subtle') }}" title="Üretim / Kargoya Verme Son Tarihi">
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
        <span class="badge px-3 py-2 fs-6 rounded-pill {{ $statusBadgeClass }}">
            {{ UiLabels::orderStatus($order->status) }}
        </span>
    </div>
</div>

<div class="row g-3">
    <!-- Sol Sütun: Sipariş Kalemleri ve Prova Süreci -->
    <div class="col-lg-8">
        <!-- 1. Sipariş Kalemleri -->
        <div class="card p-3 mb-3 shadow-sm">
            <h2 class="h6 fw-bold mb-3 border-bottom pb-2">Sipariş Kalemleri</h2>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Ürün</th>
                            <th>Varyant / Özellik</th>
                            <th>Adet</th>
                            <th>Birim Fiyat</th>
                            <th class="text-end">Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->name }}</div>
                                    @if($item->sku) <span class="badge bg-light text-muted border">SKU: {{ $item->sku }}</span> @endif
                                </td>
                                <td class="small text-muted">{{ $item->variant_name ?? 'Standart' }}</td>
                                <td class="fw-bold">{{ $item->quantity }} adet</td>
                                <td>₺{{ number_format($item->price, 2, ',', '.') }}</td>
                                <td class="text-end fw-bold">₺{{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted text-center py-3">Bu siparişe ait ürün kalemi bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <td colspan="4" class="text-end fw-semibold">Ara Toplam:</td>
                            <td class="text-end fw-bold">₺{{ number_format($order->subtotal, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end text-muted small">Pazaryeri Komisyonu (%{{ $order->commission_rate ?? 0 }}):</td>
                            <td class="text-end text-danger small">-₺{{ number_format($order->commission_amount ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="4" class="text-end fw-bold text-success">Net Satıcı Hakedişi:</td>
                            <td class="text-end fw-bold text-success fs-6">₺{{ number_format($order->vendor_amount ?? $order->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 2. Dijital Prova ve Tasarım Onay Modülü (Özel Baskılı Ürünler İçin) -->
        <div class="card p-3 mb-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <div>
                    <h2 class="h6 fw-bold mb-0">Dijital Baskı Provası (Proofing)</h2>
                    <span class="small text-muted">Özel baskılı siparişlerde müşteri onayına dijital prova sunulur.</span>
                </div>
            </div>

            <!-- Geçmiş Provalar Listesi -->
            @if($order->designApprovals && $order->designApprovals->isNotEmpty())
                <div class="list-group mb-3">
                    @foreach($order->designApprovals as $approval)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold">Prova Turu #{{ $approval->round }}</span>
                                <span class="badge @if($approval->status === 'approved') bg-success @elseif($approval->status === 'revision_requested') bg-danger @else bg-warning text-dark @endif">
                                    {{ UiLabels::designApprovalStatus($approval->status) }}
                                </span>
                            </div>
                            @if($approval->design_file_path)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $approval->design_file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        📄 Yüklenen Prova Dosyasını İndir / Görüntüle
                                    </a>
                                </div>
                            @endif
                            @if($approval->vendor_note)
                                <div class="small text-muted mt-2"><strong>Satıcı Notu:</strong> {{ $approval->vendor_note }}</div>
                            @endif
                            @if($approval->customer_feedback)
                                <div class="alert alert-danger small mt-2 mb-0">
                                    <strong>Müşteri Revizyon Talebi:</strong> {{ $approval->customer_feedback }}
                                </div>
                            @endif
                            <div class="text-muted small text-end mt-1">{{ $approval->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-light border small text-muted mb-3">
                    Bu sipariş için henüz dijital prova yüklenmedi.
                </div>
            @endif

            <!-- Yeni Prova Yükleme Formu -->
            <form method="POST" action="{{ route('vendor.orders.design.store', $order) }}" enctype="multipart/form-data" class="border rounded p-3 bg-light">
                @csrf
                <div class="fw-semibold small mb-2">Müşteriye Yeni Prova Gönder</div>
                <div class="mb-2">
                    <label class="form-label small">Prova Dosyası (PDF, JPG, PNG, ZIP - Max 10MB)</label>
                    <input type="file" name="file" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.zip">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Satıcı Notu / Açıklama</label>
                    <textarea name="vendor_note" rows="2" class="form-control form-control-sm" placeholder="Örn: Çalışmanız baskı ölçülerine uyarlandı. Onayınız beklenmektedir."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Provayı Müşteri Onayına Gönder</button>
            </form>
        </div>
    </div>

    <!-- Sağ Sütun: Durum Yönetimi, Kargo ve Müşteri Bilgileri -->
    <div class="col-lg-4">
        <!-- 1. Durum Geçişleri ve Kargo Paneli -->
        <div class="card p-3 mb-3 shadow-sm">
            <h2 class="h6 fw-bold mb-3 border-bottom pb-2">Sipariş Durumunu Yönet</h2>

            <!-- Mevcut Kargo Bilgisi (Varsa) -->
            @if($order->latestShipment)
                @php
                    $trackingUrl = UiLabels::carrierTrackingUrl($order->latestShipment->carrier, $order->latestShipment->tracking_number);
                @endphp
                <div class="alert alert-info small mb-3">
                    <div class="fw-bold mb-1">🚚 Kargo Bilgisi:</div>
                    <div><strong>Firma:</strong> {{ $order->latestShipment->carrier }}</div>
                    <div><strong>Takip No:</strong> <span class="font-monospace fw-bold">{{ $order->latestShipment->tracking_number }}</span></div>
                    @if($order->latestShipment->shipped_at)
                        <div class="text-muted mt-1">Çıkış: {{ $order->latestShipment->shipped_at->format('d.m.Y H:i') }}</div>
                    @endif
                    @if($trackingUrl)
                        <div class="mt-2">
                            <a href="{{ $trackingUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary py-1 px-2">
                                🔗 Kargoyu Canlı Sorgula ↗
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Üretime Al Butonu -->
            @if(in_array(OrderStatus::IN_PRODUCTION, $allowedTransitions))
                <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}" class="mb-2">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ OrderStatus::IN_PRODUCTION }}">
                    <button type="submit" class="btn btn-success w-100 py-2 fw-semibold" onclick="return confirm('Siparişi üretime almak istediğinize emin misiniz?');">
                        ⚙️ Üretime Al
                    </button>
                </form>
            @endif

            <!-- Kargoya Hazır Butonu -->
            @if(in_array(OrderStatus::READY_TO_SHIP, $allowedTransitions))
                <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}" class="mb-2">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ OrderStatus::READY_TO_SHIP }}">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        📦 Üretim Bitti, Pakete / Kargoya Hazır
                    </button>
                </form>
            @endif

            <!-- Kargoya Ver Formu (Takip No Girişi) -->
            @if(in_array(OrderStatus::SHIPPED, $allowedTransitions))
                <div class="border rounded p-3 bg-light mb-2">
                    <div class="fw-semibold small mb-2">🚚 Kargoya Teslim Et</div>
                    <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="{{ OrderStatus::SHIPPED }}">
                        <div class="mb-2">
                            <label class="form-label small">Kargo Firması</label>
                            <select name="carrier" class="form-select form-select-sm" id="bk-carrier" required>
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
                            <div class="form-text">Taşıyıcı listesi Basit Kargo entegrasyonundan gelir.</div>
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
                        <div class="mb-2">
                            <label class="form-label small">Takip Numarası</label>
                            <input type="text" name="tracking_number" class="form-control form-control-sm" required placeholder="Takip barkod numarası">
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-semibold btn-sm">
                            Kargoya Verildi Olarak İşaretle
                        </button>
                    </form>
                </div>
            @endif

            <!-- Teslim Edildi Butonu -->
            @if(in_array(OrderStatus::DELIVERED, $allowedTransitions))
                <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}" class="mb-2">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ OrderStatus::DELIVERED }}">
                    <button type="submit" class="btn btn-outline-success w-100 btn-sm" onclick="return confirm('Alıcının ürünü teslim aldığını onaylıyor musunuz?');">
                        ✅ Teslim Edildi Olarak Tamamla
                    </button>
                </form>
            @endif

            @if(empty($allowedTransitions))
                <p class="text-muted small mb-0">Bu sipariş için şu anda yapılabilecek durum geçişi bulunmuyor.</p>
            @endif
        </div>

        <!-- 2. Müşteri & Teslimat Bilgileri -->
        <div class="card p-3 mb-3 shadow-sm">
            <h2 class="h6 fw-bold mb-3 border-bottom pb-2">Müşteri ve Teslimat</h2>
            <div class="small mb-2">
                <span class="text-muted d-block">Alıcı Adı:</span>
                <strong>{{ $order->user?->name ?? 'Misafir Alıcı' }}</strong>
            </div>
            <div class="small mb-2">
                <span class="text-muted d-block">E-posta:</span>
                {{ $order->user?->email ?? '-' }}
            </div>
            <div class="small mb-3">
                <span class="text-muted d-block">Teslimat Adresi:</span>
                <div class="p-2 bg-light rounded border">
                    {{ $order->shipping_address ?? ($order->shippingAddress?->full_address ?? 'Adres belirtilmemiş.') }}
                </div>
            </div>
            @if($order->notes)
                <div class="small">
                    <span class="text-muted d-block">Müşteri Sipariş Notu:</span>
                    <div class="p-2 bg-warning-subtle rounded border border-warning text-dark">
                        {{ $order->notes }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
