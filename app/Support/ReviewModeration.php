<?php

namespace App\Support;

/**
 * Basit yorum metni kontrolü (küfür / spam anahtar kelimeleri).
 */
final class ReviewModeration
{
    /**
     * @var list<string>
     */
    private const BLOCKED_SUBSTRINGS = [
        'http://',
        'https://',
        'www.',
        'spam',
        'amk',
        'aq ',
        ' orospu',
        'siktir',
        'piç',
    ];

    public static function commentContainsBlockedLanguage(?string $comment): bool
    {
        if ($comment === null || $comment === '') {
            return false;
        }

        $normalized = mb_strtolower($comment, 'UTF-8');

        foreach (self::BLOCKED_SUBSTRINGS as $needle) {
            if (mb_strpos($normalized, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
