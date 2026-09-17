# BaskıYeri Global UI/UX, Hız ve SEO Yenileme Planı

## 1. Amaç ve başarı ölçütleri

BaskıYeri'nin tüm web arayüzleri modern, profesyonel, sade ve hızlı bir tasarım sistemi altında birleştirilecektir. Kullanıcıların alışık olduğu e-ticaret davranışları korunurken marka, modern bir baskı stüdyosu görünümüyle farklılaşacaktır.

Ana kullanıcı yolculuğu ürün arama, ürün keşfetme ve satın almadır. Özel baskı, tabela ve tasarım için teklif alma ikinci belirgin yol olacaktır.

Çalışma şu alanları kapsar:

- Vitrin, global navigasyon ve footer
- Ana sayfa
- Ürün ve satıcı liste/detay sayfaları
- Sepet, ödeme ve teklif akışları
- Giriş, kayıt ve OTP ekranları
- Müşteri hesabı
- Satıcı paneli
- Yönetim paneli
- Blog, kurumsal ve yasal sayfalar
- Boş, hata, başarı, yükleniyor ve yetkisiz erişim durumları

Native mobil uygulama kapsam dışındadır. Mevcut mobil API sözleşmeleri korunacaktır.

## 2. Mevcut durum

- Projede 119 Blade şablonu ve vitrin, müşteri, satıcı ve yönetim için dört layout ailesi bulunmaktadır.
- Tailwind, Bootstrap, uyumluluk sınıfları, inline CSS ve inline JavaScript birlikte kullanılmaktadır.
- Ana sayfadaki slider ve tekrarlanan yönlendirme alanları ilk ekranı yoğunlaştırmaktadır.
- Ana sayfadaki sekiz PNG görselin toplam boyutu yaklaşık 2,23 MB'dir.
- Görseli olmayan ürünlerde harici rastgele fotoğraf servisi kullanılmaktadır.
- Ortak canonical, meta açıklaması, Open Graph, yapılandırılmış veri ve sitemap altyapısı bulunmamaktadır.
- Türkçe ve İngilizce seçimi aynı URL üzerinde çerez/oturum ile çalışmaktadır.

## 3. Görsel yön ve tasarım sistemi

Tasarımın karakteri modern bir baskı stüdyosu olacaktır. Güçlü tipografi, gerçek ürün fotoğrafları, temiz yüzeyler ve kontrollü vurgu renkleri kullanılacaktır. Etki; yoğun animasyon veya efekt yerine kompozisyon, boşluk ve içerik hiyerarşisiyle sağlanacaktır.

### Temel tokenlar

| Alan | Değer |
| --- | --- |
| Ana zemin | Sıcak kırık beyaz `#F7F5F0` |
| Kart ve form yüzeyi | Beyaz `#FFFFFF` |
| Ana metin | Mürekkep tonu `#182023` |
| İkincil metin | Gri `#596166` |
| Ana eylem | Koyu turuncu `#C2410C`, beyaz yazı |
| Kenarlık | Açık nötr `#DEDAD2` |
| Kontrol radius | 8 px |
| Kart radius | 12 px |
| Özel vitrin radius | En fazla 20 px |
| Boşluk ölçeği | 4/8/12/16/24/32/48/64 px |
| Geçişler | 120–180 ms |

- Türkçe karakter desteği olan tek bir variable WOFF2 font yerel olarak sunulacaktır.
- İkonlarda yalnız kullanılan SVG'lerden oluşan tek çizgi dili kullanılacaktır.
- `prefers-reduced-motion` desteği korunacaktır.
- Normal metinlerde en az 4,5:1 kontrast hedeflenecektir.
- Ana dokunma alanlarında 44×44 px tasarım hedefi kullanılacaktır.

## 4. Arayüz kararları

### Global gezinme

- Logo, belirgin ürün araması, ürün kategorileri, teklif al, hesap ve sepet bulunacaktır.
- Masaüstünde ana seçenekler görünür olacak; mobilde klavye ve ekran okuyucu uyumlu drawer kullanılacaktır.
- Mobil vitrinde Ana Sayfa, Ürünler, Sepet ve Hesabım alt gezinmesi kullanılacaktır.
- Ödeme ekranı ve paneller kendi sade navigasyonunu kullanacaktır.
- Sabit öğeler içerik ve ana eylemleri kapatmayacaktır.

### Ana sayfa

- Otomatik slider kaldırılarak tek, güçlü ve responsive hero kullanılacaktır.
- Hero içinde kısa değer önerisi, ürün arama ve ürünlere giden ana eylem olacaktır.
- Teklif alma daha düşük görsel ağırlıkla ikincil eylem olacaktır.
- Aynı hedefe giden tekrarlı kart ve butonlar azaltılacaktır.
- Kategoriler, adil ürün dağıtımını kullanan ürün keşfi, özel üretim ve gerçek güven bilgileri açık bir sırada sunulacaktır.

### Ürün listesi ve kartları

- Masaüstünde sol filtre, mobilde filtre drawer'ı kullanılacaktır.
- Sonuç sayısı, seçili filtreler, temizleme işlemi ve sıralama görünür olacaktır.
- Filtre ve sıralama durumu URL'de korunacaktır.
- Sayfalama gerçek bağlantılarla çalışacaktır.
- Ürün kartında 4:3 görsel, kategori, ürün adı, satıcı, fiyat ve tek ana eylem yer alacaktır.
- Varyantlı, teklifli, dijital ve stok dışı ürünlerin mevcut iş kuralları korunacaktır.

### Ürün detayı, sepet ve ödeme

- Ürün görseli, fiyat, varyant, stok, adet, satıcı ve satın alma eylemi birbirine yakın konumlandırılacaktır.
- Masaüstünde görsel ve satın alma alanı yan yana; mobilde mantıklı içerik sırasında gösterilecektir.
- Sepette ürünler, adet kontrolleri ve toplamlar kolay taranacaktır.
- Ödemede dikkat dağıtmayan düzen, görünür hata/başarı durumu ve mevcut veriye dayanan ücret açıklamaları kullanılacaktır.

### Teklif ve hizmet talepleri

- Formlar ihtiyaç, teknik bilgiler, dosyalar ve iletişim şeklinde mantıksal gruplara ayrılacaktır.
- Mevcut talep türleri, yetkiler ve validasyon kuralları korunacaktır.
- Form hatasında kullanıcının girdiği bilgiler kaybolmayacaktır.

### Hesap ve paneller

- Müşteri hesabında siparişler ve bekleyen işlemler öne çıkarılacaktır.
- Satıcı panelinde sipariş, ürün, teklif ve mesaj işlemleri önceliklendirilecektir.
- Yönetim panelinde onay kuyrukları, operasyon ve finans görünür olacaktır.
- Yoğun tablolar mobilde okunabilir kartlara veya kontrollü yatay kaydırmaya dönüşecektir.
- Kritik işlemler açık metin ve gerekli onay diyaloğuyla sunulacaktır.

## 5. Teknik mimari

- Laravel Blade, Vite ve Tailwind korunacaktır. Yeni SPA framework'ü veya ağır UI kütüphanesi eklenmeyecektir.
- Renk, tipografi, boşluk, radius, gölge, hareket ve durum değerleri ortak tokenlara taşınacaktır.
- Buton, alan, select, textarea, kart, rozet, uyarı, modal/drawer, tablo, sayfalama, breadcrumb, sayfa başlığı, boş durum ve SEO head ortak Blade bileşenleri olacaktır.
- Dört layout aynı bileşen sistemini kullanacaktır; vitrin daha ferah, paneller daha yoğun yerleşime sahip olacaktır.
- Bootstrap panel ailesi bazında kaldırılacaktır. Modal, dropdown ve diğer davranışlar taşınmadan CDN bağlantıları silinmeyecektir.
- Tekrarlanan inline CSS ve JavaScript derlenen dosyalara taşınacaktır.
- Grafik ve sürükleme araçları yalnız ihtiyaç duyulan sayfalarda yüklenecektir.
- Mevcut route isimleri, form alanları, yetkilendirme, ödeme, stok, teklif, güven seviyesi ve adil ürün dağıtımı korunacaktır.
- Yalnız arayüz için veritabanı şeması değiştirilmeyecektir.

## 6. Performans planı

- Hero tek responsive AVIF/WebP görsel olacaktır; boyutları tanımlı ve doğru öncelikle yüklenecektir.
- Ekran altındaki görseller tembel yüklenecektir.
- Yeni ürün görselleri için yeniden kullanılabilir türetme işlemi hazırlanacak; mevcut görseller tekrarlanabilir bir komutla dönüştürülecektir.
- Görseli olmayan ürünlerde yerel ve markalı placeholder kullanılacaktır.
- N+1 ve tekrarlanan sorgular ölçülüp giderilecektir.
- Kullanıcıya özel sepet, ödeme, hesap ve panel yanıtları ortak önbelleğe alınmayacaktır.
- Service worker'ın eski tasarım dosyalarını veya özel sayfaları yanlış önbelleklemediği doğrulanacaktır.

### Bütçeler ve hedefler

- Mobil hero: en fazla 180 KB
- İlk yüklemede sıkıştırılmış birinci taraf JavaScript: en fazla 100 KB
- İlk yüklemede sıkıştırılmış CSS: en fazla 60 KB
- Gerçek kullanıcı verisinde 75. yüzdelik LCP: en fazla 2,5 saniye
- Gerçek kullanıcı verisinde 75. yüzdelik INP: en fazla 200 ms
- Gerçek kullanıcı verisinde 75. yüzdelik CLS: en fazla 0,1
- Sabit mobil Lighthouse profilinde üç ölçümün medyanı: Performance ≥90, Accessibility ≥95, SEO ≥95

## 7. Türkçe SEO planı

- Mevcut Türkçe URL'ler korunacaktır.
- İngilizce arayüz çalışmaya devam edecek; ayrı İngilizce URL ve `hreflang` bu fazda yapılmayacaktır.
- Ortak SEO head bileşeni başlık, açıklama, canonical, robots, Open Graph ve JSON-LD verisini yönetecektir.
- Ürün, kategori, satıcı ve blog sayfaları sunucudan anlamlı HTML olarak üretilecektir.
- `/sitemap.xml` yalnız herkese açık, canonical ve indekslenebilir URL'leri içerecektir.
- Kategori için `category` slug parametresi canonical biçim olacaktır; `category_id` bağlantıları uyumluluk için çalışmaya devam edecektir.
- Her sayfalama sayfası kendi canonical adresini kullanacaktır.
- Arama, sıralama, gereksiz filtre kombinasyonları, hesap, sepet, ödeme ve özel talep sayfaları `noindex` olacaktır.
- Admin ve satıcı panellerindeki `noindex,nofollow` korunacaktır.
- Product/Offer, BreadcrumbList, Organization ve BlogPosting verileri yalnız gerçek içerikten üretilecektir.
- Teklifli ürüne sahte fiyat, değerlendirmesiz ürüne sahte puan veya veritabanında olmayan işletme bilgisi eklenmeyecektir.
- Mevcut kalıcı yönlendirmeler korunacaktır.

## 8. Uygulama sırası

1. Route–Blade–layout–rol matrisi çıkarılacak; erişilebilen ekranların başlangıç görüntüleri ve ölçümleri kaydedilecek.
2. Tasarım tokenları, ortak bileşenler ve dört layout oluşturulacak.
3. Ana sayfa, ürün listesi, ürün detayı, sepet ve ödeme tamamlanacak.
4. Teklif ve hizmet talebi akışları tamamlanacak.
5. Müşteri hesabı, satıcı paneli ve yönetim paneli dönüştürülecek.
6. Blog, kurumsal, yasal ve kimlik doğrulama ekranları tamamlanacak.
7. Kullanılmayan eski stil ve scriptler güvenli şekilde kaldırılacak.
8. SEO, performans, erişilebilirlik ve responsive kontrolleri tamamlanacak.
9. Test ve ölçüm sonuçları teslim raporuna yazılacak.

## 9. Test ve kabul kriterleri

- Route envanterindeki her arayüz için `kontrol edildi`, `erişim engeli var` veya `kullanılmıyor` durumu ve kanıt bulunmalıdır.
- 360, 390, 768, 1024 ve 1440 px genişliklerde taşma ve sabit öğe çakışması olmamalıdır.
- Menü, drawer, modal, filtre ve formlar klavyeyle kullanılabilmelidir.
- Odak görünür olmalı; modal/drawer kapandığında doğru elemana dönmelidir.
- Boş liste, stok dışı ürün, görselsiz ürün, varyant seçimi ve ödeme hatası kontrol edilmelidir.
- Sepet ekleme, adet güncelleme, kaldırma, teklif oluşturma ve rol erişimleri doğrulanmalıdır.
- Canonical, robots, sitemap ve JSON-LD otomatik testlerle kontrol edilmelidir.
- Üretim Vite build'i ve mevcut PHP test paketi geçmelidir.
- Lighthouse ölçümü sabit profil ve koşullarda üç kez yapılarak medyan değer raporlanmalıdır.
- Önceden var olan sorunlar ile bu çalışmanın oluşturduğu sorunlar ayrı raporlanmalıdır.

## 10. Sınırlar ve varsayımlar

- Çalışma yerel projede ve izole test verisiyle yürütülecektir.
- Gerçek ödeme, e-posta, WhatsApp veya başka dış bildirim gönderilmeyecektir.
- Sahte yorum, güven rozeti, fiyat, sayaç veya teslimat vaadi üretilmeyecektir.
- Kullanıcının mevcut ve ilgisiz değişiklikleri silinmeyecek veya geri alınmayacaktır.
- Canlıya otomatik yayın yapılmayacaktır.
- Lighthouse ve Core Web Vitals değerleri ölçülebilir kabul hedefleridir; arama sıralaması veya her ortamda 100 puan garantisi değildir.
