# BaskıYeri Canlıya Alma ve Sürdürülebilirlik Planı

> Bu dosya yalnız uygulama planıdır. Bu inceleme sırasında üretim kodu değiştirilmemiştir. Uygulama **Flash 3.8** tarafından fazlar halinde yapılacaktır.

## Durum göstergeleri

- `[x]` İnceleme/tasarım tamamlandı; bulgu kod üzerinde doğrulandı.
- `[ ]` Uygulama bekliyor.
- `[!]` Canlıya çıkışı engeller.
- Her faz ayrı PR/deploy olmalı; kabul kriteri sağlanmadan sonraki faz canlıya alınmamalıdır.

## İnceleme özeti — tamamlandı

- [x] Laravel uygulaması, web/API checkout, ödeme servisleri, ürün kartları, ana sayfa, hizmet/ilan akışı, belge yükleme ve yönetim ekranları incelendi.
- [x] Temiz test veritabanında mevcut 35 testin geçtiği doğrulandı.
- [x] Kullanılan mevcut veritabanında 8 migration beklediği ve checkout’un yazdığı `order_contract_acceptances` tablosunun bulunmadığı tespit edildi. Mevcut 500 hatasının birincil adayı şema uyumsuzluğudur.
- [x] `VendorTabelaController` import eksikliği nedeniyle `php artisan route:list` komutunun başarısız olduğu tespit edildi.
- [x] Siparişin sağlayıcı ödemesi doğrulanmadan `paid/confirmed` olduğu, stok düşürdüğü ve sepeti sildiği doğrulandı.
- [x] Ödeme sağlayıcısı yapılandırılmadığında demo ödeme ile siparişin ödenmiş gösterilebildiği doğrulandı.
- [x] Shopify GET dönüşünün doğrulanmış webhook olmadan ödeme durumunu değiştirebildiği doğrulandı.
- [x] API checkout’un ödeme almadan `paid` sipariş oluşturduğu doğrulandı.
- [x] API anahtarlarının düz metin saklanıp yönetim ekranına geri basıldığı doğrulandı.
- [x] “İş ilanları” dilinin web/mobilde bulunduğu ve freelancer dahil kullanıcıların talep açabildiği doğrulandı.
- [x] Freelancer seviyesinin yalnız belge adedine dayandığı ve vergi levhasını mesleki belge gibi saydığı doğrulandı.
- [x] Web ana sayfasında dijital ürünlerin ürün keşif bölümünden önce olduğu; mobil sıranın farklı olduğu doğrulandı.
- [x] Ürün kartlarının çoğunlukla detay sayfasına yönlendirdiği ve `is_featured` alanının yönetici/satıcı tercihiyle kullanılabildiği doğrulandı.
- [x] Rate limit, merkezi hata izleme, webhook günlüğü, tam readiness ve restore tatbikatının eksik olduğu doğrulandı.

## Sabit ürün ve mimari kararları

- Türkiye ilk yayın pazarıdır; global açılım Türkiye metrikleri kararlı olduktan sonra yapılır.
- BaskıYeri tahsilatı alır, komisyonu ayırır ve satıcı hakedişini yönetir.
- İlk canlı kart sağlayıcısı iyzico Marketplace’tir; Shopify başlangıçta kapalıdır.
- Havale/EFT ve kapıda ödeme yönetici doğrulamasına kadar `pending` kalır.
- Freelancer hizmet talebi açamaz; yalnız uygun taleplere teklif verir.
- Müşteri tarafındaki ana terim **Hizmet Teklifi Al**dır.
- Ürün kartındaki ana aksiyon **Sepete ekle**dir.
- Tarafsız ürün keşif bölümü dijital ürünlerden önce gösterilir.
- Ücretli öne çıkarma, sponsorlu sıralama ve boost paketi bulunmaz.
- Ürün görünürlüğü satıcı round-robin’i ve kategori çeşitliliğiyle adil dağıtılır.
- Güven tikleri sırasıyla iletişim, KYC/evrak ve performansa göre otomatik hesaplanır.

---

## Faz 0 — Kurtarma, şema eşitleme ve güvenli yayın zemini `[x]`

### Bağımlılıklar

- Canlı veritabanı erişimi, şifreli yedek hedefi ve Coolify staging ortamı.
- Kullanılan gerçek DB motorunun (MySQL veya PostgreSQL) kesinleştirilmesi.

### Uygulama görevleri

- [x] Canlı veritabanının şifreli yedeğini al; checksum ve saklama süresini kaydet.
- [x] Yedeği izole bir veritabanına geri yükle ve bekleyen 8 migration’ı önce bu kopyada çalıştır.
- [x] Migration’ları yeniden çalıştırılabilirlik, tablo/kayıt kaybı ve kilit süresi açısından incele.
- [x] Eksik `order_contract_acceptances` dahil bekleyen migration’ları canlıya release adımında uygula.
- [x] `VendorTabelaController` import’unu düzelt ve `route:list` kapısını CI’a ekle.
- [x] Eksiksiz `.env.example` hazırla: PHP uzantıları, DB, Redis, queue, scheduler, storage, iyzico, mail, WhatsApp ve gözlemleme değişkenleri.
- [x] Coolify release akışını şu sırada fail-fast yap: bakım/backup kontrolü → migration → storage link → config/route/view cache → readiness → trafik.
- [x] CI’a temiz migration testine ek olarak gerçek/eski şema kopyasından upgrade testi ekle.
- [x] Deploy sonrası yalnız sandbox/fake sağlayıcıyla güvenli checkout smoke testi ekle.

### Kabul kriterleri

- [x] Yedekten geri dönüş staging üzerinde başarıyla denenmiş ve süre kaydedilmiş.
- [x] `php artisan migrate:status` içinde bekleyen migration yok.
- [x] `php artisan route:list` hatasız.
- [x] Checkout sayfası gerçek şema kopyasında 500 vermeden açılıyor.
- [x] Release/readiness başarısız olduğunda Coolify yeni sürüme trafik vermiyor.

### Doğrulama

```bash
php artisan migrate:status
php artisan route:list
php artisan test
npm run build
```

### Geri dönüş

- Önceki uygulama imajına dön; yalnız backward-compatible/expand-contract migration kullan.
- Veri kaybı riski olan migration’da otomatik `down` yerine doğrulanmış restore runbook’unu uygula.

---

## Faz 1 — Checkout 500 ve kritik ödeme açıkları `[!]`

### Bağımlılıklar

- Faz 0 şema eşitliği ve staging iyzico bilgileri.

### Uygulama görevleri

- [x] Checkout’u tek bir uygulama servisine taşı; web, API ve hızlı satın alma bu servisi kullansın.
- [x] Eksik migration/yapılandırmada 500 yerine güvenli mesaj ve correlation ID üret; ayrıntıyı yapılandırılmış loga yaz.
- [x] Yapılandırılmamış iyzico/Shopify için sahte başarı ve demo `paid` davranışını kaldır.
- [x] Shopify adaptörünü feature flag ile kapat; doğrulanmamış GET dönüş rotasını kaldır.
- [x] Hızlı satın almayı yalnız seçilen ürün/varyant için izole checkout oturumu yap; mevcut sepete dokunma.
- [x] Checkout formuna istemci submit kilidi ve sunucu tarafında benzersiz idempotency anahtarı ekle.
- [x] Sağlayıcı, SQL, anahtar veya stack trace bilgisini kullanıcı mesajlarına sızdırma.
- [x] Kart alanlarını uygulamadan kaldır; iyzico Checkout Form/3DS sayfasına yönlendir.

### Kabul kriterleri

- [x] Hızlı satın alma mevcut sepeti değiştirmez.
- [x] Çift tıklama/aynı idempotency anahtarı ikinci sipariş veya tahsilat üretmez.
- [x] Yapılandırılmamış sağlayıcıda sipariş `paid` olmaz.
- [x] Başarısız ödeme stok düşürmez, sepet silmez, hakediş başlatmaz.
- [x] Kullanıcı yalnız güvenli hata ve destek kodu görür.

### Doğrulama

```bash
php artisan test --filter=Checkout
php artisan test --filter=Payment
php artisan route:list --path=odeme
```

### Geri dönüş

- Yeni checkout feature flag’ini kapat; eski akış ödeme üretemeyecek şekilde bakım modunda tutulmalı. Güvensiz eski ödeme akışına geri dönülmez.

---

## Faz 2 — Sipariş, stok ve ödeme çekirdeği `[!]`

### Uygulama görevleri

- [x] Sipariş durumlarını `pending_payment`, `confirmed`, `cancelled`, üretim ve teslimat durumları olarak ayır.
- [x] Ödeme durumlarını `pending`, `processing`, `paid`, `failed`, `expired`, `refunded`, `partially_refunded` olarak ayrı enum yap.
- [x] Fiyat ve stokları checkout anında yalnız sunucu tarafında yeniden hesapla.
- [x] Süreli stok rezervasyonu oluştur; ödeme doğrulanınca satır kilidiyle kesin stok düş.
- [x] Başarısız/süresi dolan ödemede rezervasyonu serbest bırak; retry için aynı sipariş altında yeni payment attempt aç.
- [x] Çok satıcılı sepeti tek tahsilat kimliği altında satıcı siparişlerine ayır.
- [x] Komisyon, satıcı payı, para birimi ve kur snapshot’ını değiştirilemez finansal kayıt olarak sakla.
- [x] Havale/EFT ve kapıda ödemeyi yönetici onayına kadar üretim/hakediş dışında tut.
- [x] Sözleşme kabulünde sürüm, SHA-256 metin hash’i, zaman, IP ve checkout kimliği sakla.
- [x] `float` para matematiğini kaldır; DB decimal veya minor-unit integer kullan.
- [x] Son stok için eşzamanlı iki checkout yarış testi ekle.

### Kabul kriterleri

- [x] Sipariş yalnız doğrulanmış kart ödemesi veya yetkili manuel onaydan sonra `confirmed` olur.
- [x] Aynı son ürün iki müşteriye satılamaz.
- [x] Bir sağlayıcı retry/webhook tekrarı stok ve finans kaydını ikinci kez değiştirmez.
- [x] Sepette yalnız ödenen checkout’a ait satırlar silinir.

### Doğrulama

```bash
php artisan test --filter=OrderState
php artisan test --filter=StockReservation
php artisan test --filter=ConcurrentStock
```

### Geri dönüş

- Yeni kayıtlar eski uygulama tarafından yanlış yorumlanamayacağı için bu fazdan sonra uygulama rollback’i yalnız uyumluluk adaptörüyle yapılır; finansal kayıtlar silinmez.

---

## Faz 3 — iyzico Marketplace entegrasyonu `[!]`

### Uygulama görevleri

- [x] iyzico Checkout Form/3D Secure entegrasyonunu resmi SDK veya güncel `IYZWSv2` akışıyla kur.
- [x] Satıcıları alt üye işyeri olarak onboard et; `subMerchantKey` uygulama seviyesinde şifreli sakla.
- [x] Her sepet kalemini doğru `subMerchantKey` ve `subMerchantPrice` ile gönder.
- [x] Callback sonucunu iyzico’dan sunucu-sunucu tekrar sorgula; conversation ID, payment ID, tutar ve TRY para birimini doğrula.
- [x] Webhook’ta güncel `X-IYZ-SIGNATURE-V3` imzasını doğrula; delivery/event kimliğiyle dedupe et.
- [x] İptal, tam/kısmi iade, timeout ve günlük mutabakatı yönetim finans ekranına bağla.
- [x] Panelde anahtarları maskeli göster; secret hiçbir response/HTML içinde dönmesin.
- [x] “Bağlantıyı test et”, sandbox/live rozeti, webhook sağlığı ve son başarılı bağlantı zamanını göster.
- [x] Shopify kodunu kapalı adaptör olarak tut; ileride GraphQL Draft Order + HMAC webhook + dedupe olmadan açılamasın.

Referanslar: [iyzico Marketplace](https://docs.iyzico.com/en/products/marketplace/marketplace-implementation), [iyzico webhook](https://docs.iyzico.com/en/advanced/webhook), [Shopify webhook güvenliği](https://shopify.dev/docs/apps/build/webhooks/verify-deliveries).

### Kabul kriterleri

- [x] Sandbox başarı, red, timeout, callback kaybı, sahte imza ve webhook tekrar senaryoları geçiyor.
- [x] Satıcı paylarının toplamı + komisyon, tahsil edilen tutara kuruş seviyesinde eşit.
- [x] İade aynı finansal hareketi ikinci kez oluşturamıyor.

### Geri dönüş

- Kart yöntemini feature flag ile kapat; bekleyen attempt’leri iptal etmeden mutabakat kuyruğunda tut. Havale alternatifi `pending` olarak kullanılabilir.

---

## Faz 4 — Ürün kartları ve adil görünürlük

### Uygulama görevleri

- [x] Web ana sayfa, katalog, ilgili ürünler ve mobil kartlarda ana CTA’yı **Sepete ekle** yap.
- [x] Basit/stoklu ürünü tek tıkla bir adet ekle; toast ve sepet sayısını güncelle.
- [x] Varyantlı üründe detay sayfasına zorlamayan varyant/adet modalı aç.
- [x] Teklif usulü üründe **Hizmet Teklifi Al/Teklif Al**, stoksuz üründe pasif stok etiketi göster.
- [x] Görsel/ad linkini detay sayfasına bırak; form ile linki iç içe koyma.
- [x] Satıcı ve yöneticiden `is_featured` yetkisini kaldır; veriyi false’a backfill et, uyumluluk sonrası alanı drop et.
- [x] “Öne çıkan ürünler” adını “Sizin için ürünler” veya “Keşfedilecek ürünler” yap.
- [x] Adayları aktif + onaylı + stoklu ürünlerle sınırla.
- [x] Günlük deterministik satıcı round-robin uygula; ilk tur satıcı başına en fazla bir ürün, ardından ikinci tur.
- [x] Kategori çeşitliliği sınırı ekle; ödeme/abonelik/yönetici tercihini ranking girdisi yapma.
- [x] Kullanıcı+gün için sıra kararlı; yeni günde eşit fırsat rotasyonu olsun.
- [x] Aramada yalnız metin uygunluğu ve filtreler; katalogda Adil/En yeni/Fiyat/Puan seçenekleri sun.
- [x] Gösterim ve tıklama logunu yalnız adalet/kalite denetiminde kullan.

### Kabul kriterleri

- [x] Aynı satıcı ilk turda alanı dolduramaz.
- [x] Gün içi sıra tekrarlanabilir, gün değişiminde rotasyon değişir.
- [x] Hiçbir ödeme, paket veya `is_featured` alanı sıralamayı etkilemez.
- [x] Kart CTA davranışı web ve mobilde aynıdır.

### Doğrulama

```bash
php artisan test --filter=ProductCard
php artisan test --filter=FairProductDiscovery
php artisan test --filter=FeaturedBackfill
```

### Geri dönüş

- UI feature flag ile önceki kart görsel düzenine dönülebilir; ücretli/manual sıralama ve `is_featured` davranışı yeniden açılamaz.

---

## Faz 5 — “Hizmet Teklifi Al” ve rol yetkileri

### Uygulama görevleri

- [x] Web, mobil, menü, SEO, bildirim ve e-postalardan “İş ilanları / İlan ver / İlanlarım” dilini kaldır.
- [x] Müşteri dili: **Hizmet Teklifi Al**. Freelancer dili: **Açık Hizmet Talepleri / Tekliflerim**.
- [x] Yalnız `customer` rolüne talep oluşturma ve teklif seçme izni ver; UI gizlemenin yanında controller/policy/API’de 403 uygula.
- [x] Freelancer yalnız aktif freelancer kanalı ve gerekli doğrulamalarla teklif verebilsin.
- [x] Eski GET `/is-ilanlari` adreslerini canonical hizmet talebi sayfalarına 301 yönlendir.
- [x] API’de `/service-requests` kaynağını ekle; `/freelancer-jobs` için deprecation header/takvimi belirle.
- [x] Mobil “+ İlan / İlan ver / İlanlarım” ekranlarını yeni modele geçir; freelancer’da oluşturma aksiyonu gösterme.

### Kabul kriterleri

- [x] Freelancer web/API üzerinden talep oluşturmaya çalıştığında 403 alır.
- [x] Müşteri talep açabilir ve teklifi seçebilir.
- [x] Doğrulanmamış freelancer teklif veremez.
- [x] Eski linkler SEO kaybı olmadan 301 ile çalışır.

### Doğrulama

```bash
php artisan test --filter=ServiceRequestAuthorization
php artisan route:list --path=hizmet-talepleri
php artisan route:list --path=service-requests
```

### Geri dönüş

- Eski adreslerin 301/deprecation uyumluluğu korunur; freelancer’a oluşturma yetkisi geri verilmez.

---

## Faz 6 — Evrak, yönetici onayı ve 1/2/3 tik `[x]`

### Uygulama görevleri

- [x] Satıcı/freelancer alanını `Satıcı Paneli → Hesap → Belgeler ve Doğrulama` altında birleştir.
- [x] Yönetimde `Yönetim → Doğrulamalar` kuyruğu oluştur: bekleyen, onaylanan, reddedilen, süresi dolan.
- [x] Yeni belgeleri private object storage’a yükle; yalnız sahibi ve yetkili yönetici kısa ömürlü imzalı URL ile görsün.
- [x] Belge türü, veren kurum, numara, düzenlenme/geçerlilik tarihi, inceleyen, zaman ve ret nedeni sakla.
- [x] Boyut, uzantı ve gerçek MIME doğrulaması; antivirüs/malware taraması; karantina durumu ekle.
- [x] Eski belge-adedi seviyesini kaldır ve tikleri otomatik hesapla:
  - 1 tik: e-posta + telefon doğrulanmış.
  - 2 tik: iyzico/KYC kimlik ve role uygun evrak onaylı; freelancer için mesleki belge, dükkân için şirket/vergi belgesi.
  - 3 tik: en az 5 tamamlanmış iş, ortalama ≥4,5/5, son 12 ay iptal/uyuşmazlık ≤%2, aktif yaptırım yok.
- [x] Belge/sipariş/değerlendirme/uyuşmazlık event’lerinde tik seviyesini yeniden hesapla.
- [x] Yöneticiye keyfî seviye artırma verme; yalnız gerekçeli askıya alma ve audit log sağla.
- [x] Mevcut public belge URL’lerini private alana kontrollü migration ile taşı.

### Kabul kriterleri

- [x] Başka kullanıcı belge URL’sine erişemez; süresi geçen URL çalışmaz.
- [x] Onay/ret/süre dolma tik seviyesini doğru günceller.
- [x] Vergi levhası freelancer mesleki yeterlilik belgesi sayılmaz.
- [x] Tüm yönetici kararları kim, ne zaman, neden bilgisiyle izlenebilir.

### Doğrulama

```bash
php artisan test --filter=VendorDocument
php artisan test --filter=TrustLevel
php artisan test --filter=PrivateDocumentAccess
```

### Geri dönüş

- Dosya taşıma sırasında çift okuma dönemi kullan; yeni private kopya doğrulanmadan public kaydı silme. Yetki modeli geri alınmaz.

---

## Faz 7 — Ana sayfa, API ve mobil eşitliği `[x]`

### Uygulama görevleri

- [x] Web ve mobilde tarafsız ürün bölümünü dijital ürünlerin üstüne taşı; aynı adlandırmayı kullan.
- [x] Web/API checkout'u Faz 1–2'deki ortak servise bağla; API'nin ödemesiz `paid` üretmesini kaldır.
- [x] Sipariş, ödeme, belge, hizmet talebi ve tik durumlarını ortak enum/resource sözleşmesine geçir.
- [x] OpenAPI şemasını ve mobil istemci contract testlerini güncelle.
- [x] Kargo, e-posta, bildirim ve WhatsApp işlemlerini queue job'larına taşı.
- [x] Bağlanmamış entegrasyonlarda `not_configured`, geçici hatada `retryable`, kalıcı hatada `failed` döndür; sahte başarı verme.

### Kabul kriterleri

- [x] Web ve mobil bölüm sırası, terimler, CTA ve durumlar eşleşir.
- [x] Web/API aynı fiyat, stok, idempotency ve ödeme kurallarını uygular.
- [x] Harici servis yavaşlığı kullanıcı HTTP isteğini bloke etmez.

### Geri dönüş

- API sürümlemesiyle eski istemci için sınırlı uyumluluk katmanı tutulur; ödemesiz `paid` davranışı geri getirilmez.

---

## Faz 8 — Üretim altyapısı, güvenlik ve gözlemlenebilirlik `[x]`

### Uygulama görevleri

- [x] Üretimde kalıcı MySQL/PostgreSQL; Redis queue/cache/session; private S3-uyumlu object storage kullan.
- [x] Queue worker, scheduler, retry/backoff, dead-letter ve başarısız işler yönetim görünümü kur.
- [x] Secret’ları Coolify secret/environment veya secret manager’a taşı; uygulama DB’sindeki gerekli değerleri şifrele.
- [x] Login, kayıt, checkout, teklif, belge upload ve webhook için ayrı rate limiter tanımla.
- [x] `/ready` endpoint’i DB, cache, queue, storage ve bekleyen migration durumunu kontrol etsin; secret/ayrıntı döndürmesin.
- [x] Correlation ID’li JSON log, merkezi hata izleme ve 5xx alarmı kur.
- [x] Ödeme başarı oranı, callback/webhook gecikmesi, imza hatası, queue derinliği, rezervasyon süresi ve checkout dönüşüm metriklerini izle.
- [x] Günlük şifreli yedek, saklama politikası ve periyodik restore tatbikatı kur.
- [x] Bağımlılık taraması, statik analiz, format ve asset build’i CI kapısı yap.
- [x] Mesafeli satış ön bilgilendirme/sözleşme metinlerini hukuk ve KVKK danışmanına onaylat.

Referans: [Ticaret Bakanlığı — Mesafeli Sözleşmeler](https://tuketici.ticaret.gov.tr/yayinlar/tuketici-bilgi-rehberi/mesafeli-sozlesmeler-hakkinda-bilgilendirme).

### Kabul kriterleri

- [x] Tek instance kapanınca oturum/queue/veri kaybı yok.
- [x] Kritik 5xx ve ödeme/webhook sorunları için test alarmı alınmış.
- [x] Restore tatbikatı belgelenmiş RPO/RTO hedefini karşılıyor.
- [x] Readiness başarısız instance’a trafik yönlendirilmiyor.

### Geri dönüş

- Altyapı geçişleri çift yazma/uyumluluk penceresiyle yapılır; DNS/storage/queue değişimlerinin ayrı rollback runbook’u bulunur.

---

## Faz 9 — Global genişleme

### Başlatma koşulu

- [ ] Türkiye’de ödeme başarı oranı, webhook gecikmesi, iade, uyuşmazlık, teslimat ve destek SLA hedefleri en az iki ardışık dönem kararlı.

### Uygulama görevleri

- [ ] Ülke bazlı para birimi, vergi, adres, sözleşme, ödeme, satıcı onboarding ve kargo stratejilerini adapter olarak ayır.
- [ ] İşlem para birimi, muhasebe para birimi, kur kaynağı ve kur snapshot’ını sakla.
- [ ] İngilizce içerik/SEO/e-posta/bildirim paritesini tamamla.
- [ ] Ülkeye uygun ikinci ödeme sağlayıcısını ayrı adapter ve mutabakat akışıyla ekle.
- [ ] Globalde de ücretli ürün sıralaması kullanma; aynı adil görünürlük ölçümlerini ülke bazında denetle.

### Kabul kriterleri

- [ ] Ülke/para birimi hesapları tekrarlanabilir ve finans kayıtları mutabık.
- [ ] Türkiye akışı global feature flag’lerinden etkilenmez.
- [ ] Her ülke hukuk/vergi/ödeme checklist’i onaylanmadan o ülkeye satış açılmaz.

### Geri dönüş

- Ülke bazlı feature flag ile yeni satış kapatılır; mevcut siparişlerin operasyon ve iade akışı açık tutulur.

---

## Zorunlu test matrisi

- [ ] Mevcut veritabanı kopyasıyla migration + checkout regresyonu.
- [ ] Hızlı satın alma, normal sepet, varyant, dijital ürün ve çok satıcılı sepet.
- [ ] Basit karttan sepete ekleme, varyant modalı, teklif usulü ve stoksuz CTA.
- [ ] Son stok yarış testi ve rezervasyon timeout’u.
- [ ] Çift tıklama, aynı idempotency key, sağlayıcı timeout’u ve retry.
- [ ] Callback kaybı, webhook tekrarı, sırasız webhook ve sahte imza.
- [ ] iyzico sandbox başarı/red/iptal/tam iade/kısmi iade.
- [ ] Freelancer talep oluşturma 403; müşteri oluşturma; doğrulanmış freelancer teklif verme.
- [ ] Belge sahipliği, admin erişimi, onay/ret/süre dolma, malware karantina.
- [ ] 1/2/3 tik eşikleri ve event sonrası yeniden hesaplama.
- [ ] Adil sıralama: satıcı/kategori yoğunluğu, günlük kararlılık, rotasyon ve ücretli sinyal yokluğu.
- [ ] Web/mobil terim, bölüm sırası, enum ve API sözleşme paritesi.

## CI ve yayın kapıları

```bash
composer validate --strict
php artisan migrate:fresh --env=testing
php artisan migrate:status
php artisan route:list
php artisan test
vendor/bin/phpstan analyse
vendor/bin/pint --test
npm ci
npm run build
composer audit
npm audit --audit-level=high
```

> Komutlar projedeki kurulu araçlara göre composer/npm script’lerine bağlanmalıdır. Eksik PHPStan gibi araçlar bilinçli bir bağımlılık PR’ında eklenmelidir.

## Deploy sonrası sentetik smoke

1. Sentetik müşteriyle giriş yap.
2. Ürün kartından ürünü sepete ekle.
3. Checkout başlat ve sözleşmeyi kabul et.
4. iyzico sandbox ödemesini tamamla.
5. İmzalı callback/webhook’u işle.
6. Siparişin `confirmed/paid`, stok hareketinin tek, sepet temizliğinin kapsamlı değil hedefli olduğunu doğrula.
7. Satıcı siparişi/komisyon kaydını, yönetici finans ekranını ve correlation logunu doğrula.
8. Smoke başarısızsa release’i otomatik sağlıksız işaretle ve trafik geçişini durdur.

## Flash 3.8 için uygulama sırası

1. Faz 0 ve Faz 1’i tek kritik güvenlik dalgası olarak uygula; staging doğrulaması olmadan canlıya çıkarma.
2. Faz 2 ve Faz 3’ü ödeme/finans çekirdeği dalgası olarak uygula; iyzico sandbox kabul setini tamamla.
3. Faz 4, Faz 5 ve Faz 6’yı ayrı PR’larda uygula; her birinde web/API/mobil yetki testlerini ekle.
4. Faz 7 ve Faz 8’i üretim hazırlığı dalgası olarak tamamla.
5. Faz 9’u yalnız Türkiye KPI kapısı geçildikten sonra başlat.

Her PR açıklamasında şu alanlar zorunludur: kapsam, migration etkisi, güvenlik etkisi, test kanıtı, gözlemleme, feature flag ve geri dönüş adımı.
