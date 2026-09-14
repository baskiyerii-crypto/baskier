import { NextResponse, type NextRequest } from 'next/server';

import { API_URL } from '@/lib/api/config';
import { getToken } from '@/lib/auth/token';

/**
 * BFF: tarayici Laravel'e dogrudan gitmez, buradan gecer.
 * Bearer token httpOnly cookie'den okunur, boylece CORS ve
 * SANCTUM_STATEFUL_DOMAINS ayari gerekmez.
 */
async function forward(request: NextRequest, path: string[]): Promise<NextResponse> {
    const target = new URL(`${API_URL}/${path.map(encodeURIComponent).join('/')}`);
    target.search = request.nextUrl.search;

    const token = await getToken();
    const headers = new Headers({ Accept: 'application/json' });

    const contentType = request.headers.get('content-type');
    if (contentType) {
        headers.set('Content-Type', contentType);
    }
    if (token) {
        headers.set('Authorization', `Bearer ${token}`);
    }

    const hasBody = !['GET', 'HEAD'].includes(request.method);

    const response = await fetch(target, {
        method: request.method,
        headers,
        body: hasBody ? await request.arrayBuffer() : undefined,
        cache: 'no-store',
    });

    const body = await response.arrayBuffer();

    return new NextResponse(body, {
        status: response.status,
        headers: {
            'Content-Type': response.headers.get('content-type') ?? 'application/json',
            'Cache-Control': 'no-store',
        },
    });
}

type RouteContext = { params: Promise<{ path: string[] }> };

export async function GET(request: NextRequest, context: RouteContext) {
    return forward(request, (await context.params).path);
}

export async function POST(request: NextRequest, context: RouteContext) {
    return forward(request, (await context.params).path);
}

export async function PUT(request: NextRequest, context: RouteContext) {
    return forward(request, (await context.params).path);
}

export async function PATCH(request: NextRequest, context: RouteContext) {
    return forward(request, (await context.params).path);
}

export async function DELETE(request: NextRequest, context: RouteContext) {
    return forward(request, (await context.params).path);
}
