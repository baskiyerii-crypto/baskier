import { LOCALE_COOKIE, type Locale } from '@/i18n/routing';

const ONE_YEAR_IN_SECONDS = 60 * 60 * 24 * 365;

/**
 * Kullanici dili elle sectiginde cagrilir. Cookie yazildigi andan sonra
 * middleware IP onerisini devreye sokmaz.
 */
export function persistLocale(locale: Locale): void {
    document.cookie = `${LOCALE_COOKIE}=${locale}; path=/; max-age=${ONE_YEAR_IN_SECONDS}; samesite=lax`;
}
