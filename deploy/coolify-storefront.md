# Coolify: BaskiYeri Storefront

Laravel uygulamasi ayni kaliyor. Next.js vitrin **ayri bir uygulama** olarak
eklenir. Push tek basina vitrini gostermez; bu app olmadan site Blade'de kalir.

## On kosul

1. Laravel Coolify app'i ayakta ve `https://SENIN-DOMAIN.com/api/v1/home` 200 donmeli.
2. GitHub `main` guncel (storefront/ klasoru repoda olmali).

## 1) Yeni uygulama olustur

Coolify → Project → **+ New** → Application → ayni GitHub repo.

| Alan | Deger |
| --- | --- |
| Base Directory | `/storefront` |
| Build Pack | **Dockerfile** |
| Dockerfile Location | `/Dockerfile` (base directory'ye gore) |
| Port | `3000` |
| Healthcheck Path | `/` |

## 2) Ortam degiskenleri

Environment Variables'a ekle. **Available at Buildtime** isaretini ac (ucunde de).

Canli domain ornekleri (kendi domaininle degistir):

```env
LARAVEL_API_URL=https://SENIN-DOMAIN.com/api/v1
LARAVEL_WEB_URL=https://SENIN-DOMAIN.com
NEXT_PUBLIC_SITE_URL=https://vitrin.SENIN-DOMAIN.com
```

| Degisken | Build | Runtime | Not |
| --- | --- | --- | --- |
| `LARAVEL_API_URL` | evet | evet | Public HTTPS; Coolify build container Laravel'e ulasabilmeli |
| `LARAVEL_WEB_URL` | evet | evet | `/storage` gorselleri ve Blade linkleri |
| `NEXT_PUBLIC_SITE_URL` | evet | evet | canonical / hreflang / sitemap; vitrinin kendi URL'i |

## 3) Domain (guvenli ilk yayin)

Ilk denemede **alt alan adi** kullan (canli siteyi bozmaz):

- Ornek: `vitrin.SENIN-DOMAIN.com` → bu storefront app'e bagla
- DNS: A/CNAME kaydi Coolify sunucusuna

Ayni domainde path bolusumu (ileri seviye) icin:
[`nginx-storefront.conf.example`](./nginx-storefront.conf.example)
veya Coolify Traefik path kurallari gerekir. Once alt domain ile dogrula.

## 4) Deploy

Deploy / Redeploy. Build log'unda `npm run build` basarili olmali.

Kontrol listesi:

- [ ] `https://vitrin.SENIN-DOMAIN.com/` aciliyor
- [ ] `https://vitrin.SENIN-DOMAIN.com/urunler` urun listesi
- [ ] `https://vitrin.SENIN-DOMAIN.com/en` Ingilizce
- [ ] Gorseller Laravel `/storage` uzerinden geliyor
- [ ] Dil secici (TR \| EN) cookie yaziyor

## 5) Laravel app'e dokunma

Mevcut Laravel Coolify app'inin Base Directory'i `/` kalsin.
`/api/*`, `/giris`, `/admin`, `/satici-panel` Laravel'de kalmaya devam eder.

## Sik hatalar

| Belirti | Neden | Cozum |
| --- | --- | --- |
| Build fail, `ECONNREFUSED` / fetch failed | Build aninda API yok | `LARAVEL_API_URL` public domain olsun; Laravel once ayakta |
| Site hala eski Blade | Storefront app yok veya domain Laravel'e bakiyor | Domain'i storefront app'e bagla |
| Gorsel kirik | `LARAVEL_WEB_URL` yanlis | Laravel'in gercek web kokunu yaz |
| Canonical localhost | `NEXT_PUBLIC_SITE_URL` eksik / buildtime yok | Degiskeni buildtime + runtime ver, redeploy |

## Iliski ozeti

```text
Tarayici
  ├─ vitrin.domain.com  → Coolify Storefront (Next :3000)
  │                         └─ server-side fetch → domain.com/api/v1
  └─ domain.com         → Coolify Laravel (Blade + API + paneller)
```
