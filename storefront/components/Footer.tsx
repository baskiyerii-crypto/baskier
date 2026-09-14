import { useTranslations } from 'next-intl';

import { Link } from '@/i18n/navigation';
import { legacyUrl } from '@/lib/urls';

export default function Footer() {
    const t = useTranslations('footer');
    const nav = useTranslations('nav');
    const home = useTranslations('home');

    return (
        <footer className="mt-16 border-t border-slate-200/70 bg-white/60 backdrop-blur">
            <div className="by-container py-12">
                <div className="by-card by-gradient-border mb-10 p-6 md:p-8">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p className="by-section-title text-slate-500">{home('vendorCtaEyebrow')}</p>
                            <p className="mt-2 text-xl font-extrabold tracking-tight text-slate-900">
                                {home('vendorCtaTitle')}
                            </p>
                            <p className="mt-2 text-sm text-slate-600">{home('vendorCtaBody')}</p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <a href={legacyUrl('/kayit?role=vendor')} className="by-btn-cta px-7 py-3 text-base">
                                {home('vendorCtaPrimary')}
                            </a>
                            <Link href="/saticilar" className="by-btn-secondary px-7 py-3 text-base">
                                {home('vendorCtaSecondary')}
                            </Link>
                        </div>
                    </div>
                </div>

                <div className="grid gap-8 md:grid-cols-4">
                    <div className="md:col-span-2">
                        <div className="flex items-center gap-2 text-base font-extrabold tracking-tight text-slate-900">
                            <span className="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-sm">
                                B
                            </span>
                            <span>{nav('brand')}</span>
                        </div>
                        <p className="mt-3 max-w-md text-sm leading-relaxed text-slate-600">{t('tagline')}</p>
                    </div>

                    <div>
                        <p className="by-section-title text-slate-500">{t('discover')}</p>
                        <div className="mt-3 space-y-2 text-sm">
                            <Link href="/urunler" className="block text-slate-700 hover:text-slate-900">
                                {nav('products')}
                            </Link>
                            <Link href="/saticilar" className="block text-slate-700 hover:text-slate-900">
                                {nav('vendors')}
                            </Link>
                            <a href={legacyUrl('/is-ilanlari')} className="block text-slate-700 hover:text-slate-900">
                                {nav('jobs')}
                            </a>
                        </div>
                    </div>

                    <div>
                        <p className="by-section-title text-slate-500">{t('corporate')}</p>
                        {/* Kurumsal sayfalar bu fazda Blade'de kaliyor. */}
                        <div className="mt-3 space-y-2 text-sm">
                            <a href={legacyUrl('/hakkimizda')} className="block text-slate-700 hover:text-slate-900">
                                {t('about')}
                            </a>
                            <a href={legacyUrl('/kullanim-kosullari')} className="block text-slate-700 hover:text-slate-900">
                                {t('terms')}
                            </a>
                            <a href={legacyUrl('/gizlilik')} className="block text-slate-700 hover:text-slate-900">
                                {t('privacy')}
                            </a>
                        </div>
                    </div>
                </div>

                <div className="mt-10 flex flex-col gap-2 border-t border-slate-200 pt-6 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                    <p>{t('rights', { year: new Date().getFullYear() })}</p>
                    <p>{t('country')}</p>
                </div>
            </div>
        </footer>
    );
}
