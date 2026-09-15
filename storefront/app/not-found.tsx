import Link from 'next/link';
import { getTranslations } from 'next-intl/server';

import { defaultLocale } from '@/i18n/routing';

import './globals.css';

/**
 * Kok seviyedeki not-found sinirI. [locale] altinda tutuldugunda Next
 * durum kodunu 404 yapmiyor (soft 404), bu yuzden burada duruyor.
 * Dil bilgisi olmadan calistigi icin varsayilan dili kullanir.
 */
export default async function NotFound() {
    const t = await getTranslations({ locale: defaultLocale, namespace: 'common' });

    return (
        <html lang={defaultLocale}>
            <body className="min-h-screen font-sans">
                <div className="by-container py-20">
                    <div className="by-card p-8 text-center">
                        <p className="text-lg font-bold text-slate-900">{t('notFoundTitle')}</p>
                        <p className="mt-2 text-sm text-slate-600">{t('notFoundBody')}</p>
                        <Link href="/" className="by-btn-primary mt-5">
                            {t('backHome')}
                        </Link>
                    </div>
                </div>
            </body>
        </html>
    );
}
