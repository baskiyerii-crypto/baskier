import { useLocale, useTranslations } from 'next-intl';

import { Link, getPathname } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { legacyUrl } from '@/lib/urls';
import LocaleSwitcher from '@/components/LocaleSwitcher';

type Props = {
    query?: string;
};

export default function Header({ query }: Props) {
    const t = useTranslations('nav');
    const locale = useLocale() as Locale;
    const searchAction = getPathname({ href: '/urunler', locale });

    return (
        <header className="sticky top-0 z-50 border-b border-slate-200/70 bg-white/70 backdrop-blur">
            <div className="by-container py-4">
                <div className="flex items-center justify-between gap-4">
                    <Link href="/" className="flex items-center gap-2 text-sm font-extrabold tracking-tight text-slate-900">
                        <span className="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-sm">
                            B
                        </span>
                        <span>{t('brand')}</span>
                    </Link>

                    <form action={searchAction} className="hidden flex-1 lg:block">
                        <input
                            className="by-input"
                            name="q"
                            defaultValue={query ?? ''}
                            placeholder={t('searchPlaceholder')}
                            aria-label={t('search')}
                        />
                    </form>

                    <nav className="hidden items-center gap-4 text-sm font-semibold text-slate-700 xl:flex">
                        <Link href="/urunler" className="hover:text-slate-900">
                            {t('products')}
                        </Link>
                        <Link href="/saticilar" className="hover:text-slate-900">
                            {t('vendors')}
                        </Link>
                        {/* Henuz Blade'de sunuluyor */}
                        <a href={legacyUrl('/is-ilanlari')} className="hover:text-slate-900">
                            {t('jobs')}
                        </a>
                    </nav>

                    <div className="flex items-center gap-2">
                        <LocaleSwitcher />
                        <a href={legacyUrl('/giris')} className="hidden sm:inline-flex by-btn-secondary">
                            {t('login')}
                        </a>
                        <a href={legacyUrl('/teklif-talebi')} className="by-btn-cta px-4 py-2.5">
                            {t('quote')}
                        </a>
                    </div>
                </div>

                <form action={searchAction} className="mt-4 lg:hidden">
                    <input
                        className="by-input"
                        name="q"
                        defaultValue={query ?? ''}
                        placeholder={t('searchPlaceholder')}
                        aria-label={t('search')}
                    />
                </form>
            </div>
        </header>
    );
}
