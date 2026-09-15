import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { cache } from 'react';

import ProductCard from '@/components/ProductCard';
import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { ApiError } from '@/lib/api/client';
import { getVendor } from '@/lib/api/catalog';
import { buildAlternates } from '@/lib/seo';
import { storageUrl } from '@/lib/urls';

type PageProps = {
    params: Promise<{ locale: string; slug: string }>;
};

const loadVendor = cache(async (slug: string) => {
    try {
        const { data } = await getVendor(slug);

        return data;
    } catch (error) {
        if (error instanceof ApiError && error.status === 404) {
            return null;
        }

        throw error;
    }
});

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
    const { locale, slug } = await params;
    const vendor = await loadVendor(slug);

    if (!vendor) {
        // Bkz. urun detay sayfasi: rewrite yuzunden status 200 kaliyor, noindex veriyoruz.
        const common = await getTranslations({ locale, namespace: 'common' });

        return { title: common('notFoundTitle'), robots: { index: false, follow: false } };
    }

    return {
        title: vendor.name,
        description: vendor.description ?? undefined,
        alternates: buildAlternates({ pathname: '/satici/[slug]', params: { slug } }, locale as Locale),
    };
}

export default async function VendorPage({ params }: PageProps) {
    const { locale, slug } = await params;
    setRequestLocale(locale);

    const t = await getTranslations({ locale, namespace: 'vendors' });
    const nav = await getTranslations({ locale, namespace: 'nav' });

    const vendor = await loadVendor(slug);
    if (!vendor) {
        notFound();
    }

    const logo = storageUrl(vendor.logo);
    const products = vendor.products ?? [];

    return (
        <div className="by-container py-6">
            <nav className="mb-5 text-sm text-slate-500">
                <Link href="/" className="hover:text-slate-900">
                    {nav('home')}
                </Link>
                <span className="mx-2">/</span>
                <Link href="/saticilar" className="hover:text-slate-900">
                    {nav('vendors')}
                </Link>
                <span className="mx-2">/</span>
                <span className="text-slate-700">{vendor.name}</span>
            </nav>

            <div className="by-card p-6">
                <div className="flex flex-wrap items-center gap-4">
                    {logo ? (
                        /* eslint-disable-next-line @next/next/no-img-element -- gorseller Laravel /storage'dan geliyor */
                        <img src={logo} alt={vendor.name} className="h-16 w-16 rounded-2xl object-cover" />
                    ) : (
                        <span className="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-2xl font-bold text-white">
                            {vendor.name.charAt(0).toUpperCase()}
                        </span>
                    )}
                    <div className="min-w-0">
                        <h1 className="text-2xl font-bold tracking-tight text-slate-900">{vendor.name}</h1>
                        <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            {vendor.city ? <span>{[vendor.city, vendor.district].filter(Boolean).join(' / ')}</span> : null}
                            {vendor.rating_average ? (
                                <span className="by-badge">
                                    {t('rating')}: {vendor.rating_average}
                                </span>
                            ) : null}
                            {vendor.reviews_count ? (
                                <span className="by-badge">{t('reviews', { count: vendor.reviews_count })}</span>
                            ) : null}
                        </div>
                    </div>
                </div>

                {vendor.description ? (
                    <p className="mt-4 text-sm leading-relaxed text-slate-700">{vendor.description}</p>
                ) : null}
            </div>

            <section className="mt-8">
                <h2 className="text-xl font-bold tracking-tight text-slate-900">{t('products')}</h2>
                {products.length === 0 ? (
                    <p className="by-card mt-4 p-6 text-sm text-slate-600">{t('noProducts')}</p>
                ) : (
                    <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {products.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                )}
            </section>
        </div>
    );
}
