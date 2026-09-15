import { defaultLocale, locales, type Locale } from '@/i18n/routing';

/** Proxy'lerin ulke kodunu yazdigi basliklar; sirayla denenir. */
const COUNTRY_HEADERS = ['x-vercel-ip-country', 'cf-ipcountry', 'x-country-code'];

export function readCountry(headers: Headers): string | null {
    for (const name of COUNTRY_HEADERS) {
        const value = headers.get(name)?.trim().toUpperCase();
        if (value && value !== 'XX') {
            return value;
        }
    }

    return null;
}

/** Kural sade: TR ise Turkce, diger her ulke Ingilizce. */
export function countryToLocale(country: string | null): Locale | null {
    if (!country) {
        return null;
    }

    return country === 'TR' ? 'tr' : 'en';
}

export function localeFromAcceptLanguage(headers: Headers): Locale | null {
    const header = headers.get('accept-language');
    if (!header) {
        return null;
    }

    const preferred = header
        .split(',')
        .map((part) => {
            const [tag, ...params] = part.trim().split(';');
            const quality = params
                .map((param) => param.trim())
                .find((param) => param.startsWith('q='));

            return {
                language: tag.trim().toLowerCase().split('-')[0],
                quality: quality ? Number.parseFloat(quality.slice(2)) : 1,
            };
        })
        .filter((entry) => Number.isFinite(entry.quality))
        .sort((a, b) => b.quality - a.quality)
        .find((entry) => locales.includes(entry.language as Locale));

    return (preferred?.language as Locale) ?? null;
}

/** IP ulkesi > tarayici dili > tr */
export function detectLocale(headers: Headers): Locale {
    return countryToLocale(readCountry(headers)) ?? localeFromAcceptLanguage(headers) ?? defaultLocale;
}
