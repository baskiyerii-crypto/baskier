import type { MetadataRoute } from 'next';

import { defaultLocale, locales, type Locale } from '@/i18n/routing';
import { getProducts, getVendors } from '@/lib/api/catalog';
import type { Product, Vendor } from '@/lib/api/types';
import { absoluteUrl } from '@/lib/seo';

type Href = Parameters<typeof absoluteUrl>[0];

const MAX_PAGES = 50;

function entry(href: Href, changeFrequency: 'daily' | 'weekly'): MetadataRoute.Sitemap[number] {
    const languages: Record<string, string> = {};
    for (const locale of locales) {
        languages[locale] = absoluteUrl(href, locale);
    }

    return {
        url: absoluteUrl(href, defaultLocale as Locale),
        changeFrequency,
        alternates: { languages },
    };
}

async function collectProducts(): Promise<Product[]> {
    const items: Product[] = [];
    let page = 1;

    while (page <= MAX_PAGES) {
        const { data, meta } = await getProducts({ page });
        items.push(...data);

        const lastPage = meta?.last_page ?? page;
        if (page >= lastPage) {
            break;
        }
        page += 1;
    }

    return items;
}

async function collectVendors(): Promise<Vendor[]> {
    const items: Vendor[] = [];
    let page = 1;

    while (page <= MAX_PAGES) {
        const { data, meta } = await getVendors(page);
        items.push(...data);

        const lastPage = meta?.last_page ?? page;
        if (page >= lastPage) {
            break;
        }
        page += 1;
    }

    return items;
}

/**
 * Yalnizca Next'in sundugu sayfalar. Blade'de duran kurumsal sayfalar
 * Laravel tarafindan listelenmeye devam eder.
 *
 * Coolify build sirasinda API gecici olarak ulasilamazsa sadece sabit
 * sayfalar yazilir; runtime ISR sonradan urun/satici ekler.
 */
export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
    const staticEntries = [entry('/', 'daily'), entry('/urunler', 'daily'), entry('/saticilar', 'weekly')];

    try {
        const [products, vendors] = await Promise.all([collectProducts(), collectVendors()]);

        return [
            ...staticEntries,
            ...products.map((product) =>
                entry({ pathname: '/urun/[slug]', params: { slug: product.slug } }, 'weekly'),
            ),
            ...vendors.map((vendor) => entry({ pathname: '/satici/[slug]', params: { slug: vendor.slug } }, 'weekly')),
        ];
    } catch {
        return staticEntries;
    }
}
