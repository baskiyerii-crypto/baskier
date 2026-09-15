import { getPathname } from '@/i18n/navigation';
import { defaultLocale, locales, type Locale } from '@/i18n/routing';
import { SITE_URL } from '@/lib/api/config';

type Href = Parameters<typeof getPathname>[0]['href'];

export function absoluteUrl(href: Href, locale: Locale): string {
    return `${SITE_URL}${getPathname({ href, locale })}`;
}

/** Her sayfada canonical + tr/en hreflang; x-default Turkce (ana pazar). */
export function buildAlternates(href: Href, locale: Locale) {
    const languages: Record<string, string> = {};

    for (const candidate of locales) {
        languages[candidate] = absoluteUrl(href, candidate);
    }

    return {
        canonical: absoluteUrl(href, locale),
        languages: {
            ...languages,
            'x-default': languages[defaultLocale],
        },
    };
}
