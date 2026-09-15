# BaskıYeri: Satıcı Paneli Tamir, Restorasyon ve İyileştirme Teknik Şartnamesi (Agent Eylem Planı)

Bu belge; yapay zeka kodlama ajanlarının veya yazılımcıların BaskıYeri Satıcı Panelindeki (`/satici-panel`) tüm kritik mantık hatalarını, çalışmayan durum makinelerini, kopuk arayüzleri ve eksik şablonları **sıfır belirsizlikle, tek seferde ve kusursuzca** onarabilmesi için hazırlanmış kesin teknik uygulama şartnamesidir.

---

## 🎯 Temel Hedef ve Kapsam

Sistemde backend servisleri (`OrderWorkflowService`, `DesignApproval`, `CommissionService`, `UiLabels`) büyük oranda yazılmış olmasına rağmen; satıcı arayüzleri (`resources/views/vendor/`) ile controller'lar arasında ciddi kopukluklar ve eski statü kodları bulunmaktadır. Bu şartname aşağıdaki 6 ana görevi sırasıyla çözer:

1. **Sipariş Durum Makinesi ve Controller Restorasyonu** (`OrderWorkflowService` tam entegrasyonu)
2. **Sipariş Detay Ekranı (`orders/show.blade.php`) Sıfırdan İnşası** (Ürün kalemleri, teslimat adresi, dijital prova yükleme, kargo takip)
3. **Sipariş Listesi (`orders/index.blade.php`) Sekmeli Modernizasyonu** (Durum sekmeleri, Türkçe rozetler, arama)
4. **Dashboard ve Layout İyileştirmesi** (CSS çatışmalarının giderilmesi, mobil drawer, gerçek KPI'lar ve sayaçlar)
5. **Ürün Yönetimi Düzeltmeleri** (JS görsel gizleme hatasının onarımı, termin süresi ve kişiselleştirme alanları)
6. **Finans, Cüzdan ve Eksik Menülerin Bağlanması** (Ödeme talepleri linki, IBAN tanımlama, net hakediş şeffaflığı)

---

## 📂 Dizin ve Dosya Haritası

```
f:\dev\baskiyeripazar\baskier\
├── app/
│   ├── Domain/
│   │   └── OrderStatus.php                     <- Referans statü sabitleri
│   ├── Http/Controllers/Vendor/
│   │   ├── VendorDashboardController.php       <- DÜZELTİLECEK (Sorgu ve statü sayacı)
│   │   ├── VendorOrderController.php           <- DÜZELTİLECEK (Durum makinesi ve kargo entegrasyonu)
│   │   ├── VendorOrderDesignController.php     <- MEVCUT (Prova yükleme servisi)
│   │   ├── VendorProductController.php         <- DÜZELTİLECEK (Validasyon ve alanlar)
│   │   ├── VendorPayoutRequestWebController.php<- DÜZELTİLECEK (Bakiye rezerv kontrolü)
│   │   └── VendorProfileController.php         <- YENİ (Mağaza ve IBAN ayarları)
│   ├── Models/
│   │   ├── Order.php                           <- DÜZELTİLECEK (Shipment ilişkisi)
│   │   └── Vendor.php                          <- DÜZELTİLECEK (payoutRequests ilişkisi)
│   └── Services/
│       └── OrderWorkflowService.php            <- Kullanılacak durum makinesi
├── resources/views/
│   ├── layouts/
│   │   └── vendor.blade.php                    <- DÜZELTİLECEK (Menü, responsive drawer, CSS temizliği)
│   └── vendor/
│       ├── dashboard.blade.php                 <- DÜZELTİLECEK (Gerçek metrikler, son siparişler)
│       ├── orders/
│       │   ├── index.blade.php                 <- YENİDEN YAZILACAK (Filtreli sekmeler, rozetler)
│       │   └── show.blade.php                  <- YENİDEN YAZILACAK (Detay, prova yükleme, kargo girişi)
│       ├── products/
│       │   ├── create.blade.php                <- DÜZELTİLECEK (JS hatası ve yeni alanlar)
│       │   ├── edit.blade.php                  <- DÜZELTİLECEK (JS hatası ve yeni alanlar)
│       │   └── index.blade.php                 <- DÜZELTİLECEK (Görseller ve filtre)
│       ├── payout-requests/
│       │   └── index.blade.php                 <- DÜZELTİLECEK (IBAN alanı ve net bakiye)
│       └── profile/
│           └── edit.blade.php                  <- YENİ (Mağaza ve fatura/IBAN bilgileri)
└── routes/
    └── web.php                                 <- DÜZELTİLECEK (Yeni route'lar eklenecek)
```

---

## 🛠️ GÖREV 1: Sipariş Durum Makinesi ve Controller Onarımı

### 1.1. Problem Tespiti
- `VendorDashboardController.php` içinde satıcının aktif siparişleri aranırken `whereIn('status', ['paid', 'in_progress'])` yazılmıştır. Ancak veritabanı migrasyonu bu statüleri `confirmed` ve `in_production` olarak değiştirmiştir. Sonuç: Sayaç daima 0 döner.
- `VendorOrderController.php` içinde durum güncellemesi yapılırken `$request->validate(['status' => ['required', 'in:in_progress,delivered']])` doğrudan çalıştırılmakta; `OrderWorkflowService` baypas edilmekte ve `shipped`, `ready_to_ship`, `design_review` adımları işletilememektedir.

### 1.2. Yapılacak Değişiklikler

#### [MODIFY] `app/Http/Controllers/Vendor/VendorDashboardController.php`
```php
// AKTİF SİPARİŞ SAYACI DÜZELTMESİ:
use App\Domain\OrderStatus;

$ordersPending = Order::where('vendor_id', $vendor->id)
    ->whereIn('status', [
        OrderStatus::CONFIRMED,
        OrderStatus::DESIGN_REVIEW,
        OrderStatus::IN_PRODUCTION,
        OrderStatus::READY_TO_SHIP,
        OrderStatus::SHIPPED,
    ])->count();

// EKLENECEK YENİ METRİKLER:
$proofPendingCount = Order::where('vendor_id', $vendor->id)
    ->where('status', OrderStatus::DESIGN_REVIEW)
    ->count();

$readyToShipCount = Order::where('vendor_id', $vendor->id)
    ->whereIn('status', [OrderStatus::IN_PRODUCTION, OrderStatus::READY_TO_SHIP])
    ->count();

$totalRevenue = Order::where('vendor_id', $vendor->id)
    ->whereNotIn('status', [OrderStatus::CANCELLED, OrderStatus::PENDING])
    ->sum('vendor_amount');

$recentOrders = Order::where('vendor_id', $vendor->id)
    ->with(['user', 'items.product'])
    ->latest()
    ->limit(5)
    ->get();
```

#### [MODIFY] `app/Http/Controllers/Vendor/VendorOrderController.php`
`updateStatus` metodu `OrderWorkflowService` ve kargo takip bilgilerini destekleyecek şekilde güncellenmelidir:

```php
use App\Domain\OrderStatus;
use App\Models\Shipment;
use App\Services\OrderWorkflowService;

public function show(Request $request, Order $order)
{
    $vendor = $this->getVendor($request);
    if ($order->vendor_id !== $vendor->id) {
        abort(403);
    }
    $order->load([
        'user',
        'quote.quoteRequest',
        'items.product',
        'items.variant',
        'designApprovals' => fn($q) => $q->latest('id'),
        'shippingAddress',
        'billingAddress'
    ]);

    $allowedTransitions = app(OrderWorkflowService::class)->allowedTransitions()[$order->status] ?? [];

    return view('vendor.orders.show', compact('order', 'vendor', 'allowedTransitions'));
}

public function updateStatus(Request $request, Order $order, OrderWorkflowService $workflow)
{
    $vendor = $this->getVendor($request);
    if ($order->vendor_id !== $vendor->id) {
        abort(403);
    }

    $validated = $request->validate([
        'status' => ['required', 'string'],
        'carrier' => ['nullable', 'string', 'max:100'],
        'tracking_number' => ['nullable', 'string', 'max:100'],
    ]);

    $targetStatus = $validated['status'];

    if (! $workflow->canTransition($order, $targetStatus)) {
        return back()->with('error', "Sipariş durumu {$order->status} durumundan {$targetStatus} durumuna geçirilemez.");
    }

    // Kargo çıkışı yapılıyorsa kargo bilgilerini kaydet
    if ($targetStatus === OrderStatus::SHIPPED) {
        $request->validate([
            'carrier' => ['required', 'string'],
            'tracking_number' => ['required', 'string'],
        ]);

        Shipment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'carrier' => $validated['carrier'],
                'tracking_number' => $validated['tracking_number'],
                'shipped_at' => now(),
            ]
        );
    }

    $workflow->transition($order, $targetStatus, $request->user());

    return back()->with('success', 'Sipariş durumu güncellendi: ' . \App\Support\UiLabels::orderStatus($targetStatus));
}
```

---

## 🎨 GÖREV 2: Sipariş Detay Ekranının (`orders/show.blade.php`) Baştan Yazılması

### 2.1. Problem Tespiti
Mevcut şablon 26 satırdan ibarettir; sipariş içindeki kalemleri, müşterinin adresini, tasarım dosyalarını ve satıcının prova yükleme formunu barındırmamaktadır.

### 2.2. Uygulama Talimatı
`resources/views/vendor/orders/show.blade.php` dosyasını aşağıdaki tam teşekküllü arayüz ile değiştirin:

```blade
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
        <span class="badge px-3 py-2 fs-6 rounded-pill {{ $statusBadgeClass }}">
            {{ UiLabels::orderStatus($order->status) }}
        </span>
    </div>
</div>

<div class="row g-3">
    <!-- Sol Sütun: Sipariş Kalemleri ve Prova Süreci -->
    <div class="col-lg-8">
        <!-- 1. Sipariş Kalemleri -->
        <div class="card p-3 mb-3">
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
                                <td colspan="5" class="text-muted text-center py-3">Bu siparişe ait kalem bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <td colspan="4" class="text-end fw-semibold">Ara Toplam:</td>
                            <td class="text-end fw-bold">₺{{ number_format($order->subtotal, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end text-muted small">Komisyon Kesintisi (%{{ $order->commission_rate }}):</td>
                            <td class="text-end text-danger small">-₺{{ number_format($order->commission_amount, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="4" class="text-end fw-bold text-success">Net Satıcı Hakedişi:</td>
                            <td class="text-end fw-bold text-success fs-6">₺{{ number_format($order->vendor_amount, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 2. Dijital Prova ve Tasarım Onay Modülü (Özel Baskılı Ürünler İçin) -->
        <div class="card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h2 class="h6 fw-bold mb-0">Dijital Baskı Provası (Proofing)</h2>
                <span class="small text-muted">Müşteri onayından sonra baskıya geçilir</span>
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
                                        📄 Yüklenen Prova Dosyasını Aç / İndir
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
                    Bu sipariş için henüz bir dijital prova yüklenmedi.
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
                    <textarea name="vendor_note" rows="2" class="form-control form-control-sm" placeholder="Örn: Renkler CMYK kodlarına göre ayarlandı, onayınızı rica ederiz."></textarea>
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
                    <div class="fw-semibold small mb-2">Kargoya Teslim Et</div>
                    <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="{{ OrderStatus::SHIPPED }}">
                        <div class="mb-2">
                            <label class="form-label small">Kargo Firması</label>
                            <select name="carrier" class="form-select form-select-sm" required>
                                <option value="Yurtiçi Kargo">Yurtiçi Kargo</option>
                                <option value="Aras Kargo">Aras Kargo</option>
                                <option value="MNG Kargo">MNG Kargo</option>
                                <option value="Sürat Kargo">Sürat Kargo</option>
                                <option value="PTT Kargo">PTT Kargo</option>
                                <option value="Özel Kurye / Dağıtım">Özel Kurye / Dağıtım</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Takip Numarası</label>
                            <input type="text" name="tracking_number" class="form-control form-control-sm" required placeholder="Takip barkod no">
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-semibold btn-sm">
                            🚚 Kargoya Verildi Olarak İşaretle
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
        </div>

        <!-- 2. Müşteri & Teslimat Bilgileri -->
        <div class="card p-3 mb-3">
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
                    <span class="text-muted d-block">Müşteri Notu:</span>
                    <div class="p-2 bg-warning-subtle rounded border border-warning text-dark">
                        {{ $order->notes }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
```

---

## 📑 GÖREV 3: Sipariş Listesi Ekranının (`orders/index.blade.php`) Yenilenmesi

### 3.1. Problem Tespiti
Mevcut ekran statü sekmelerine, arama çubuğuna ve renkli Türkçe rozetlere sahip değildir.

### 3.2. Uygulama Talimatı
`VendorOrderController@index` metodunu statü ve arama filtresini destekleyecek şekilde düzenleyin:

```php
public function index(Request $request)
{
    $vendor = $this->getVendor($request);
    $query = $vendor->orders()->with(['user', 'items'])->latest();

    if ($status = $request->input('status')) {
        $query->where('status', $status);
    }

    if ($search = $request->input('q')) {
        $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
              ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
        });
    }

    $orders = $query->paginate(20)->withQueryString();

    return view('vendor.orders.index', compact('vendor', 'orders'));
}
```

`resources/views/vendor/orders/index.blade.php` dosyasını şu arayüzle güncelleyin:

```blade
@extends('layouts.vendor')

@section('title', 'Siparişler')

@section('content')
@php
    use App\Support\UiLabels;
    use App\Domain\OrderStatus;
    $currentStatus = request('status', '');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h5 fw-bold mb-0">Sipariş Yönetimi</h1>
    <form method="GET" action="{{ route('vendor.orders.index') }}" class="d-flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Sipariş no veya alıcı ara...">
        <button type="submit" class="btn btn-secondary btn-sm">Ara</button>
        @if(request()->hasAny(['q', 'status']))
            <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary btn-sm">Sıfırla</a>
        @endif
    </form>
</div>

<!-- Durum Sekmeleri -->
<ul class="nav nav-pills mb-3 gap-1 bg-white p-2 rounded border small">
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === '' ? 'active' : '' }}" href="{{ route('vendor.orders.index') }}">Tümü</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::CONFIRMED ? 'active' : '' }}" href="{{ route('vendor.orders.index', ['status' => OrderStatus::CONFIRMED]) }}">Yeni Onaylanan</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::DESIGN_REVIEW ? 'active' : '' }}" href="{{ route('vendor.orders.index', ['status' => OrderStatus::DESIGN_REVIEW]) }}">Prova Bekleyen</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::IN_PRODUCTION ? 'active' : '' }}" href="{{ route('vendor.orders.index', ['status' => OrderStatus::IN_PRODUCTION]) }}">Üretimde</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::SHIPPED ? 'active' : '' }}" href="{{ route('vendor.orders.index', ['status' => OrderStatus::SHIPPED]) }}">Kargoda</a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-1 px-3 {{ $currentStatus === OrderStatus::DELIVERED ? 'active' : '' }}" href="{{ route('vendor.orders.index', ['status' => OrderStatus::DELIVERED]) }}">Tamamlanan</a>
    </li>
</ul>

<div class="card overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Sipariş No</th>
                    <th>Tarih</th>
                    <th>Müşteri</th>
                    <th>Kalemler</th>
                    <th>Net Kazanç</th>
                    <th>Durum</th>
                    <th class="text-end">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="fw-bold">{{ $order->order_number }}</td>
                        <td class="small text-muted">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $order->user?->name ?? 'Misafir Alıcı' }}</td>
                        <td class="small text-muted">{{ $order->items->pluck('name')->join(', ') ?: 'Kalem detayı yok' }}</td>
                        <td class="fw-bold text-success">₺{{ number_format($order->vendor_amount, 2, ',', '.') }}</td>
                        <td>
                            <span class="badge rounded-pill
                                @if(in_array($order->status, [OrderStatus::CONFIRMED, OrderStatus::PENDING])) bg-primary
                                @elseif($order->status === OrderStatus::DESIGN_REVIEW) bg-warning text-dark
                                @elseif($order->status === OrderStatus::IN_PRODUCTION) bg-info text-dark
                                @elseif($order->status === OrderStatus::SHIPPED) bg-primary-subtle text-primary
                                @elseif($order->status === OrderStatus::DELIVERED) bg-success
                                @else bg-secondary @endif">
                                {{ UiLabels::orderStatus($order->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('vendor.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">İncele & Yönet →</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Bu kriterlere uygun sipariş bulunamadı.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
```

---

## 🖥️ GÖREV 4: Layout (`layouts/vendor.blade.php`) ve Menü Restorasyonu

### 4.1. Problem Tespiti
- Sol kenar çubuğunda **Ödeme Talepleri** bağlantısı yoktur.
- Mağaza profili / IBAN ayarları bağlantısı yoktur.
- "Tabela bakiyesi" üreticiyi yanıltmaktadır.
- Bootstrap ve Tailwind sınıfları aynı anda yüklendiği için layout bozulmaları yaşanmaktadır.

### 4.2. Yapılacak Değişiklikler
`resources/views/layouts/vendor.blade.php` dosyasında sol menü `nav` bloğunu şu şekilde güncelleyin:

```blade
<nav class="nav">
    <a href="{{ route('vendor.dashboard') }}" class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
        <span>Özet & Dashboard</span>
    </a>
    <a href="{{ route('vendor.orders.index') }}" class="nav-link {{ request()->routeIs('vendor.orders.*') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span>Siparişler</span>
    </a>
    <a href="{{ route('vendor.products.index') }}" class="nav-link {{ request()->routeIs('vendor.products.*') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <span>Ürünlerim</span>
    </a>
    <a href="{{ route('vendor.quote-requests.index') }}" class="nav-link {{ request()->routeIs('vendor.quote-requests.*') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <span>Teklif Talepleri</span>
    </a>
    <a href="{{ route('vendor.payout-requests.index') }}" class="nav-link {{ request()->routeIs('vendor.payout-requests.*') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <span>Hakediş & Para Çekme</span>
    </a>
    <a href="{{ route('vendor.balance.index') }}" class="nav-link {{ request()->routeIs('vendor.balance.*') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg>
        <span>Görüşme Bakiyesi</span>
    </a>
    <a href="{{ route('vendor.messages.index') }}" class="nav-link {{ request()->routeIs('vendor.messages.*') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>Müşteri Mesajları</span>
    </a>
    <a href="{{ route('vendor.subscriptions.index') }}" class="nav-link {{ request()->routeIs('vendor.subscriptions.*') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <span>Modüller & Abonelik</span>
    </a>
    @if(auth()->user()->vendor)
    <a href="{{ route('vendors.show', auth()->user()->vendor->slug) }}" target="_blank" rel="noopener" class="nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        <span>Vitrin Mağazam ↗</span>
    </a>
    @endif
</nav>
```

---

## 📦 GÖREV 5: Ürün Yönetimi JS ve Alan Düzeltmeleri

### 5.1. Problem Tespiti
`resources/views/vendor/products/create.blade.php` dosyasındaki javascript satırında:
```js
document.querySelector('[name=product_type]').addEventListener('change', function(){
    var isDigital = this.value==='digital';
    document.querySelector('.product-digital-field').style.display = isDigital ? 'none' : 'block';
    document.querySelector('.product-digital-link-field').style.display = isDigital ? 'block' : 'none';
});
```
`.product-digital-field` sınıfı görsel alanına verilmiştir! Bu nedenle satıcı dijital ürün seçtiğinde **ürün görseli yükleme alanı kaybolmaktadır**. Dijital pazarda da kapak görseli zorunludur.

### 5.2. Yapılacak Değişiklik
`create.blade.php` ve `edit.blade.php` dosyalarında görsel alanındaki `.product-digital-field` sınıfını kaldırın. Görsel her iki ürün tipi için de görünür kalmalıdır; yalnızca `.product-digital-link-field` dijital seçildiğinde açılmalıdır.

---

## 💰 GÖREV 6: Hakediş ve Ödeme Güvenliği (`VendorPayoutRequestWebController.php`)

### 6.1. Problem Tespiti
Satıcı talep oluşturduğunda bakiye rezerv edilmemektedir.

### 6.2. Yapılacak Değişiklik
`app/Http/Controllers/Vendor/VendorPayoutRequestWebController.php` içinde `store` metodunda açıkta bekleyen taleplerin toplamını kontrol edin:

```php
public function store(Request $request)
{
    $vendor = $this->vendor($request);
    $validated = $request->validate([
        'amount' => ['required', 'numeric', 'min:50'],
        'iban' => ['required', 'string', 'regex:/^TR[0-9]{24}$/i'],
        'account_holder' => ['required', 'string', 'max:150'],
    ]);

    $amount = (float) $validated['amount'];

    // Bekleyen çekim taleplerini topla
    $pendingTotal = PayoutRequest::where('vendor_id', $vendor->id)
        ->where('status', 'pending')
        ->sum('amount');

    $availableBalance = (float) $vendor->balance - (float) $pendingTotal;

    if ($amount > $availableBalance) {
        return back()->with('error', 'Yetersiz çekilebilir bakiye. Açıkta bekleyen talepleriniz düşüldükten sonra çekebileceğiniz tutar: ₺' . number_format(max(0, $availableBalance), 2, ',', '.'));
    }

    PayoutRequest::create([
        'vendor_id' => $vendor->id,
        'amount' => $amount,
        'status' => 'pending',
        'admin_note' => 'IBAN: ' . strtoupper($validated['iban']) . ' | Alıcı: ' . $validated['account_holder'],
    ]);

    return back()->with('success', 'Ödeme talebiniz oluşturuldu. Finans birimi onayından sonra IBAN hesabınıza aktarılacaktır.');
}
```

---

## 🧪 Doğrulama ve Test Adımları

Ajan bu adımları sırasıyla doğrulamalıdır:

1. **Syntax ve Derleme Kontrolü:**
   - Değiştirilen tüm Blade dosyalarında tag kapatma, `@csrf`, `@method` ve PHP syntax hatası olmadığını teyit et.
2. **Dashboard Sayacı Doğrulaması:**
   - Veritabanında `status = 'confirmed'` veya `'in_production'` olan bir sipariş olduğunda dashboard sayacının sıfır değil, doğru sayıyı verdiğini kontrol et.
3. **Sipariş Akışı Doğrulaması:**
   - `vendor.orders.show` rotasına gidildiğinde ürün adı, adet, fiyat ve net hakedişin doğru basıldığını gör.
   - Prova yükleme formunun `VendorOrderDesignController@store` metoduna başarıyla POST attığını doğrula.
   - Duruma göre (Örn. `ready_to_ship` iken) kargo takip formu doldurulup gönderildiğinde sipariş durumunun `shipped` olduğunu ve `shipments` tablosuna kayıt düştüğünü test et.
4. **Menü Doğrulaması:**
   - Kenar çubuğunda "Hakediş & Para Çekme" bağlantısının doğrudan `/satici-panel/odeme-talepleri` rotasına yönlendirdiğini teyit et.
