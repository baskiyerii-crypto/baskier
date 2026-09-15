import type { MetadataRoute } from 'next';

import { SITE_URL } from '@/lib/api/config';

export default function robots(): MetadataRoute.Robots {
    return {
        rules: [
            {
                userAgent: '*',
                allow: '/',
                // BFF ucu indekslenmez.
                disallow: '/api/',
            },
        ],
        sitemap: `${SITE_URL}/sitemap.xml`,
    };
}
