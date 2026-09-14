'use client';

import { useParams } from 'next/navigation';
import { useLocale, useTranslations } from 'next-intl';
import { useTransition } from 'react';

import { usePathname, useRouter } from '@/i18n/navigation';
import { locales, type Locale } from '@/i18n/routing';
import { persistLocale } from '@/lib/locale/persist';

export default function LocaleSwitcher() {
    const t = useTranslations('locale');
    const active = useLocale() as Locale;
    const pathname = usePathname();
    const params = useParams();
    const router = useRouter();
    const [isPending, startTransition] = useTransition();

    function change(next: Locale) {
        if (next === active) {
            return;
        }

        persistLocale(next);
        startTransition(() => {
            // Ayni sayfanin diger dildeki adresine gider: /urun/x -> /en/product/x
            router.replace(
                // @ts-expect-error -- params tipi dinamik segmentlere gore degisiyor
                { pathname, params },
                { locale: next },
            );
        });
    }

    return (
        <div
            className="inline-flex items-center rounded-full border border-slate-200 bg-white/70 p-0.5"
            aria-label={t('label')}
        >
            {locales.map((locale) => (
                <button
                    key={locale}
                    type="button"
                    onClick={() => change(locale)}
                    disabled={isPending}
                    aria-current={locale === active}
                    className={
                        locale === active
                            ? 'rounded-full bg-slate-900 px-3 py-1.5 text-xs font-bold text-white'
                            : 'rounded-full px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 disabled:opacity-60'
                    }
                >
                    {locale === 'tr' ? t('trShort') : t('enShort')}
                </button>
            ))}
        </div>
    );
}
