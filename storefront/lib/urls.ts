import { WEB_URL } from './api/config';

/** Laravel'in /storage dizinindeki gorseller (Blade'deki asset('storage/...') ile ayni). */
export function storageUrl(path: string | null | undefined): string | null {
    if (!path) {
        return null;
    }

    if (/^https?:\/\//i.test(path)) {
        return path;
    }

    return `${WEB_URL}/storage/${path.replace(/^\/+/, '')}`;
}

/** Henuz Blade'de duran sayfalar (giris, sepet, paneller) icin mutlak link. */
export function legacyUrl(path: string): string {
    return `${WEB_URL}${path.startsWith('/') ? path : `/${path}`}`;
}

export function productPlaceholder(seed: string | number): string {
    return `https://picsum.photos/800/600?random=liste${seed}`;
}
