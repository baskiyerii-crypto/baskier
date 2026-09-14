import { useTranslations } from 'next-intl';

export default function LoadingState() {
    const t = useTranslations('common');

    return (
        <div className="by-container py-16">
            <div className="by-card flex items-center justify-center gap-3 p-10 text-sm font-semibold text-slate-600">
                <span
                    className="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-500"
                    aria-hidden
                />
                {t('loading')}
            </div>
        </div>
    );
}
