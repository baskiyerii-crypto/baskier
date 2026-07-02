import Constants from 'expo-constants';

/** Laravel web kökü (Blade sayfaları). API adresinden türetilir veya extra.webUrl. */
export const WEB_ORIGIN =
    Constants.expoConfig?.extra?.webUrl ||
    process.env.EXPO_PUBLIC_WEB_URL ||
    String(Constants.expoConfig?.extra?.apiUrl || process.env.EXPO_PUBLIC_API_URL || '')
        .replace(/\/api\/v1\/?$/i, '')
        .replace(/\/$/, '') ||
    'http://127.0.0.1:8000';

export function webUrl(path) {
    const p = path.startsWith('/') ? path : `/${path}`;
    return `${WEB_ORIGIN}${p}`;
}
