import { API_URL } from './config';
import type { ApiMeta } from './types';

export class ApiError extends Error {
    constructor(
        message: string,
        readonly status: number,
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

export type ApiResult<T> = {
    data: T;
    meta: ApiMeta;
};

type WrappedResponse = {
    success: boolean;
    message?: string | null;
    data?: unknown;
    meta?: ApiMeta;
    errors?: unknown;
};

type LaravelPaginator = {
    current_page: number;
    last_page?: number;
    per_page?: number;
    total?: number;
    data: unknown[];
};

function isWrapped(payload: unknown): payload is WrappedResponse {
    return (
        typeof payload === 'object' &&
        payload !== null &&
        'success' in payload &&
        typeof (payload as WrappedResponse).success === 'boolean' &&
        'data' in payload
    );
}

function isPaginator(payload: unknown): payload is LaravelPaginator {
    return (
        typeof payload === 'object' &&
        payload !== null &&
        'current_page' in payload &&
        Array.isArray((payload as LaravelPaginator).data)
    );
}

/**
 * Laravel uc sekil donuyor:
 * - ApiResponse sarmalayicisi: { success, message, data, meta, errors }
 * - Duz JSON (ornegin HomeContentController, CategoryController)
 * - Duz Laravel paginator (ornegin VendorController)
 * Ucunu de ayni sonuca indiriyoruz.
 */
export function unwrap<T>(payload: unknown): ApiResult<T> {
    if (isWrapped(payload)) {
        return { data: payload.data as T, meta: payload.meta ?? null };
    }

    if (isPaginator(payload)) {
        return {
            data: payload.data as T,
            meta: {
                current_page: payload.current_page,
                last_page: payload.last_page,
                per_page: payload.per_page,
                total: payload.total,
            },
        };
    }

    return { data: payload as T, meta: null };
}

type QueryValue = string | number | boolean | null | undefined;

export type ApiRequestOptions = {
    query?: Record<string, QueryValue>;
    /** ISR suresi (saniye). false verilirse cache'lenmez. */
    revalidate?: number | false;
    token?: string | null;
    method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
    body?: unknown;
};

function buildUrl(path: string, query?: Record<string, QueryValue>): string {
    const url = new URL(`${API_URL}${path.startsWith('/') ? path : `/${path}`}`);

    for (const [key, value] of Object.entries(query ?? {})) {
        if (value !== undefined && value !== null && value !== '') {
            url.searchParams.set(key, String(value));
        }
    }

    return url.toString();
}

export async function apiRequest<T>(path: string, options: ApiRequestOptions = {}): Promise<ApiResult<T>> {
    const { query, revalidate, token, method = 'GET', body } = options;

    const headers: Record<string, string> = { Accept: 'application/json' };
    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }
    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    const response = await fetch(buildUrl(path, query), {
        method,
        headers,
        body: body === undefined ? undefined : JSON.stringify(body),
        cache: revalidate === false ? 'no-store' : undefined,
        next: revalidate === false || revalidate === undefined ? undefined : { revalidate },
    });

    const text = await response.text();
    let payload: unknown = null;
    if (text) {
        try {
            payload = JSON.parse(text);
        } catch {
            payload = { message: text };
        }
    }

    if (!response.ok) {
        const message =
            (isWrapped(payload) && payload.message) ||
            (typeof payload === 'object' && payload !== null && 'message' in payload
                ? String((payload as { message?: unknown }).message)
                : null) ||
            `HTTP ${response.status}`;

        throw new ApiError(message, response.status);
    }

    return unwrap<T>(payload);
}

export function apiGet<T>(path: string, options: Omit<ApiRequestOptions, 'method' | 'body'> = {}) {
    return apiRequest<T>(path, { ...options, method: 'GET' });
}
