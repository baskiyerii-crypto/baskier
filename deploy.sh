#!/usr/bin/env bash
set -e

echo "=== BaskıYeri Release Akışı Başlatılıyor ==="

# 1. Bakım moduna al (isteğe bağlı secret ile bypass edilebilir)
php artisan down --render="errors::503" --secret="${DEPLOY_SECRET:-bypass-deploy}" || true

# 2. Sembolik bağlantı
php artisan storage:link --force

# 3. Veritabanı Migration (fail-fast: hata verirse release durur)
echo "--- Veritabanı Migration Çalıştırılıyor ---"
php artisan migrate --force

# 4. Önbellek optimizasyonu
echo "--- Önbellekler Temizlenip Hazırlanıyor ---"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Bakım modundan çıkış
php artisan up

# 6. Kuyruk işçilerini yeniden başlat
php artisan queue:restart || true

echo "=== BaskıYeri Release Akışı Başarıyla Tamamlandı ==="
