/** Laravel API kokU. Mobil ile ayni sozlesme: /api/v1 */
export const API_URL = (process.env.LARAVEL_API_URL ?? 'http://127.0.0.1:8000/api/v1').replace(/\/$/, '');

/** Blade sayfalari ve /storage gorselleri icin Laravel web kokU. */
export const WEB_URL = (
    process.env.LARAVEL_WEB_URL ?? API_URL.replace(/\/api\/v1$/i, '')
).replace(/\/$/, '');

/** Canonical ve sitemap icin storefront'un public adresi. */
export const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000').replace(/\/$/, '');

/** Bearer token'i tutan httpOnly cookie; mobil AsyncStorage'daki token ile ayni ise yarar. */
export const TOKEN_COOKIE = 'by_token';

/** Public listelerin ISR suresi (saniye). */
export const LIST_REVALIDATE = 300;

/** Urun/satici detaylarinin ISR suresi (saniye). */
export const DETAIL_REVALIDATE = 600;
