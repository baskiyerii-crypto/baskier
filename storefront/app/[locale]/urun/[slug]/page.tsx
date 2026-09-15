import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { cache } from 'react';

import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { ApiError } from '@/lib/api/client';
import { getProduct } from '@/lib/api/catalog';
import { formatPrice } from '@/lib/format';
import { buildAlternates } from '@/lib/seo';
import { legacyUrl, storageUrl } from '@/lib/urls';
import type { Product } from '@/lib/api/types';

type PageProps = {
    params: Promise<{ locale: string; slug: string }>;
};

/** generateMetadata ve sayfa ayni veriyi kullaniyor; cache ile tek istek. */
const loadProduct = cache(async (slug: string): Promise<Product | null> => {
    try {
        const { data } = await getProduct(slug);

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
    const product = await loadProduct(slug);
    const alternates = buildAlternates({ pathname: '/urun/[slug]', params: { slug } }, locale as Locale);

    if (!product) {
        // next-intl'in locale rewrite'i notFound() statusunu 200'e dusuruyor.
        // Soft 404 indekslenmesin diye sayfayi noindex isaretliyoruz.
        const common = await getTranslations({ locale, namespace: 'common' });

        return { title: common('notFoundTitle'), robots: { index: false, follow: false } };
    }

    return {
        title: product.name,
        description: product.short_description ?? undefined,
        alternates,
    };
}

export default async function ProductPage({ params }: PageProps) {
    const { locale, slug } = await params;
    setRequestLocale(locale);

    const t = await getTranslations({ locale, namespace: 'product' });
    const productsT = await getTranslations({ locale, namespace: 'products' });
    const nav = await getTranslations({ locale, namespace: 'nav' });

    const product = await loadProduct(slug);
    if (!product) {
        notFound();
    }

    const image = storageUrl(product.main_image);
    const inStock = (product.stock ?? 0) > 0;

    return (
        <div className="by-container py-6">
            <nav className="mb-5 text-sm text-slate-500">
                <Link href="/" className="hover:text-slate-900">
                    {nav('home')}
                </Link>
                <span className="mx-2">/</span>
                <Link href="/urunler" className="hover:text-slate-900">
                    {nav('products')}
                </Link>
                {product.category ? (
                    <>
                        <span className="mx-2">/</span>
                        <Link
                            href={{ pathname: '/urunler', query: { category_id: product.category.id } }}
                            className="hover:text-slate-900"
                        >
                            {product.category.name}
                        </Link>
                    </>
                ) : null}
                <span className="mx-2">/</span>
                <span className="text-slate-700">{product.name}</span>
            </nav>

            <div className="grid gap-6 lg:grid-cols-2">
                <div className="by-card overflow-hidden">
                    <div className="aspect-[4/3] bg-slate-100">
                        {/* eslint-disable-next-line @next/next/no-img-element -- gorseller Laravel /storage'dan geliyor */}
                        <img
                            src={image ?? `https://picsum.photos/1200/900?random=detay${product.id}`}
                            alt={product.name}
                            className="h-full w-full object-cover"
                        />
                    </div>
                </div>

                <div className="space-y-6">
                    <div className="by-card p-6">
                        <p className="by-section-title text-slate-500">
                            {product.category?.name ?? productsT('categoryFallback')}
                        </p>
                        <h1 className="mt-2 text-3xl font-bold tracking-tight text-slate-900">{product.name}</h1>

                        <div className="mt-4 flex flex-wrap items-center gap-3">
                            <span className="rounded-full bg-orange-50 px-4 py-2 text-xl font-extrabold text-orange-900">
                                {formatPrice(product.price, locale as Locale)}
                            </span>
                            {inStock ? (
                                <span className="by-badge border-emerald-200 bg-emerald-50 text-emerald-800">
                                    {t('inStock')}
                                </span>
                            ) : (
                                <span className="by-badge">{t('outOfStock')}</span>
                            )}
                        </div>

                        {product.vendor ? (
                            <div className="mt-5 rounded-2xl border border-slate-200 bg-white/70 p-4">
                                <p className="by-section-title text-slate-500">{t('vendor')}</p>
                                <div className="mt-2 flex flex-wrap items-center justify-between gap-3">
                                    <Link
                                        href={{ pathname: '/satici/[slug]', params: { slug: product.vendor.slug } }}
                                        className="text-sm font-semibold text-slate-900 hover:underline"
                                    >
                                        {product.vendor.name}
                                    </Link>
                                    <Link
                                        href={{ pathname: '/satici/[slug]', params: { slug: product.vendor.slug } }}
                                        className="by-btn-secondary"
                                    >
                                        {t('viewVendor')}
                                    </Link>
                                </div>
                            </div>
                        ) : null}

                        {product.short_description ? (
                            <p className="mt-5 text-sm leading-relaxed text-slate-700">{product.short_description}</p>
                        ) : null}

                        {product.description ? (
                            <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p className="by-section-title text-slate-500">{t('description')}</p>
                                <p className="mt-2 whitespace-pre-wrap text-sm text-slate-700">{product.description}</p>
                            </div>
                        ) : null}
                    </div>

                    <div className="by-card p-6">
                        <p className="by-section-title text-slate-500">{t('purchase')}</p>
                        {/*
                          Sepet ve odeme adimlari bu fazda Laravel tarafinda kaliyor (Faz 1b).
                          Bu yuzden CTA mevcut oturum akisina yonlendiriyor.
                        */}
                        <div className="mt-4 flex flex-wrap gap-3">
                            {inStock ? (
                                <a href={legacyUrl('/giris')} className="by-btn-cta px-6 py-3 text-base">
                                    {t('purchaseOnSite')}
                                </a>
                            ) : null}
                            <a href={legacyUrl('/teklif-talebi')} className="by-btn-secondary px-6 py-3 text-base">
                                {t('quoteButton')}
                            </a>
                        </div>
                        <p className="mt-3 text-xs text-slate-500">{t('purchaseHint')}</p>
                        <p className="mt-1 text-xs text-slate-500">{t('quoteHint')}</p>
                    </div>
                </div>
            </div>
        </div>
    );
}
