import createMiddleware from 'next-intl/middleware';
import type { NextRequest } from 'next/server';

import {
    LOCALE_COOKIE,
    LOCALE_SUGGESTION_COOKIE,
    defaultLocale,
    locales,
    routing,
    type Locale,
} from '@/i18n/routing';
import { isCrawler } from '@/lib/geo/crawler';
import { detectLocale } from '@/lib/geo/detectLocale';

const handleI18n = createMiddleware(routing);

const ONE_YEAR_IN_SECONDS = 60 * 60 * 24 * 365;
const ONE_DAY_IN_SECONDS = 60 * 60 * 24;

function isLocale(value: string | undefined): value is Locale {
    return value !== undefined && (locales as readonly string[]).includes(value);
}

function localeFromPathname(pathname: string): Locale | null {
    const segment = pathname.split('/')[1];

    return isLocale(segment) ? segment : null;
}

/**
 * Dil karari: URL prefix > NEXT_LOCALE cookie > IP ulkesi > tr.
 *
 * Karari next-intl'e cookie uzerinden dayatiyoruz; boylece yol eslemesini
 * (ornegin /urunler <-> /en/products) ve yonlendirmeyi next-intl yapar,
 * biz sadece hangi dilin secilecegine karar veririz.
 */
export default function middleware(request: NextRequest) {
    const { pathname } = request.nextUrl;
    const prefixLocale = localeFromPathname(pathname);
    const cookieValue = request.cookies.get(LOCALE_COOKIE)?.value;
    const cookieLocale = isLocale(cookieValue) ? cookieValue : null;

    // ?lang=tr|en acik secim sayilir. Mobil WebView bunu kullanarak
    // yurt disi IP'de bile Turkce kaliyor.
    const queryValue = request.nextUrl.searchParams.get('lang') ?? undefined;
    const queryLocale = isLocale(queryValue) ? queryValue : null;

    const preferredLocale: Locale = prefixLocale
        ? prefixLocale
        : (queryLocale ??
          cookieLocale ??
          (isCrawler(request.headers.get('user-agent')) ? defaultLocale : detectLocale(request.headers)));

    let effectiveLocale: Locale = preferredLocale;
    let suggestion: Locale | null = null;

    if (!prefixLocale && preferredLocale !== defaultLocale && pathname !== '/') {
        // Locale'siz ic sayfada paylasilan/bookmark'lanan linki bozmuyoruz;
        // istenen URL'i sunup ustte oneri bandi gosteriyoruz. Kokte ise yonlendiriyoruz.
        effectiveLocale = defaultLocale;
        suggestion = preferredLocale;
    }

    request.cookies.set(LOCALE_COOKIE, effectiveLocale);

    const response = handleI18n(request);

    const explicitLocale = prefixLocale ?? queryLocale;
    if (explicitLocale) {
        // URL'de acik dil varsa kullanici secimi olarak kalicilastir.
        response.cookies.set(LOCALE_COOKIE, explicitLocale, {
            path: '/',
            maxAge: ONE_YEAR_IN_SECONDS,
            sameSite: 'lax',
        });
    }

    if (suggestion) {
        response.cookies.set(LOCALE_SUGGESTION_COOKIE, suggestion, {
            path: '/',
            maxAge: ONE_DAY_IN_SECONDS,
            sameSite: 'lax',
        });
    } else if (request.cookies.has(LOCALE_SUGGESTION_COOKIE)) {
        response.cookies.delete(LOCALE_SUGGESTION_COOKIE);
    }

    return response;
}

export const config = {
    matcher: ['/((?!api|_next|_vercel|.*\\..*).*)'],
};
