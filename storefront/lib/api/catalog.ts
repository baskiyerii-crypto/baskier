import { apiGet } from './client';
import { DETAIL_REVALIDATE, LIST_REVALIDATE } from './config';
import type { Category, HomePayload, Product, Vendor } from './types';

export function getHome() {
    return apiGet<HomePayload>('/home', { revalidate: LIST_REVALIDATE });
}

export function getCategories() {
    return apiGet<Category[]>('/categories', { revalidate: LIST_REVALIDATE });
}

export type ProductListParams = {
    q?: string;
    categoryId?: number | string;
    type?: string;
    page?: number;
};

export function getProducts({ q, categoryId, type, page }: ProductListParams = {}) {
    return apiGet<Product[]>('/products', {
        query: {
            q,
            category_id: categoryId,
            type,
            page,
        },
        revalidate: LIST_REVALIDATE,
    });
}

export function getProduct(slug: string) {
    return apiGet<Product>(`/products/${encodeURIComponent(slug)}`, { revalidate: DETAIL_REVALIDATE });
}

export function getVendors(page?: number) {
    return apiGet<Vendor[]>('/vendors', { query: { page }, revalidate: LIST_REVALIDATE });
}

export function getVendor(slug: string) {
    return apiGet<Vendor & { products?: Product[] }>(`/vendors/${encodeURIComponent(slug)}`, {
        revalidate: DETAIL_REVALIDATE,
    });
}
