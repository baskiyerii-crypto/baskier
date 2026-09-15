import createNextIntlPlugin from 'next-intl/plugin';
import type { NextConfig } from 'next';

const withNextIntl = createNextIntlPlugin('./i18n/request.ts');

const nextConfig: NextConfig = {
    // Coolify/Docker icin kucuk runtime imaji (Dockerfile runner asamasinda kullanilir).
    output: 'standalone',
    // Urun gorselleri Laravel'in /storage dizininden geliyor; optimize etmeden
    // dogrudan <img> ile servis ediyoruz (Blade tarafi ile ayni davranis).
    poweredByHeader: false,
    // Repo kokUnde Laravel'in de package-lock.json'i var; kok tahminini sabitliyoruz.
    outputFileTracingRoot: __dirname,
};

export default withNextIntl(nextConfig);
