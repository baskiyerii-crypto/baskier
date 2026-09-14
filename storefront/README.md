# BaskiYeri Storefront (Next.js)

Hibrit mimarinin vitrin katmani. Laravel motor olarak kalir: is kurallari
`app/Services`, auth (Sanctum), kuyruk ve mevcut sema degismez. Bu uygulama
sadece `/api/v1` uclarini tuketir; mobil (`mobile/`) ile ayni sozlesme.

## Kurulum

```bash
cd storefront
npm install
cp .env.example .env.local   # Windows: copy .env.example .env.local
npm run dev
```

| Degisken | Ne ise yarar |
| --- | --- |
| `LARAVEL_API_URL` | Laravel API kokU (`.../api/v1`) |
| `LARAVEL_WEB_URL` | Blade sayfalari ve `/storage` gorselleri |
| `NEXT_PUBLIC_SITE_URL` | canonical, hreflang, sitemap |

`npm run build` sirasinda ana sayfa ve sitemap on-render edildigi icin
**Laravel API erisilebilir olmali**. Headless mimarinin normal davranisi.

## Dil (TR / EN)

Karar sirasi `middleware.ts` icinde:

1. URL prefix (`/en/...`)
2. `?lang=tr|en` (acik secim; mobil WebView bunu kullanir)
3. `NEXT_LOCALE` cookie (kullanici secimi)
4. IP ulke basligi (`x-vercel-ip-country`, `cf-ipcountry`, `x-country-code`) - `TR` ise Turkce, digerleri Ingilizce
5. `Accept-Language`, sonra `tr`

Kurallar:

- Crawler'a IP ile yonlendirme yapilmaz; istenen URL neyse o sunulur (cloaking olmasin).
- Kok `/` icin IP'ye gore yonlendirme var.
- Locale'siz **ic** sayfalarda yonlendirme yok; paylasilan link bozulmasin diye
  ustte oneri bandi gosterilir (`LocaleSuggestion`).
- Kullanici `TR | EN` dugmesine bastiginda cookie yazilir, IP bir daha karismaz.

URL sozlugu tek kaynakta: `i18n/routing.ts` -> `pathnames`. Ic linkler
`i18n/navigation.ts` uzerinden pathname anahtariyla verilir, elle `/urunler`
yazilmaz. Turkce prefix'siz (`/urunler`), Ingilizce `/en/products`.

## Hangi sayfa nerede

Next serves: `/`, `/urunler`, `/urun/[slug]`, `/saticilar`, `/satici/[slug]` ve
bunlarin `/en/...` karsiliklari.

Laravel (Blade) serves: `/giris`, `/kayit`, `/sepet`, `/odeme`, `/hesap*`,
`/hesabim`, `/satici-panel`, `/admin`, `/teklif-talebi`, `/is-ilanlari`,
kurumsal sayfalar ve `/sozlesme/*`.

`i18n/routing.ts` icinde henuz Blade'de olan yollar da tanimli; sozluk ve
hreflang tek kaynaktan calissin diye. O sayfalara link verirken
`lib/urls.ts` -> `legacyUrl()` kullanilir.

Yonlendirme ornegi: [`deploy/nginx-storefront.conf.example`](../deploy/nginx-storefront.conf.example).
**Laravel route'lari silinmez** - Blade layout'lari `route('home')`,
`route('products.index')`, `route('products.show')` yardimcilarini kullaniyor;
disaridan hangi tarafin cevap verecegini proxy belirler.

## API sekilleri

Laravel uc farkli sekil donuyor, `lib/api/client.ts` ucunu de cozer:

- `ApiResponse` sarmalayicisi (`/products`): `{ success, message, data, meta, errors }`
- Duz JSON (`/home`, `/categories`)
- Duz Laravel paginator (`/vendors`): `{ current_page, data, last_page, ... }`

Tarayici Laravel'e dogrudan gitmez: `app/api/proxy/[...path]` BFF ucu Bearer
token'i httpOnly cookie'den okur. Bu sayede CORS ve `SANCTUM_STATEFUL_DOMAINS`
ayari gerekmiyor.

## Bilinen kisit

Eksik urun/satici icin `notFound()` cagriliyor ancak next-intl'in locale
rewrite'i nedeniyle HTTP status 200 kaliyor (rewrite olmadan 404 donuyor).
Soft 404 indekslenmesin diye bu sayfalar `robots: noindex, nofollow` ile
isaretlendi. Turkce URL'leri prefix'siz tutma karari degismedikce bu kisit
kalir.

## Bu fazda kapsam disi

Sepet/odeme arayuzu (Faz 1b), 3D onizleme ve anlik parametrik fiyatlama
(Faz 2), icerik (urun adi/aciklama) cevirisi, admin ve satici panelinin
tasinmasi (Faz 3).
