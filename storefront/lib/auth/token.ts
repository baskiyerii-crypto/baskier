import { cookies } from 'next/headers';

import { TOKEN_COOKIE } from '@/lib/api/config';

/** Sanctum token'i httpOnly cookie'de duruyor; tarayiciya asla sizmaz. */
export async function getToken(): Promise<string | null> {
    const store = await cookies();

    return store.get(TOKEN_COOKIE)?.value ?? null;
}

export async function isAuthenticated(): Promise<boolean> {
    return (await getToken()) !== null;
}
