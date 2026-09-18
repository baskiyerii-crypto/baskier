@extends('layouts.account')

@section('title', 'Sipariş #'.$order->order_number)

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;

    $statusBadgeClass = match($order->status) {
        OrderStatus::CONFIRMED, OrderStatus::PENDING, 'paid' => 'bg-primary text-white',
        OrderStatus::DESIGN_REVIEW => 'bg-warning text-dark',
        OrderStatus::IN_PRODUCTION => 'bg-info text-dark',
        OrderStatus::READY_TO_SHIP => 'bg-secondary text-white',
        OrderStatus::SHIPPED => 'bg-primary-subtle text-primary border border-primary',
        OrderStatus::DELIVERED, OrderStatus::COMPLETED => 'bg-success text-white',
        OrderStatus::CANCELLED => 'bg-danger text-white',
        default => 'bg-secondary text-white',
    };

    $shipment = $order->latestShipment;
    $trackingNo = $shipment?->tracking_number ?? $order->tracking_number;
    $carrierName = $shipment?->carrier ?? $order->carrier_code;
    $shippedDate = $shipment?->shipped_at ?? $order->shipped_at;
@endphp

    <nav class="mb-3">
        <a href="{{ route('account.orders.index') }}" class="small text-muted text-decoration-none">← Siparişlerim</a>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1">Sipariş #{{ $order->order_number }}</h1>
            <span class="text-muted small">Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }}</span>
        </div>
        <div>
            <span class="badge px-3 py-2 fs-6 rounded-pill {{ $statusBadgeClass }}">
                {{ UiLabels::orderStatus($order->status) }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Kargo & Gönderi Takip Kartı --}}
    @if($trackingNo || $shippedDate || in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED, OrderStatus::COMPLETED]))
        <div class="bg-white rounded-4 shadow-sm p-4 mb-4 border border-primary-subtle">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <h2 class="h6 fw-bold mb-0 text-primary">🚚 Kargo ve Gönderi Takibi</h2>
                <span class="badge bg-primary-subtle text-primary">
                    {{ in_array($order->status, [OrderStatus::DELIVERED, OrderStatus::COMPLETED]) ? 'Teslim Edildi' : 'Kargoda' }}
                </span>
            </div>
            <div class="row g-2 small mt-1">
                @if($carrierName)
                    <div class="col-sm-4">
                        <span class="text-muted d-block">Kargo Firması:</span>
                        <strong>{{ $carrierName }}</strong>
                    </div>
                @endif
                @if($trackingNo)
                    <div class="col-sm-4">
                        <span class="text-muted d-block">Takip Numarası:</span>
                        <strong class="font-monospace user-select-all">{{ $trackingNo }}</strong>
                    </div>
                @endif
                @if($shippedDate)
                    <div class="col-sm-4">
                        <span class="text-muted d-block">Kargoya Veriliş:</span>
                        <strong>{{ $shippedDate->format('d.m.Y H:i') }}</strong>
                    </div>
                @endif
            </div>
            @php
                $trackingUrl = UiLabels::carrierTrackingUrl($carrierName, $trackingNo);
            @endphp
            @if($trackingUrl)
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ $trackingUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                        🔗 Kargo Firmasının Sitesinde Canlı Takip Et ↗
                    </a>
                </div>
            @endif
        </div>
    @endif

    {{-- Dijital Baskı Provası & Onay Modülü --}}
    @if($order->designApprovals && $order->designApprovals->isNotEmpty())
        <div class="bg-white rounded-4 shadow-sm p-4 mb-4 border">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 border-bottom pb-2">
                <div>
                    <h2 class="h6 fw-bold mb-0">📄 Dijital Baskı Provası</h2>
                    <span class="small text-muted">Üretici tarafından yüklenen baskı provasını inceleyip onaylayabilir veya revizyon isteyebilirsiniz.</span>
                </div>
            </div>

            <div class="list-group mb-3">
                @foreach($order->designApprovals as $approval)
                    @php
                        $approvalBadge = match($approval->status) {
                            'approved' => 'bg-success text-white',
                            'revision_requested' => 'bg-danger text-white',
                            default => 'bg-warning text-dark',
                        };
                    @endphp
                    <div class="list-group-item p-3 rounded-3 mb-2 border">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <span class="fw-bold">Prova Turu #{{ $approval->round }}</span>
                            <span class="badge rounded-pill px-3 py-1.5 {{ $approvalBadge }}">
                                {{ UiLabels::designApprovalStatus($approval->status) }}
                            </span>
                        </div>

                        @if($approval->design_file_path)
                            <div class="my-2">
                                <a href="{{ route('account.orders.design.file', [$order, $approval]) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                                    👁️ Yüklenen Prova Dosyasını İncele / İndir
                                </a>
                            </div>
                        @endif

                        @if($approval->vendor_note)
                            <div class="small mt-2 p-2 bg-light rounded text-dark">
                                <strong>Satıcı Açıklaması:</strong> {{ $approval->vendor_note }}
                            </div>
                        @endif

                        @if($approval->customer_feedback)
                            <div class="small mt-2 p-2 bg-danger-subtle text-danger rounded border border-danger-subtle">
                                <strong>Revizyon Notunuz:</strong> {{ $approval->customer_feedback }}
                            </div>
                        @endif

                        <div class="text-muted small text-end mt-2">{{ $approval->created_at->format('d.m.Y H:i') }}</div>

                        {{-- Bekleyen prova varsa Müşteri Onay ve Revizyon Aksiyonları --}}
                        @if($approval->status === 'pending')
                            <div class="mt-3 pt-3 border-top">
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <form method="POST" action="{{ route('account.orders.design.approve', [$order, $approval]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100 fw-semibold btn-sm py-2" onclick="return confirm('Baskı provasını onaylıyor musunuz? Onayınızla sipariş üretime geçecektir.');">
                                                ✅ Provayı Onaylıyorum (Baskıya Geç)
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-sm-6">
                                        <button type="button" class="btn btn-outline-danger w-100 btn-sm py-2" onclick="document.getElementById('revision-form-{{ $approval->id }}').classList.toggle('d-none');">
                                            ✏️ Revizyon Talep Et
                                        </button>
                                    </div>
                                </div>

                                <form id="revision-form-{{ $approval->id }}" method="POST" action="{{ route('account.orders.design.revision', [$order, $approval]) }}" class="d-none mt-3 p-3 bg-light rounded border">
                                    @csrf
                                    <label class="form-label small fw-semibold">Revizyon Notunuz / Düzeltilmesini İstediğiniz Noktalar:</label>
                                    <textarea name="customer_feedback" rows="3" class="form-control form-control-sm mb-2" required placeholder="Örn: Logo boyutu biraz daha büyütülebilir, iletişim bilgileri sola hizalanabilir."></textarea>
                                    <button type="submit" class="btn btn-danger btn-sm">Revizyon Talebini Gönder</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Genel Sipariş Detayları & Mağaza --}}
    <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
        <h2 class="h6 fw-bold mb-3 border-bottom pb-2">Sipariş Bilgileri</h2>
        <div class="row g-3 small">
            <div class="col-sm-6">
                <span class="text-muted d-block">Satıcı Mağaza:</span>
                @if($order->vendor)
                    <strong>{{ $order->vendor->name }}</strong>
                    <a href="{{ route('vendors.show', $order->vendor->slug) }}" target="_blank" class="small ms-1 text-decoration-none">Mağaza Profili ↗</a>
                @else
                    <strong>BaskıYeri Platformu</strong>
                @endif
            </div>
            @if($order->contractor)
                <div class="col-sm-6">
                    <span class="text-muted d-block">Freelancer Uzman:</span>
                    <strong>{{ $order->contractor->name }}</strong>
                </div>
            @endif
            @if($order->quote?->quoteRequest)
                <div class="col-sm-6">
                    <span class="text-muted d-block">Bağlı Teklif Talebi:</span>
                    <a href="{{ route('quote-requests.show', $order->quote->quoteRequest) }}" class="fw-semibold">
                        #{{ $order->quote->quoteRequest->id }} — {{ $order->quote->quoteRequest->title }}
                    </a>
                </div>
            @endif
            <div class="col-sm-6">
                <span class="text-muted d-block">Ödeme Yöntemi:</span>
                <strong>{{ $order->payment_method === 'bank_transfer' ? 'Banka Havalesi / EFT' : ($order->payment_method === 'cash_on_delivery' ? 'Kapıda Ödeme' : 'Kredi / Banka Kartı') }}</strong>
            </div>
            @if($order->shipping_address)
                <div class="col-12">
                    <span class="text-muted d-block">Teslimat Adresi:</span>
                    <div class="p-2 bg-light rounded border mt-1">
                        {{ $order->shipping_address }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Sipariş Kalemleri --}}
    <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
        <h2 class="h6 fw-bold mb-3 border-bottom pb-2">Sipariş Kalemleri</h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Ürün</th>
                        <th>Adet</th>
                        <th>Birim Fiyat</th>
                        <th class="text-end">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $line)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $line->name }}</div>
                                @if($line->variant_name)
                                    <div class="text-muted small">Varyant: {{ $line->variant_name }}</div>
                                @endif
                                @if($line->product)
                                    <a href="{{ route('products.show', $line->product->slug) }}" class="small text-muted" target="_blank">Ürünü görüntüle ↗</a>
                                @endif
                            </td>
                            <td>{{ $line->quantity }} adet</td>
                            <td>₺{{ number_format($line->price, 2, ',', '.') }}</td>
                            <td class="text-end fw-bold">₺{{ number_format($line->price * $line->quantity, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-top">
                    <tr>
                        <td colspan="3" class="text-end fw-bold fs-6">Genel Toplam:</td>
                        <td class="text-end fw-bold fs-6 text-success">₺{{ number_format($order->subtotal, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Değerlendirme & Yorum --}}
    @if(in_array($order->status, [OrderStatus::DELIVERED, OrderStatus::COMPLETED, 'delivered', 'completed'], true) && !$existingReview && $order->vendor)
        <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
            <h2 class="h6 fw-bold mb-2">Satıcıyı ve Alışverişi Değerlendirin</h2>
            <p class="small text-muted mb-3">Siparişiniz tamamlandı. Satıcıya ve ürünlere dair deneyiminizi paylaşabilirsiniz.</p>
            <form action="{{ route('account.orders.review', $order) }}" method="post" id="orderReviewForm">
                @csrf
                @if($reviewProductChoices->count() > 1)
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Değerlendirilen ürün</label>
                        <select name="product_id" class="form-select form-select-sm" required>
                            @foreach($reviewProductChoices as $line)
                                <option value="{{ $line->product_id }}">{{ $line->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($reviewProductChoices->count() === 1)
                    <input type="hidden" name="product_id" value="{{ $reviewProductChoices->first()->product_id }}">
                @endif
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Puanınız (1–5)</label>
                    <select name="rating" id="orderReviewRating" class="form-select form-select-sm" required>
                        <option value="" selected disabled>Puan seçin</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }} yıldız</option>
                        @endfor
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Yorumunuz (isteğe bağlı)</label>
                    <textarea name="comment" class="form-control form-control-sm" rows="3" maxlength="2000" placeholder="Deneyiminizi kısaca yazın"></textarea>
                </div>
                <button type="submit" id="orderReviewSubmit" class="btn btn-warning rounded-pill px-4 fw-semibold btn-sm" disabled>Değerlendirmeyi Gönder</button>
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
        <div class="bg-white rounded-4 shadow-sm p-3 mb-4">
            <p class="small text-muted mb-0">⭐ Bu sipariş için değerlendirmeniz kaydedilmiştir. Teşekkür ederiz.</p>
        </div>
    @endif

    <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
        <h2 class="h6 fw-bold mb-2">Sipariş sorusu</h2>
        <p class="small text-muted">Siparişle ilgili sorularınız burada kalır; telefon veya sosyal medya paylaşmayın.</p>
        <form method="POST" action="{{ route('customer.orders.questions.store', $order) }}" class="mt-2">
            @csrf
            <input type="text" name="subject" class="form-control mb-2" placeholder="Konu (opsiyonel)" maxlength="180">
            <textarea name="body" class="form-control" rows="3" required maxlength="2000" placeholder="Sorunuz"></textarea>
            <button class="btn btn-primary btn-sm mt-2">Gönder</button>
        </form>
        <a href="{{ route('customer.order-questions.index') }}" class="small d-inline-block mt-2">Tüm sipariş sorularım</a>
    </div>
@endsection
