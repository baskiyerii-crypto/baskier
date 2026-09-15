import type { Locale } from '@/i18n/routing';

const INTL_LOCALE: Record<Locale, string> = {
    tr: 'tr-TR',
    en: 'en-GB',
};

export function toNumber(value: string | number | null | undefined): number | null {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const parsed = typeof value === 'number' ? value : Number.parseFloat(value);

    return Number.isFinite(parsed) ? parsed : null;
}

export function formatPrice(value: string | number | null | undefined, locale: Locale): string | null {
    const amount = toNumber(value);
    if (amount === null) {
        return null;
    }

    return new Intl.NumberFormat(INTL_LOCALE[locale], {
        style: 'currency',
        currency: 'TRY',
        maximumFractionDigits: 2,
    }).format(amount);
}
