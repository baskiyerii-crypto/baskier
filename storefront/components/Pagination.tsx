import { useTranslations } from 'next-intl';

import { Link } from '@/i18n/navigation';

type Props = {
    page: number;
    lastPage: number;
    query?: Record<string, string | number | undefined>;
};

export default function Pagination({ page, lastPage, query = {} }: Props) {
    const t = useTranslations('pagination');

    if (lastPage <= 1) {
        return null;
    }

    const base = Object.fromEntries(
        Object.entries(query).filter(([, value]) => value !== undefined && value !== ''),
    ) as Record<string, string | number>;

    return (
        <nav className="flex items-center justify-between gap-3" aria-label={t('status', { page, total: lastPage })}>
            {page > 1 ? (
                <Link href={{ pathname: '/urunler', query: { ...base, page: page - 1 } }} className="by-btn-secondary">
                    {t('previous')}
                </Link>
            ) : (
                <span className="by-btn-secondary opacity-40">{t('previous')}</span>
            )}

            <span className="text-sm font-semibold text-slate-600">{t('status', { page, total: lastPage })}</span>

            {page < lastPage ? (
                <Link href={{ pathname: '/urunler', query: { ...base, page: page + 1 } }} className="by-btn-secondary">
                    {t('next')}
                </Link>
            ) : (
                <span className="by-btn-secondary opacity-40">{t('next')}</span>
            )}
        </nav>
    );
}
