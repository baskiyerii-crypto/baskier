# BaskıYeri Platformu Afet Kurtarma, Yedekleme ve Restore Runbook'u

Bu doküman, üretim ortamında (production) meydana gelebilecek donanım arızası, veri merkezi kesintisi, veri bozulması veya insan hatası durumlarında uygulanacak yedekleme ve felaket kurtarma (Disaster Recovery) prosedürlerini tanımlar.

---

## 1. Hedef Kurtarma Metrikleri (SLA)

| Metrik | Tanım | Hedeflenen Süre |
|---|---|---|
| **RPO (Recovery Point Objective)** | Olası veri kaybı penceresi | **En fazla 24 Saat** (Her gece 03:00 otomatik yedekleme) |
| **RTO (Recovery Time Objective)** | Sistemin yeniden ayağa kaldırılma süresi | **En fazla 30 Dakika** |

---

## 2. Otomatik Yedekleme Mimarisi

- **Zamanlama:** Her gün saat 03:00'te Laravel Scheduler (`bootstrap/app.php`) tarafından tetiklenir:
  ```bash
  php artisan platform:backup --retention=30
  ```
- **Güvenlik ve Şifreleme:**
  - Veritabanı içeriği gzip ile sıkıştırılır.
  - Uygulama anahtarı (`APP_KEY`) kullanılarak **AES-256-CBC** ile şifrelenir.
  - Şifrelenmiş arşiv için **SHA-256** bütünlük sağlama toplamı (`.sha256`) dosyası üretilir.
- **Depolama Konumu:**
  - `storage/app/backups/backup-{Y-m-d-His}.enc`
  - `storage/app/backups/backup-{Y-m-d-His}.enc.sha256`
- **Saklama Politikası (Retention):** 30 günden eski arşiv dosyaları otomatik temizlenir.

---

## 3. Sentetik Restore Tatbikatı (Non-Destructive Rehearsal)

Canlı veritabanını ezmeden veya hizmeti durdurmadan yedeklerin bütünlüğünü düzenli olarak test etmek için sentetik tatbikat komutu kullanılır:

```bash
php artisan platform:restore-verify
```

Bu komut:
1. En son alınan yedek arşivini bulur.
2. SHA-256 sağlama toplamı eşleşmesini doğrular.
3. AES-256 şifresini çözer ve gzip açılımını test eder.
4. Temel tabloların (`users`, `orders`, `products`, `vendors`) arşivde bulunduğunu sentetik olarak denetler.
5. RPO (yedek yaşı) ve RTO (kurtarma süresi ms) değerlerini ölçerek yönetici paneli metrik ekranına (`/admin/metrikler`) yansıtır.

---

## 4. Acil Durumda Tam Veritabanı Geri Yükleme Adımları

Tam sistem çöküşünde yeni bir sunucu veya Docker konteyneri üzerinde verileri kurtarma adımları:

### Adım 1: Uygulamayı Bakım Moduna Alın
```bash
php artisan down --secret="acil-kurtarma-anahtari"
```

### Adım 2: Yedek Dosyasını Belirleyin
`storage/app/backups/` dizininden kurtarılacak en güncel `.enc` dosyasını seçin:
```bash
ls -la storage/app/backups/
```

### Adım 3: Arşiv Bütünlüğünü Doğrulayın
```bash
php artisan platform:restore-verify --file="storage/app/backups/backup-YYYY-MM-DD-HHMMSS.enc"
```

### Adım 4: Veritabanını Geri Yükleyin
- **SQLite için:**
  ```bash
  # Mevcut DB'yi yedekle
  cp database/database.sqlite database/database.sqlite.bak
  # Deşifre edilen içeriği yerine koy
  php -r "file_put_contents('database/database.sqlite', gzdecode(Illuminate\Support\Facades\Crypt::decrypt(file_get_contents('storage/app/backups/backup-YYYY-MM-DD-HHMMSS.enc'))));"
  ```
- **MySQL / PostgreSQL için:**
  Deşifre edilen SQL çıktısını veritabanı yöneticisine aktarın:
  ```bash
  mysql -u baskiyeri_user -p baskiyeri < dump.sql
  ```

### Adım 5: Migration ve Hazırlık Kontrolü
```bash
php artisan migrate --status
php artisan route:list
curl -I http://localhost/ready
```

### Adım 6: Uygulamayı Yeniden Canlıya Alın
```bash
php artisan up
```

---

## 5. İletişim ve Eskalasyon Listesi
- **Teknik Lider / DevOps:** bilgi@baskiyeri.com
- **Hosting / Altyapı:** Hetzner / Coolify Konsolu
- **Ödeme Sağlayıcı İletişimi:** iyzico Entegrasyon Destek (0850 399 99 99)