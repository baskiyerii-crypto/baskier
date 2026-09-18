<?php

namespace App\Support;

class Phone
{
    /**
     * Normalize a TR mobile/landline to digits: 90XXXXXXXXXX.
     */
    public static function normalize(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw) ?? '';
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = '90'.substr($digits, 1);
        }
        if (strlen($digits) === 10 && (str_starts_with($digits, '5') || str_starts_with($digits, '2'))) {
            $digits = '90'.$digits;
        }

        return $digits !== '' ? $digits : null;
    }

    /**
     * @return list<string>
     */
    public static function lookupKeys(?string $raw): array
    {
        $normalized = self::normalize($raw);
        if (! $normalized) {
            return [];
        }

        $keys = [$normalized, $raw];
        if (str_starts_with($normalized, '90') && strlen($normalized) >= 12) {
            $national = substr($normalized, 2);
            $keys[] = '0'.$national;
            $keys[] = $national;
            $keys[] = '+'.$normalized;
        }

        return array_values(array_unique(array_filter($keys)));
    }

    public static function syntheticEmail(string $normalizedPhone): string
    {
        return $normalizedPhone.'@saha.invalid';
    }
}
