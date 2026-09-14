import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import { cookies } from 'next/headers';
import { notFound } from 'next/navigation';
import { NextIntlClientProvider, hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';

import Footer from '@/components/Footer';
import Header from '@/components/Header';
import LocaleSuggestion from '@/components/LocaleSuggestion';
import { LOCALE_SUGGESTION_COOKIE, locales, routing, type Locale } from '@/i18n/routing';
import { SITE_URL } from '@/lib/api/config';
import { buildAlternates } from '@/lib/seo';

import '../globals.css';

/** Blade tarafi da Inter kullaniyor; tema paritesi icin ayni aile. */
const inter = Inter({ subsets: ['latin'], display: 'swap' });

type LayoutProps = {
    children: React.ReactNode;
    params: Promise<{ locale: string }>;
};

export function generateStaticParams() {
    return routing.locales.map((locale) => ({ locale }));
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
    const { locale } = await params;
    const t = await getTranslations({ locale, namespace: 'home' });

    return {
        metadataBase: new URL(SITE_URL),
        title: {
            default: t('metaTitle'),
            template: `%s — ${t('metaTitle')}`,
        },
        description: t('metaDescription'),
        alternates: buildAlternates('/', locale as Locale),
    };
}

export default async function LocaleLayout({ children, params }: LayoutProps) {
    const { locale } = await params;

    if (!hasLocale(routing.locales, locale)) {
        notFound();
    }

    setRequestLocale(locale);

    const suggestionCookie = (await cookies()).get(LOCALE_SUGGESTION_COOKIE)?.value;
    const suggested =
        suggestionCookie && (locales as readonly string[]).includes(suggestionCookie) && suggestionCookie !== locale
            ? (suggestionCookie as Locale)
            : null;

    return (
        <html lang={locale} className={inter.className}>
            <body className="min-h-screen font-sans">
                <NextIntlClientProvider>
                    <div className="flex min-h-screen flex-col">
                        <Header />
                        {suggested ? <LocaleSuggestion suggested={suggested} /> : null}
                        <main className="flex-1">{children}</main>
                        <Footer />
                    </div>
                </NextIntlClientProvider>
            </body>
        </html>
    );
}
