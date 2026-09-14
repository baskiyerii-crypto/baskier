import { defineRouting } from 'next-intl/routing';

export const locales = ['tr', 'en'] as const;
export type Locale = (typeof locales)[number];

export const defaultLocale: Locale = 'tr';

/** Kullanici secimi burada saklanir; IP onerisi bu cookie varken devreye girmez. */
export const LOCALE_COOKIE = 'NEXT_LOCALE';

/** Middleware, IP onerisini sayfaya bu cookie ile bildirir. */
export const LOCALE_SUGGESTION_COOKIE = 'by_locale_suggestion';

/**
 * Tum public URL sozlugu. Ic linkler i18n/navigation.ts uzerinden bu anahtarlarla
 * verilir, hicbir yerde elle '/urunler' yazilmaz.
 *
 * Isaretli satirlar henuz Laravel Blade tarafindan sunuluyor; sozluk simdiden
 * burada tutuluyor ki dil secici, hreflang ve sonraki fazlar tek kaynaktan calissin.
 */
export const pathnames = {
    '/': '/',
    '/urunler': { tr: '/urunler', en: '/products' },
    '/urun/[slug]': { tr: '/urun/[slug]', en: '/product/[slug]' },
    '/saticilar': { tr: '/saticilar', en: '/vendors' },
    '/satici/[slug]': { tr: '/satici/[slug]', en: '/vendor/[slug]' },
    // Henuz Blade'de:
    '/teklif-talebi': { tr: '/teklif-talebi', en: '/quote-request' },
    '/is-ilanlari': { tr: '/is-ilanlari', en: '/jobs' },
    '/giris': { tr: '/giris', en: '/login' },
    '/kayit': { tr: '/kayit', en: '/register' },
    '/sepet': { tr: '/sepet', en: '/cart' },
    '/odeme': { tr: '/odeme', en: '/checkout' },
    '/favorilerim': { tr: '/favorilerim', en: '/favorites' },
    '/hakkimizda': { tr: '/hakkimizda', en: '/about' },
    '/gizlilik': { tr: '/gizlilik', en: '/privacy' },
    '/kullanim-kosullari': { tr: '/kullanim-kosullari', en: '/terms' },
} as const;

export const routing = defineRouting({
    locales,
    defaultLocale,
    // Turkce bugunku gibi prefix'siz kalir, Ingilizce /en/... olur.
    localePrefix: 'as-needed',
    // Dil kararini middleware.ts veriyor (prefix > cookie > IP > tr).
    localeDetection: true,
    pathnames,
});
