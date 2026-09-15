'use client';

import { useParams } from 'next/navigation';
import { useLocale, useTranslations } from 'next-intl';
import { useState, useTransition } from 'react';

import { usePathname, useRouter } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { persistLocale } from '@/lib/locale/persist';

type Props = {
    suggested: Locale;
};

/**
 * IP baska bir dil isaret ediyor ama kullanici ic sayfada: paylasilan linki
 * bozmamak icin yonlendirme yapmiyoruz, sadece oneriyoruz.
 */
export default function LocaleSuggestion({ suggested }: Props) {
    const t = useTranslations('locale');
    const active = useLocale() as Locale;
    const pathname = usePathname();
    const params = useParams();
    const router = useRouter();
    const [hidden, setHidden] = useState(false);
    const [isPending, startTransition] = useTransition();

    if (hidden) {
        return null;
    }

    function accept() {
        persistLocale(suggested);
        startTransition(() => {
            router.replace(
                // @ts-expect-error -- params tipi dinamik segmentlere gore degisiyor
                { pathname, params },
                { locale: suggested },
            );
        });
    }

    function dismiss() {
        // Mevcut dili kilitler; boylece oneri bir daha cikmaz.
        persistLocale(active);
        setHidden(true);
    }

    return (
        <div className="by-container pt-4">
            <div className="by-card by-surface-amber flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="text-sm font-bold text-slate-900">{t('suggestionTitle')}</p>
                    <p className="mt-1 text-sm text-slate-600">{t('suggestionBody')}</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <button type="button" onClick={accept} disabled={isPending} className="by-btn-primary">
                        {t('suggestionAccept')}
                    </button>
                    <button type="button" onClick={dismiss} className="by-btn-secondary">
                        {t('suggestionDismiss')}
                    </button>
                </div>
            </div>
        </div>
    );
}
