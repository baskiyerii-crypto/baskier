# UX/UI düzenlemesi — devam notu (16 Eylül 2026)

## Kullanıcının isteği
BaskıYeri pazar, yönetim, satıcı ve freelancer akışlarının kullanıcı gibi taranması; modern, kullanılabilir arayüz ve teknik hataların giderilmesi. Kullanıcı “influencer” derken mevcut freelancer bölümünü kastettiğini doğruladı. Kullanım limiti kesintisi olursa aynı görevden devam edilmesini istedi.

## Tamamlanan değişiklikler
- **Sepet:** daralan adet alanı yerine sabit okunabilir kontrol; mobil kartlar, masaüstü tablo ve sipariş özeti, açık güncelle/kaldır işlemleri, stok uyarıları. Sepet sayaçları ürün adediyle tutarlı.
- **Katalog ve teklif formu:** geçersiz virgüllü CSS grid tanımları düzeltildi. Kategori değiştirirken arama ve ürün türü korunuyor; yanıltıcı rastgele katalog fotoğrafları kaldırıldı.
- **Yönetim/satıcı:** mobil açılır menü, Escape ve odak dönüşü, kapalı menünün klavye/erişilebilirlik ağacından çıkarılması, görünür etiketler, destek ve ödeme taleplerine yönetim menüsünden erişim. Satıcı script stack eklendi.
- **Hesap, ilan, ürün ve yönetim tablolarında taşan içerik için yatay kaydırma:** müşteri sipariş durumları Türkçeleştirildi.
- **Eksik storefront form/grid yardımcıları:** tamamlandı. Özellikle ödeme formunda d-none olmadığı için gizlenmeyen alanlar düzeltildi.
- **Form hataları:** ortak erişilebilir özette gösteriliyor; eski tekrar eden özetler kaldırıldı.
- **Freelancer:** ilan CTA'sı ilgili ilana gider. Freelancer aboneliğiyle talep açma/teklif verme doğru modülü kullanır. Kategori yetkisi ve kapanmış talep kontrolleri ücret/teklif işlemlerinde uygulanır. Freelancer detayındaki geri dönüş ve aktif menü düzeltildi. Harici picsum görseli şık yerel banner ile değiştirildi.
- **Ana sayfa ve ödeme metinleri:** sadeleştirildi. Sepet/ödemede satıcı ol reklamı kaldırıldı. Ödeme test modu kullanıcıya anlaşılır biçimde belirtilir.
- **Ödeme:** adres yokken JavaScript null hatası ve kısa sözleşmelerde kaydırma olmadığı için onay düğmesinin etkinleşmemesi düzeltildi.
- **Service worker:** eski ana sayfa HTML'sini süresiz tutup kaldırılan CSS dosyalarını çağıran cache-first davranışı kaldırıldı. Hesap HTML'si önbelleğe alınmaz. Çevrimdışı durumda yeniden dene sayfası var. Yalnız uygulamaya ait eski önbellekler temizlenir.
- **Sipariş durum makinesi senkronizasyonu:** Siparişlerin oluşturulurken `paid` yerine `OrderStatus::CONFIRMED` (`confirmed`) ve `payment_status = 'paid'` olarak kaydedilmesi sağlandı (`MarketplaceOrderService`, `QuoteRequestController`, `FreelancerJobWebController`, `Api/V1/*`). `OrderWorkflowService` içine `paid` durumu için geriye dönük uyumluluk eşlemesi eklendi. Satıcı paneli sipariş geçişleri kesintisiz çalışır hale getirildi.
- **Müşteri dijital baskı provası (Proofing) akışı:** `customer/orders/show.blade.php` ekranına dijital prova kartı eklendi. Müşteri yüklenen prova dosyasını açıp indirebilir, satıcı notunu görebilir, tek tıkla "Provayı Onaylıyorum" diyerek siparişi `in_production`'a geçirebilir veya revizyon talebi notu göndererek revizyon isteyebilir. `CustomerOrderDesignController` yetki kontrolü `can('designRespond', $order)` ile onarıldı.
- **Müşteri kargo ve gönderi takibi:** Sipariş kargolandığında müşteri detay ekranında kargo firması, takip barkodu ve kargoya veriliş tarihi renkli durum kartıyla gösterilir.
- **Müşteri sipariş listesi:** `customer/orders/index.blade.php` ekranına sipariş tarihi sütunu, Türkçe tür tanımları (`Pazaryeri`, `Özel Teklif`, `Freelancer`) ve semantik renkli durum rozetleri eklendi.
- **Ürün detay dinamik varyant & görseller:** `products/show.blade.php` ekranında varyant seçildiğinde fiyat ve stok durumu JavaScript ile canlı güncellenir; varyant stoku tükendiğinde satın alma butonu güvenle devre dışı bırakılır. Harici `picsum.photos` bağımlılıkları temizlenip yerel SVG/CSS placeholder'lar yerleştirildi (`products/show`, `favorites/index`, `freelancer-jobs/show`).

## Doğrulama
- `php artisan test --compact`: 31 test, 146 assertion başarılı.
- `php artisan test tests/Feature/CustomerOrderWorkflowTest.php`: 5 test (prova onayı, revizyon, kargo takip, sipariş listesi, teklif siparişi) başarılı.
- `node --test --test-isolation=none tests/Feature/service-worker.test.mjs`: 4 test başarılı.
- `php artisan view:cache`: Başarılı (tüm Blade şablonları hatasız derlendi).
- `git diff --check`: Başarılı (0 whitespace/syntax hatası).
- Gerçek ödeme, gerçek kargo, e-posta veya müşteriye mesaj gönderilmedi. Tüm iş akışlarının canlı entegrasyonlarla uçtan uca testi yapıldığı iddia edilmemeli.

## Devam / yayın öncesi
1. Kullanıcı devam istediğinde mevcut diff'i koruyarak bu nottan ilerle; tamamlanan işleri yeniden yapma.
2. Canlı hedef/erişim sağlandığında migration ve build dağıtımı gerekir. Normal veritabanındaki bekleyen roadmap migration kapsamını incelemeden otomatik canlı dağıtım yapma.
3. İleriki aşamalarda canlı sağlayıcı ortamları (ör. İyzico, BasitKargo API canlı modları) entegrasyon anahtarları tanımlandığında test edilebilir.
