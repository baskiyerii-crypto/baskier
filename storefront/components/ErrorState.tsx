'use client';

import { useTranslations } from 'next-intl';

type Props = {
    reset: () => void;
};

export default function ErrorState({ reset }: Props) {
    const t = useTranslations('common');

    return (
        <div className="by-container py-16">
            <div className="by-card p-8 text-center">
                <p className="text-lg font-bold text-slate-900">{t('errorTitle')}</p>
                <p className="mt-2 text-sm text-slate-600">{t('errorBody')}</p>
                <button type="button" onClick={reset} className="by-btn-primary mt-5">
                    {t('retry')}
                </button>
            </div>
        </div>
    );
}
