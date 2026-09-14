import type { Metadata } from 'next';
import { getTranslations, setRequestLocale } from 'next-intl/server';

import ProductCard from '@/components/ProductCard';
import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { getHome } from '@/lib/api/catalog';
import { buildAlternates } from '@/lib/seo';
import { legacyUrl } from '@/lib/urls';

type PageProps = {
    params: Promise<{ locale: string }>;
};

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
    const { locale } = await params;
    const t = await getTranslations({ locale, namespace: 'home' });

    return {
        title: t('metaTitle'),
        description: t('metaDescription'),
        alternates: buildAlternates('/', locale as Locale),
    };
}

export default async function HomePage({ params }: PageProps) {
    const { locale } = await params;
    setRequestLocale(locale);

    const t = await getTranslations({ locale, namespace: 'home' });
    const { data } = await getHome();

    const categories = data.categories ?? [];
    const featured = data.featured_products ?? [];
    const digital = data.digital_products ?? [];

    return (
        <div className="by-container space-y-10 py-8">
            <section className="by-card by-gradient-border overflow-hidden p-8 md:p-12">
                <p className="by-section-title text-slate-500">{t('heroEyebrow')}</p>
                <h1 className="mt-3 max-w-2xl text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">
                    {t('heroTitle')}
                </h1>
                <p className="mt-3 max-w-xl text-sm leading-relaxed text-slate-600 md:text-base">{t('heroBody')}</p>
                <div className="mt-6 flex flex-wrap gap-3">
                    <Link href="/urunler" className="by-btn-primary px-6 py-3 text-base">
                        {t('heroPrimary')}
                    </Link>
                    {/* Teklif akisi bu fazda Blade'de */}
                    <a href={legacyUrl('/teklif-talebi')} className="by-btn-cta px-6 py-3 text-base">
                        {t('heroSecondary')}
                    </a>
                </div>
            </section>

            {categories.length > 0 ? (
                <section>
                    <div className="by-section-head">
                        <h2 className="text-xl font-bold tracking-tight text-slate-900">{t('categories')}</h2>
                        <Link href="/urunler" className="by-link text-sm">
                            {t('seeAll')}
                        </Link>
                    </div>
                    <div className="mt-4 flex flex-wrap gap-2">
                        {categories.map((category) => (
                            <Link
                                key={category.id}
                                href={{ pathname: '/urunler', query: { category_id: category.id } }}
                                className="by-badge px-4 py-2 hover:bg-white"
                            >
                                {category.name}
                            </Link>
                        ))}
                    </div>
                </section>
            ) : null}

            <section>
                <div className="by-section-head">
                    <h2 className="text-xl font-bold tracking-tight text-slate-900">{t('featuredProducts')}</h2>
                    <Link href="/urunler" className="by-link text-sm">
                        {t('seeAll')}
                    </Link>
                </div>
                {featured.length === 0 ? (
                    <p className="by-card mt-4 p-6 text-sm text-slate-600">{t('empty')}</p>
                ) : (
                    <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {featured.slice(0, 8).map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                )}
            </section>

            {digital.length > 0 ? (
                <section>
                    <div className="by-section-head">
                        <h2 className="text-xl font-bold tracking-tight text-slate-900">{t('digitalProducts')}</h2>
                        <Link href={{ pathname: '/urunler', query: { type: 'digital' } }} className="by-link text-sm">
                            {t('seeAll')}
                        </Link>
                    </div>
                    <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {digital.slice(0, 8).map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                </section>
            ) : null}
        </div>
    );
}
