import type { Metadata } from 'next';
import { getTranslations, setRequestLocale } from 'next-intl/server';

import Pagination from '@/components/Pagination';
import ProductCard from '@/components/ProductCard';
import { Link, getPathname } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { getCategories, getProducts } from '@/lib/api/catalog';
import { buildAlternates } from '@/lib/seo';

type SearchParams = {
    q?: string;
    category_id?: string;
    type?: string;
    page?: string;
};

type PageProps = {
    params: Promise<{ locale: string }>;
    searchParams: Promise<SearchParams>;
};

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
    const { locale } = await params;
    const t = await getTranslations({ locale, namespace: 'products' });

    return {
        title: t('metaTitle'),
        description: t('metaDescription'),
        alternates: buildAlternates('/urunler', locale as Locale),
    };
}

export default async function ProductsPage({ params, searchParams }: PageProps) {
    const { locale } = await params;
    setRequestLocale(locale);

    const { q, category_id: categoryId, type, page } = await searchParams;
    const currentPage = Number.parseInt(page ?? '1', 10) || 1;

    const t = await getTranslations({ locale, namespace: 'products' });
    const nav = await getTranslations({ locale, namespace: 'nav' });

    let categories: Awaited<ReturnType<typeof getCategories>>['data'] = [];
    let products: Awaited<ReturnType<typeof getProducts>>['data'] = [];
    let meta: Awaited<ReturnType<typeof getProducts>>['meta'] = null;

    try {
        const [categoryResult, productResult] = await Promise.all([
            getCategories(),
            getProducts({ q, categoryId, type, page: currentPage }),
        ]);
        categories = categoryResult.data;
        products = productResult.data;
        meta = productResult.meta;
    } catch {
        // Coolify build aninda API yoksa bos liste ile uretilir.
    }

    const activeCategoryId = categoryId ? Number.parseInt(categoryId, 10) : null;
    const searchAction = getPathname({ href: '/urunler', locale: locale as Locale });

    return (
        <div className="by-container py-6">
            <div className="grid gap-6 lg:grid-cols-[320px,1fr]">
                <aside className="by-card overflow-hidden">
                    <div className="relative">
                        <div className="h-28 bg-gradient-to-br from-orange-400/20 via-white to-indigo-500/10" />
                        <div className="absolute inset-x-0 top-0 p-5">
                            <p className="by-section-title text-slate-500">{t('filter')}</p>
                            <h2 className="mt-1 text-lg font-bold tracking-tight text-slate-900">{t('categories')}</h2>
                        </div>
                    </div>
                    <div className="p-5 pt-3">
                        {categories.length === 0 ? (
                            <p className="text-sm text-slate-500">{t('noCategories')}</p>
                        ) : (
                            <div className="space-y-1">
                                <Link
                                    href="/urunler"
                                    className={
                                        activeCategoryId
                                            ? 'flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50'
                                            : 'flex items-center justify-between rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-900'
                                    }
                                >
                                    <span>{t('all')}</span>
                                    <span className="by-badge">→</span>
                                </Link>

                                {categories.map((category) => (
                                    <div key={category.id}>
                                        <Link
                                            href={{ pathname: '/urunler', query: { category_id: category.id } }}
                                            className={
                                                activeCategoryId === category.id
                                                    ? 'flex items-center justify-between rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-900'
                                                    : 'flex items-center justify-between rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50'
                                            }
                                        >
                                            <span className="truncate">{category.name}</span>
                                            <span className="text-slate-400">›</span>
                                        </Link>

                                        {(category.children ?? []).map((child) => (
                                            <Link
                                                key={child.id}
                                                href={{ pathname: '/urunler', query: { category_id: child.id } }}
                                                className={
                                                    activeCategoryId === child.id
                                                        ? 'ml-3 flex items-center rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-900'
                                                        : 'ml-3 flex items-center rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-slate-50'
                                                }
                                            >
                                                <span className="truncate">{child.name}</span>
                                            </Link>
                                        ))}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </aside>

                <section>
                    <div className="by-card p-5 md:p-6">
                        <div className="flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <p className="by-section-title text-slate-500">{t('eyebrow')}</p>
                                <h1 className="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                                    {type === 'digital' ? t('digitalTitle') : t('title')}
                                </h1>
                                {q ? <p className="mt-1 text-sm text-slate-600">{t('searchResults', { query: q })}</p> : null}
                                {meta?.total !== undefined ? (
                                    <p className="mt-1 text-xs text-slate-500">{t('total', { count: meta.total })}</p>
                                ) : null}
                            </div>

                            <form action={searchAction} className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                                {categoryId ? <input type="hidden" name="category_id" value={categoryId} /> : null}
                                {type ? <input type="hidden" name="type" value={type} /> : null}
                                <input
                                    name="q"
                                    defaultValue={q ?? ''}
                                    placeholder={nav('searchPlaceholder')}
                                    aria-label={nav('search')}
                                    className="w-full rounded-full border border-slate-200 bg-white/80 px-4 py-2.5 text-sm outline-none ring-orange-400 focus:ring-2 sm:w-[340px]"
                                />
                                <button className="by-btn-primary" type="submit">
                                    {nav('search')}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div className="mt-6">
                        {products.length === 0 ? (
                            <div className="by-card p-8 text-center">
                                <p className="text-sm text-slate-600">{t('empty')}</p>
                                <Link href="/urunler" className="by-btn-secondary mt-4 inline-flex">
                                    {t('clearFilters')}
                                </Link>
                            </div>
                        ) : (
                            <>
                                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    {products.map((product) => (
                                        <ProductCard key={product.id} product={product} />
                                    ))}
                                </div>

                                <div className="mt-6">
                                    <Pagination
                                        page={meta?.current_page ?? currentPage}
                                        lastPage={meta?.last_page ?? 1}
                                        query={{ q, category_id: categoryId, type }}
                                    />
                                </div>
                            </>
                        )}
                    </div>
                </section>
            </div>
        </div>
    );
}
