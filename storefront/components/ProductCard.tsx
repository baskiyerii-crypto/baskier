import { useLocale, useTranslations } from 'next-intl';

import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import type { Product } from '@/lib/api/types';
import { formatPrice } from '@/lib/format';
import { productPlaceholder, storageUrl } from '@/lib/urls';

type Props = {
    product: Product;
};

export default function ProductCard({ product }: Props) {
    const t = useTranslations('products');
    const locale = useLocale() as Locale;
    const image = storageUrl(product.main_image) ?? productPlaceholder(product.id);

    return (
        <Link
            href={{ pathname: '/urun/[slug]', params: { slug: product.slug } }}
            className="group by-card by-card-hover overflow-hidden"
        >
            <div className="aspect-[4/3] bg-slate-100">
                {/* eslint-disable-next-line @next/next/no-img-element -- gorseller Laravel /storage'dan geliyor */}
                <img
                    src={image}
                    alt={product.name}
                    className="h-full w-full object-cover transition group-hover:scale-[1.02]"
                    loading="lazy"
                />
            </div>
            <div className="p-4">
                <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0">
                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {product.category?.name ?? t('categoryFallback')}
                        </p>
                        <p className="mt-1 truncate text-sm font-semibold text-slate-900">{product.name}</p>
                        <p className="mt-1 text-xs text-slate-500">{product.vendor?.name ?? t('vendorFallback')}</p>
                    </div>
                    <span className="shrink-0 rounded-full bg-orange-50 px-3 py-1 text-sm font-bold text-orange-900">
                        {formatPrice(product.price, locale)}
                    </span>
                </div>
            </div>
        </Link>
    );
}
