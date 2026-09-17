@extends('layouts.account')

@section('title', 'Sipariş #'.$order->order_number . ' - BaskıYeri')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;

    $statusVariant = match($order->status) {
        OrderStatus::CONFIRMED, OrderStatus::PENDING, 'paid' => 'info',
        OrderStatus::DESIGN_REVIEW, OrderStatus::IN_PRODUCTION => 'warning',
        OrderStatus::SHIPPED => 'info',
        OrderStatus::DELIVERED, OrderStatus::COMPLETED => 'success',
        OrderStatus::CANCELLED => 'danger',
        default => 'neutral',
    };

    $shipment = $order->latestShipment;
    $trackingNo = $shipment?->tracking_number ?? $order->tracking_number;
    $carrierName = $shipment?->carrier ?? $order->carrier_code;
    $shippedDate = $shipment?->shipped_at ?? $order->shipped_at;
@endphp

    <div class="mb-5">
        <a href="{{ route('account.orders.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Tüm Siparişlerime Dön
        </a>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Sipariş #{{ $order->order_number }}</h1>
            </div>
            <span class="text-xs text-muted">Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }}</span>
        </div>
        <div>
            <x-badge :variant="$statusVariant" class="text-xs py-1.5 px-3">
                {{ UiLabels::orderStatus($order->status) }}
            </x-badge>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    {{-- Kargo & Gönderi Takip Kartı --}}
    @if($trackingNo || $shippedDate || in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED, OrderStatus::COMPLETED]))
        <div class="by-card p-6 bg-surface border border-sky-200/80 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-sky-600 font-bold">🚚 Kargo ve Gönderi Takibi</span>
                </div>
                <x-badge variant="info">
                    {{ in_array($order->status, [OrderStatus::DELIVERED, OrderStatus::COMPLETED]) ? 'Teslim Edildi' : 'Kargoda' }}
                </x-badge>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                @if($carrierName)
                    <div>
                        <span class="text-muted block mb-0.5">Kargo Firması:</span>
                        <strong class="text-ink font-semibold text-sm">{{ $carrierName }}</strong>
                    </div>
                @endif
                @if($trackingNo)
                    <div>
                        <span class="text-muted block mb-0.5">Takip Numarası:</span>
                        <strong class="font-mono text-ink font-semibold text-sm select-all">{{ $trackingNo }}</strong>
                    </div>
                @endif
                @if($shippedDate)
                    <div>
                        <span class="text-muted block mb-0.5">Kargoya Veriliş:</span>
                        <strong class="text-ink font-semibold text-sm">{{ $shippedDate->format('d.m.Y H:i') }}</strong>
                    </div>
                @endif
            </div>
            @php
                $trackingUrl = UiLabels::carrierTrackingUrl($carrierName, $trackingNo);
            @endphp
            @if($trackingUrl)
                <div class="mt-4 pt-3 border-t border-border">
                    <a href="{{ $trackingUrl }}" target="_blank" rel="noopener" class="btn btn-secondary text-xs">
                        Kargo Firmasının Sitesinde Canlı Takip Et ↗
                    </a>
                </div>
            @endif
        </div>
    @endif

    {{-- Dijital Baskı Provası & Onay Modülü --}}
    @if($order->designApprovals && $order->designApprovals->isNotEmpty())
        <div class="by-card p-6 bg-surface border border-border mb-6">
            <div class="mb-4 border-b border-border pb-3">
                <h2 class="font-heading text-lg font-bold text-ink mb-0.5">📄 Dijital Baskı Provası</h2>
                <p class="text-xs text-muted">Üretici tarafından yüklenen baskı provasını inceleyip onaylayabilir veya revizyon isteyebilirsiniz.</p>
            </div>

            <div class="space-y-4">
                @foreach($order->designApprovals as $approval)
                    @php
                        $approvalVariant = match($approval->status) {
                            'approved' => 'success',
                            'revision_requested' => 'danger',
                            default => 'warning',
                        };
                    @endphp
                    <div class="p-4 rounded-xl border border-border bg-canvas/40">
                        <div class="flex justify-between items-center mb-3 flex-wrap gap-2">
                            <span class="font-bold text-sm text-ink">Prova Turu #{{ $approval->round }}</span>
                            <x-badge :variant="$approvalVariant">
                                {{ UiLabels::designApprovalStatus($approval->status) }}
                            </x-badge>
                        </div>

                        @if($approval->design_file_path)
                            <div class="my-2">
                                <a href="{{ asset('storage/' . $approval->design_file_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-surface text-xs font-medium text-ink hover:border-cta hover:text-cta transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Yüklenen Prova Dosyasını İncele / İndir
                                </a>
                            </div>
                        @endif

                        @if($approval->vendor_note)
                            <div class="text-xs mt-2 p-3 bg-surface rounded-lg border border-border text-ink">
                                <strong class="text-muted block mb-0.5">Satıcı Açıklaması:</strong>
                                {{ $approval->vendor_note }}
                            </div>
                        @endif

                        @if($approval->customer_feedback)
                            <div class="text-xs mt-2 p-3 bg-red-50 text-red-900 rounded-lg border border-red-200">
                                <strong class="block mb-0.5">Revizyon Notunuz:</strong>
                                {{ $approval->customer_feedback }}
                            </div>
                        @endif

                        <div class="text-muted text-[11px] text-right mt-2">{{ $approval->created_at->format('d.m.Y H:i') }}</div>

                        {{-- Bekleyen prova varsa Müşteri Onay ve Revizyon Aksiyonları --}}
                        @if($approval->status === 'pending')
                            <div class="mt-4 pt-3 border-t border-border">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <form method="POST" action="{{ route('account.orders.design.approve', [$order, $approval]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-cta w-full text-xs py-2" onclick="return confirm('Baskı provasını onaylıyor musunuz? Onayınızla sipariş doğrudan üretime geçecektir.');">
                                            ✅ Provayı Onaylıyorum (Baskıya Geç)
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-secondary w-full text-xs py-2 text-red-600 hover:bg-red-50" onclick="document.getElementById('revision-form-{{ $approval->id }}').classList.toggle('hidden');">
                                        ✏️ Revizyon Talep Et
                                    </button>
                                </div>

                                <form id="revision-form-{{ $approval->id }}" method="POST" action="{{ route('account.orders.design.revision', [$order, $approval]) }}" class="hidden mt-3 p-4 bg-surface rounded-xl border border-border space-y-3">
                                    @csrf
                                    <label class="block text-xs font-semibold text-ink">Revizyon Notunuz / Düzeltilmesini İstediğiniz Noktalar:</label>
                                    <textarea name="customer_feedback" rows="3" class="form-control text-xs" required placeholder="Örn: Logo boyutu biraz daha büyütülebilir, iletişim bilgileri sola hizalanabilir..."></textarea>
                                    <button type="submit" class="btn btn-secondary text-xs text-red-600 hover:bg-red-50">Revizyon Talebini Gönder</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Genel Sipariş Detayları & Mağaza --}}
    <div class="by-card p-6 bg-surface border border-border mb-6">
        <h2 class="font-heading text-base font-bold text-ink mb-4 border-b border-border pb-3">Sipariş Bilgileri</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-muted block mb-0.5">Satıcı Mağaza:</span>
                @if($order->vendor)
                    <strong class="text-ink font-semibold">{{ $order->vendor->name }}</strong>
                    <a href="{{ route('vendors.show', $order->vendor->slug) }}" target="_blank" class="text-cta hover:underline font-medium ml-1">Mağaza Profili ↗</a>
                @else
                    <strong class="text-ink font-semibold">BaskıYeri Platformu</strong>
                @endif
            </div>
            @if($order->contractor)
                <div>
                    <span class="text-muted block mb-0.5">Freelancer Uzman:</span>
                    <strong class="text-ink font-semibold">{{ $order->contractor->name }}</strong>
                </div>
            @endif
            @if($order->quote?->quoteRequest)
                <div>
                    <span class="text-muted block mb-0.5">Bağlı Teklif Talebi:</span>
                    <a href="{{ route('quote-requests.show', $order->quote->quoteRequest) }}" class="text-cta hover:underline font-medium">
                        #{{ $order->quote->quoteRequest->id }} — {{ $order->quote->quoteRequest->title }}
                    </a>
                </div>
            @endif
            <div>
                <span class="text-muted block mb-0.5">Ödeme Yöntemi:</span>
                <strong class="text-ink font-semibold">{{ $order->payment_method === 'bank_transfer' ? 'Banka Havalesi / EFT' : ($order->payment_method === 'cash_on_delivery' ? 'Kapıda Ödeme' : 'Kredi / Banka Kartı') }}</strong>
            </div>
            @if($order->shipping_address)
                <div class="sm:col-span-2">
                    <span class="text-muted block mb-1">Teslimat Adresi:</span>
                    <div class="p-3 bg-canvas/60 rounded-lg border border-border text-ink">
                        {{ $order->shipping_address }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Sipariş Kalemleri --}}
    <div class="by-card bg-surface border border-border overflow-hidden mb-6">
        <div class="p-5 border-b border-border">
            <h2 class="font-heading text-base font-bold text-ink">Sipariş Kalemleri</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                        <th class="px-5 py-3">Ürün</th>
                        <th class="px-5 py-3">Adet</th>
                        <th class="px-5 py-3">Birim Fiyat</th>
                        <th class="px-5 py-3 text-right">Tutar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($order->items as $line)
                        <tr>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-ink">{{ $line->name }}</div>
                                @if($line->variant_name)
                                    <div class="text-xs text-muted mt-0.5">Varyant: {{ $line->variant_name }}</div>
                                @endif
                                @if($line->product)
                                    <a href="{{ route('products.show', $line->product->slug) }}" class="text-xs text-cta hover:underline mt-0.5 inline-block" target="_blank">Ürünü İncele ↗</a>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs font-medium text-ink">{{ $line->quantity }} adet</td>
                            <td class="px-5 py-4 text-xs text-muted">₺{{ number_format($line->price, 2, ',', '.') }}</td>
                            <td class="px-5 py-4 text-xs font-bold text-ink text-right">₺{{ number_format($line->price * $line->quantity, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-border bg-canvas/40">
                    <tr>
                        <td colspan="3" class="px-5 py-3.5 text-right font-bold text-ink text-sm">Genel Toplam:</td>
                        <td class="px-5 py-3.5 text-right font-extrabold text-cta text-base">₺{{ number_format($order->subtotal, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Değerlendirme & Yorum --}}
    @if(in_array($order->status, [OrderStatus::DELIVERED, OrderStatus::COMPLETED, 'delivered', 'completed'], true) && !$existingReview && $order->vendor)
        <div class="by-card p-6 bg-surface border border-border mb-6">
            <h2 class="font-heading text-base font-bold text-ink mb-1">Satıcıyı ve Alışverişi Değerlendirin</h2>
            <p class="text-xs text-muted mb-4">Siparişiniz tamamlandı. Satıcıya ve ürünlere dair deneyiminizi paylaşarak diğer kullanıcılara yardımcı olabilirsiniz.</p>
            <form action="{{ route('account.orders.review', $order) }}" method="post" id="orderReviewForm" class="space-y-4 max-w-lg">
                @csrf
                @if($reviewProductChoices->count() > 1)
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Değerlendirilen Ürün</label>
                        <select name="product_id" class="form-control text-xs" required>
                            @foreach($reviewProductChoices as $line)
                                <option value="{{ $line->product_id }}">{{ $line->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($reviewProductChoices->count() === 1)
                    <input type="hidden" name="product_id" value="{{ $reviewProductChoices->first()->product_id }}">
                @endif
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Puanınız (1–5)</label>
                    <select name="rating" id="orderReviewRating" class="form-control text-xs" required>
                        <option value="" selected disabled>Puan seçin</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }} yıldız</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Yorumunuz (İsteğe bağlı)</label>
                    <textarea name="comment" class="form-control text-xs" rows="3" maxlength="2000" placeholder="Deneyiminizi kısaca yazın..."></textarea>
                </div>
                <button type="submit" id="orderReviewSubmit" class="btn btn-cta text-xs py-2 px-4" disabled>Değerlendirmeyi Gönder</button>
            </form>
            <script>
                (function () {
                    var sel = document.getElementById('orderReviewRating');
                    var btn = document.getElementById('orderReviewSubmit');
                    if (!sel || !btn) return;
                    function sync() {
                        var v = sel.value;
                        btn.disabled = !v || v === '';
                    }
                    sel.addEventListener('change', sync);
                    sync();
                })();
            </script>
        </div>
    @elseif($existingReview)
        <div class="by-card p-4 bg-surface border border-border text-xs text-muted flex items-center gap-2 mb-6">
            <span>⭐</span>
            <span>Bu sipariş için değerlendirmeniz kaydedilmiştir. Teşekkür ederiz.</span>
        </div>
    @endif
@endsection
