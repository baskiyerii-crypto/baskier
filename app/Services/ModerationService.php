<?php

namespace App\Services;

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
            'url' => '/https?:\/\/|www\./i',
            'phone' => '/\b(?:\+?90|0)?\s*5\d{2}\s*\d{3}\s*\d{2}\s*\d{2}\b|\b\d{10,}\b/',
            'email' => '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i',
            'whatsapp' => '/whatsapp|wa\.me|wp\.me/i',
            'telegram' => '/telegram\.me|t\.me\//i',
            'instagram' => '/instagram\.com|@[A-Za-z0-9._]{3,}/i',
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
        }

        return [
            'display' => $display,
            'blocked' => $blocked,
            'flags' => $flags,
        ];
    }

    public function isHardBlocked(string $body): bool
    {
        $r = $this->moderateMessage($body);

        return $r['blocked'];
    }
}
