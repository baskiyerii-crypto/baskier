import type { Metadata } from 'next';
import { getTranslations, setRequestLocale } from 'next-intl/server';

import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { getVendors } from '@/lib/api/catalog';
import { buildAlternates } from '@/lib/seo';
import { storageUrl } from '@/lib/urls';

type PageProps = {
    params: Promise<{ locale: string }>;
    searchParams: Promise<{ page?: string }>;
};

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
    const { locale } = await params;
    const t = await getTranslations({ locale, namespace: 'vendors' });

    return {
        title: t('metaTitle'),
        description: t('metaDescription'),
        alternates: buildAlternates('/saticilar', locale as Locale),
    };
}

export default async function VendorsPage({ params, searchParams }: PageProps) {
    const { locale } = await params;
    setRequestLocale(locale);

    const { page } = await searchParams;
    const currentPage = Number.parseInt(page ?? '1', 10) || 1;

    const t = await getTranslations({ locale, namespace: 'vendors' });

    let vendors: Awaited<ReturnType<typeof getVendors>>['data'] = [];
    try {
        vendors = (await getVendors(currentPage)).data;
    } catch {
        // Coolify build aninda API yoksa bos liste ile uretilir.
    }

    return (
        <div className="by-container py-6">
            <div className="by-card p-5 md:p-6">
                <p className="by-section-title text-slate-500">{t('eyebrow')}</p>
                <h1 className="mt-1 text-2xl font-bold tracking-tight text-slate-900">{t('title')}</h1>
            </div>

            <div className="mt-6">
                {vendors.length === 0 ? (
                    <div className="by-card p-8 text-center text-sm text-slate-600">{t('empty')}</div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {vendors.map((vendor) => {
                            const logo = storageUrl(vendor.logo);

                            return (
                                <Link
                                    key={vendor.id}
                                    href={{ pathname: '/satici/[slug]', params: { slug: vendor.slug } }}
                                    className="by-card by-card-hover p-5"
                                >
                                    <div className="flex items-center gap-3">
                                        {logo ? (
                                            /* eslint-disable-next-line @next/next/no-img-element -- gorseller Laravel /storage'dan geliyor */
                                            <img
                                                src={logo}
                                                alt={vendor.name}
                                                className="h-12 w-12 rounded-2xl object-cover"
                                                loading="lazy"
                                            />
                                        ) : (
                                            <span className="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-lg font-bold text-white">
                                                {vendor.name.charAt(0).toUpperCase()}
                                            </span>
                                        )}
                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-bold text-slate-900">{vendor.name}</p>
                                            {vendor.city ? (
                                                <p className="truncate text-xs text-slate-500">
                                                    {[vendor.city, vendor.district].filter(Boolean).join(' / ')}
                                                </p>
                                            ) : null}
                                        </div>
                                    </div>

                                    {vendor.description ? (
                                        <p className="mt-3 line-clamp-3 text-sm text-slate-600">{vendor.description}</p>
                                    ) : null}
                                </Link>
                            );
                        })}
                    </div>
                )}
            </div>
        </div>
    );
}
