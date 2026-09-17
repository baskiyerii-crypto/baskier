# Flash 3.8 Görevi — BaskıYeri Global UI/UX Yenilemesi

Bu görev kullanıcı tarafından onaylanmıştır. Plan sunma veya ayrıca onay isteme. Doğrudan incelemeye ve uygulamaya başla. Görevi yalnız ana sayfayı değiştirerek tamamlanmış sayma.

Önce aşağıdaki kaynakları tamamen oku:

- `.cursorrules`
- `GLOBAL_UI_UX_YENILEME_PLANI.md`
- Mevcut canlıya alma ve sürdürülebilirlik belgeleri
- İlgili route, layout, controller, Blade, CSS, JavaScript ve test dosyaları

Bu prompttaki “doğrudan uygula” talimatı, `.cursorrules` içindeki kodlamadan önce kullanıcı onayı bekleme kuralı için verilmiş açık kullanıcı onayıdır. Kısa bir çalışma özeti paylaşabilirsin fakat yanıt beklemeden kodlamaya devam et.

## Görev

BaskıYeri'nin tüm web arayüzlerini `GLOBAL_UI_UX_YENILEME_PLANI.md` doğrultusunda modern, profesyonel, sade, hızlı ve sürdürülebilir bir tasarım sistemiyle yenile.

Tasarım modern bir baskı stüdyosu karakterinde olsun:

- Sıcak kırık beyaz yüzeyler
- Mürekkep tonunda güçlü metinler
- Kontrollü turuncu ana eylemler
- Güçlü fakat sade tipografi
- Gerçek baskı ürünlerine odaklanan görseller
- Ölçülü hareket ve efektler
- Alışılmış e-ticaret kullanım düzeni
- BaskıYeri'ne özgü küçük baskı/kesim detayları

Ana kullanıcı yolculuğu ürün aramak, ürün bulmak ve satın almaktır. Özel baskı, tabela ve tasarım teklifi alma ikinci belirgin yol olarak kalmalıdır.

## Zorunlu kapsam

Aşağıdaki web arayüzlerinin tamamını kontrol et ve yenile:

- Global header, footer, menüler ve mobil gezinme
- Ana sayfa
- Ürün listesi ve filtreler
- Ürün kartları
- Ürün detayları
- Satıcı listesi ve profilleri
- Sepet ve ödeme
- Teklif ve hizmet talebi akışları
- Giriş, kayıt ve OTP
- Müşteri hesabı
- Satıcı paneli
- Yönetim paneli
- Blog
- Kurumsal ve yasal sayfalar
- Boş, hata, başarı, yükleniyor ve yetkisiz erişim durumları

Native mobil uygulamayı değiştirme. Mevcut mobil API sözleşmelerini koru.

## Teknik sınırlar

- Laravel Blade, Tailwind CSS ve Vite kullan.
- React, Vue, Next.js veya yeni bir SPA katmanı ekleme.
- Ağır UI, animasyon veya ikon kütüphanesi ekleme.
- Mevcut route isimlerini ve form sözleşmelerini koru.
- Yalnız arayüz için veritabanı şemasını değiştirme.
- Ödeme, stok, yetkilendirme, teklif, güven seviyesi ve ürün dağıtım kurallarını bozma.
- Sahte yorum, güven rozeti, fiyat, sayaç veya teslimat iddiası üretme.
- Kullanıcının mevcut ve ilgisiz değişikliklerini silme veya geri alma.
- Üretime dağıtım yapma.
- Gerçek ödeme, e-posta, WhatsApp veya başka dış bildirim gönderme.

## Uygulama sırası

1. Route–Blade–layout–rol matrisi çıkar.
2. Mevcut tüm erişilebilir ekranları yerel ortamda aç; başlangıç ekran görüntülerini ve performans ölçümlerini kaydet.
3. Renk, tipografi, boşluk, radius, gölge, durum ve responsive tokenlarını oluştur.
4. Buton, form alanı, select, textarea, kart, rozet, uyarı, modal/drawer, tablo, sayfalama, breadcrumb, sayfa başlığı, boş durum ve SEO head ortak Blade bileşenlerini oluştur.
5. Vitrin, müşteri hesabı, satıcı paneli ve yönetim paneli layout'larını ortak sistemle yenile.
6. Ana sayfa, ürün listesi, ürün detayı, sepet ve ödeme akışını tamamla.
7. Teklif ve hizmet talebi akışlarını tamamla.
8. Müşteri, satıcı ve admin ekranlarının tamamını taşı.
9. Blog, kurumsal, yasal ve kimlik doğrulama ekranlarını tamamla.
10. SEO, performans, erişilebilirlik ve responsive doğrulamaları yap.
11. Yalnız kullanım kalmadığı kanıtlanan eski CSS, JavaScript ve bağımlılıkları kaldır.
12. Testleri çalıştır, hataları düzelt ve sonuçları raporla.

## Ana sayfa kuralları

- Otomatik slider'ı kaldır.
- Tek, güçlü ve responsive hero kullan.
- Hero içinde kısa değer önerisi, ürün arama ve ürünlere yönlendiren ana eylem olsun.
- Teklif alma daha düşük görsel ağırlıkta ikincil eylem olsun.
- Aynı hedefe giden tekrarlı kart ve butonları azalt.
- Kategoriler, öne çıkan ürünler ve gerçek güven bilgilerini taranabilir bir sırada sun.
- İlk ekranı büyük görseller veya gereksiz JavaScript ile ağırlaştırma.

## Ürün deneyimi kuralları

- Masaüstünde sol filtre, mobilde erişilebilir filtre drawer'ı kullan.
- Seçili filtreleri, sonuç sayısını ve temizleme işlemini göster.
- Filtre ve sıralama durumunu URL üzerinde koru.
- Sayfalamayı gerçek bağlantılarla uygula.
- Ürün kartında görsel, kategori, ürün adı, satıcı, fiyat ve tek ana eylem göster.
- Varyantlı, teklifli, dijital ve stok dışı ürünleri mevcut iş kurallarına göre ayır.
- Ürün detayında görsel, fiyat, varyant, stok, adet, satıcı ve satın alma eylemini birbirine yakın tut.
- Mobil satın alma alanını erişilebilir ve sabit öğelerle çakışmayacak biçimde uygula.

## Panel kuralları

- Müşteri panelinde siparişler ve bekleyen işlemler önde olsun.
- Satıcı panelinde sipariş, ürün, teklif ve mesaj işlemleri önde olsun.
- Admin panelinde onay kuyrukları, operasyon ve finans görünür olsun.
- Yoğun tablolar küçük ekranlarda okunabilir karta veya kontrollü yatay kaydırmaya dönüşsün.
- Kritik işlemleri açık metin, doğru durum rengi ve gerektiğinde onay diyaloğuyla göster.
- Admin ve satıcı sayfalarındaki `noindex,nofollow` davranışını koru.

## Tasarım sistemi

Temel renkleri şu değerlerle başlat ve erişilebilir durum tonlarını bunlardan türet:

- Zemin: `#F7F5F0`
- Kart: `#FFFFFF`
- Ana metin: `#182023`
- İkincil metin: `#596166`
- Ana eylem: `#C2410C`
- Kenarlık: `#DEDAD2`

Tipografiyi yerel olarak sunulan ve Türkçe karakter içeren tek variable WOFF2 dosyasıyla çöz. Font yüklenmezse sistem fontlarıyla okunabilir görünüm sağla.

Ana kontrollerde en az 44×44 px dokunma alanı hedefle. Klavye odağını görünür yap. `prefers-reduced-motion` desteğini koru.

## Performans gereksinimleri

- Hero görselini AVIF/WebP ve responsive `srcset` ile sun.
- Görsellerin genişlik ve yüksekliklerini tanımla.
- Ekran altındaki görselleri lazy-load et.
- İlk ekrandaki ana görseli doğru öncelikle yükle.
- Harici rastgele ürün görsellerini yerel, markalı placeholder ile değiştir.
- Chart ve Sortable gibi araçları yalnız ihtiyaç duyulan sayfalarda yükle.
- Tekrarlanan inline CSS ve JavaScript'i ortak derlenen dosyalara taşı.
- N+1 ve tekrarlanan sorguları ölçerek düzelt.
- Kullanıcıya özel sayfaları ortak önbelleğe alma.
- Service worker önbelleğinin yeni dosyalarla uyumlu olduğunu doğrula.

Hedefler:

- Mobil hero ≤180 KB
- İlk yüklemede sıkıştırılmış birinci taraf JavaScript ≤100 KB
- İlk yüklemede sıkıştırılmış CSS ≤60 KB
- LCP ≤2,5 saniye
- INP ≤200 ms
- CLS ≤0,1

## SEO gereksinimleri

Bu fazda Türkçe SEO'yu uygula. İngilizce arayüzü koru fakat ayrı İngilizce URL veya `hreflang` sistemi oluşturma.

- Ortak SEO head bileşeni oluştur.
- Başlık, açıklama, canonical, robots ve Open Graph verilerini sayfa bazında üret.
- `/sitemap.xml` oluştur.
- Ürün, kategori, satıcı ve blog canonical adreslerini kullan.
- Arama, sıralama, gereksiz filtre kombinasyonları, hesap, sepet, ödeme ve panel sayfalarını `noindex` yap.
- Her sayfalama sayfasının kendi canonical adresini kullanmasını sağla.
- Sitemap'e yalnız canonical ve indekslenebilir URL'leri ekle.
- Uygun sayfalara Product/Offer, BreadcrumbList, Organization ve BlogPosting JSON-LD ekle.
- Veritabanında olmayan fiyat, stok, değerlendirme veya işletme bilgisini şemaya yazma.
- Mevcut 301 yönlendirmeleri ve URL'leri koru.

## Test ve kabul

Aşağıdakileri doğrula:

- 360, 390, 768, 1024 ve 1440 px görünüm
- Klavye ile menü, drawer, modal, filtre ve form kullanımı
- Mobil menü açma/kapatma ve odak dönüşü
- Form hatasında girilen verilerin korunması
- Boş liste, stok dışı ürün ve görseli olmayan ürün
- Varyant seçimi ve fiyat/stok güncellemesi
- Sepete ekleme, adet değiştirme ve kaldırma
- Ödeme başarı ve hata durumları
- Teklif oluşturma ve görüntüleme
- Müşteri, satıcı ve admin rol erişimleri
- Canonical, robots, sitemap ve JSON-LD
- Service worker ve PWA güncelleme davranışı
- Üretim Vite build'i
- Mevcut PHP test paketi

Sabit mobil Lighthouse profilinde üç ölçüm yap ve medyanı raporla. Hedefler:

- Performance ≥90
- Accessibility ≥95
- SEO ≥95

Hedefe ulaşmayan ölçümlerde nedeni bul ve uygulanabilir düzeltmeleri tamamlamadan işi bitirme. Önceden var olan ve bu çalışmadan bağımsız engelleri kanıtlarıyla ayrı raporla.

## Teslim raporu

Görev sonunda şunları yaz:

- Değişen ekran aileleri
- Oluşturulan ortak bileşenler
- Kaldırılan eski bağımlılıklar
- SEO değişiklikleri
- Performans öncesi/sonrası sonuçları ve ölçüm koşulları
- Çalıştırılan testler
- Kontrol edilen route ve roller
- Erişilemeyen veya veri eksikliği nedeniyle doğrulanamayan ekranlar
- Kalan riskler

Tüm ekran aileleri tamamlanmadan görevi tamamlandı olarak işaretleme.
