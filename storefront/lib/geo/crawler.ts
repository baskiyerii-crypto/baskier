const CRAWLER_PATTERN =
    /bot|crawl|spider|slurp|mediapartners|facebookexternalhit|embedly|quora link preview|showyoubot|outbrain|pinterest|vkshare|w3c_validator|whatsapp|telegrambot|lighthouse/i;

/**
 * Crawler'a IP ile dil yonlendirmesi yapilmaz; istenen URL neyse o sunulur.
 * Aksi halde cloaking ve yanlis index olusur.
 */
export function isCrawler(userAgent: string | null): boolean {
    if (!userAgent) {
        return false;
    }

    return CRAWLER_PATTERN.test(userAgent);
}
