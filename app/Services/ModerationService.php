<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

final class ModerationService
{
    /**
     * @return array{display: string, blocked: bool, flags: list<string>}
     */
    public function moderateMessage(string $body): array
    {
        $flags = [];
        $display = $body;

        $checks = [
            'url' => '/https?:\/\/|www\.|\b[a-z0-9.-]+\.(com|net|org|io|co|tr|shop)\b/i',
            'phone' => '/\b(?:\+?90|0)?\s*5\d{2}\s*\d{3}\s*\d{2}\s*\d{2}\b|\b0?\d{10,}\b/',
            'email' => '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i',
            'whatsapp' => '/whatsapp|wa\.me|wp\.me|watsap|whats\s*app/i',
            'telegram' => '/telegram\.me|t\.me\/|telegram/i',
            'instagram' => '/instagram\.com|insta\b|@[A-Za-z0-9._]{3,}/i',
            'facebook' => '/facebook\.com|fb\.com|facebook/i',
            'tiktok' => '/tiktok\.com|\btiktok\b/i',
            'off_platform' => '/sitemiz|kendi\s*sitem|internetten\s*yaz|dm\s*at|bizi\s*ara|dışarıdan\s*iletişim|platform\s*dışı|başka\s*platform/i',
        ];

        foreach ($checks as $flag => $regex) {
            if (preg_match($regex, $body)) {
                $flags[] = $flag;
            }
        }

        $blocked = $flags !== [];
        if ($blocked) {
            $display = preg_replace($checks['phone'], '[telefon gizlendi]', $display) ?? $display;
            $display = preg_replace($checks['email'], '[e-posta gizlendi]', $display) ?? $display;
            $display = preg_replace($checks['url'], '[link gizlendi]', $display) ?? $display;
            $display = preg_replace($checks['whatsapp'], '[whatsapp gizlendi]', $display) ?? $display;
            $display = preg_replace($checks['telegram'], '[telegram gizlendi]', $display) ?? $display;
            $display = preg_replace($checks['instagram'], '[sosyal gizlendi]', $display) ?? $display;
            $display = preg_replace($checks['facebook'], '[sosyal gizlendi]', $display) ?? $display;
            $display = preg_replace($checks['tiktok'], '[sosyal gizlendi]', $display) ?? $display;

            Log::info('message_moderation_blocked', [
                'flags' => $flags,
            ]);
        }

        return [
            'display' => $display,
            'blocked' => $blocked,
            'flags' => $flags,
        ];
    }

    public function isHardBlocked(string $body): bool
    {
        return $this->moderateMessage($body)['blocked'];
    }

    public function rejectionMessage(): string
    {
        return 'Mesajda telefon, e-posta, web sitesi, sosyal medya veya platform dışı yönlendirme kullanılamaz. İletişim yalnızca BaskıYeri üzerinden yapılmalıdır.';
    }
}
