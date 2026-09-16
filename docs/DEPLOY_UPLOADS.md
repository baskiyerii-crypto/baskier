# Deploy: kalıcı `public/uploads` volume

Platform logosu ve benzeri marka dosyaları `public/uploads/branding/` altına yazılır (`PlatformBranding`).

Coolify / Docker / PaaS redeploy sırasında `public/` imajdan geldiği için **volume bağlanmazsa logo silinir**.

## Zorunlu volume

| Host / volume | Container path |
|---------------|----------------|
| Persistent volume (ör. `baskiyeri-uploads`) | `/app/public/uploads` |

Laravel root genelde `/app` veya `/var/www/html` olur; path’i imaja göre ayarlayın.

## Redeploy sonrası

1. Volume bağlı mı kontrol edin.
2. Admin → Ayarlar → platform logosunu bir kez yeniden yükleyin (volume boşsa).
3. Hard refresh ile vitrini doğrulayın.

`storage/app/public` symlink’i logo için yeterli değildir; branding bilerek `public/uploads` kullanır.
