<?php

namespace App\Domain;

final class TrustLevel
{
    public const LEVEL_0 = 0;
    public const LEVEL_1 = 1;
    public const LEVEL_2 = 2;
    public const LEVEL_3 = 3;

    /**
     * @return array<int, string>
     */
    public static function labels(): array
    {
        return [
            self::LEVEL_0 => 'Doğrulanmamış',
            self::LEVEL_1 => 'İletişim Doğrulandı (1 Tik)',
            self::LEVEL_2 => 'Kimlik & Evrak Onaylı (2 Tik)',
            self::LEVEL_3 => 'Üstün Performans & Güvenilir Satıcı (3 Tik)',
        ];
    }

    public static function label(int $level): string
    {
        return self::labels()[$level] ?? 'Bilinmiyor';
    }

    public static function badgeName(int $level): string
    {
        return match ($level) {
            self::LEVEL_1 => '1 Tik',
            self::LEVEL_2 => '2 Tik',
            self::LEVEL_3 => '3 Tik',
            default => 'Doğrulanmamış',
        };
    }

    public static function description(int $level): string
    {
        return match ($level) {
            self::LEVEL_0 => 'E-posta ve telefon doğrulaması bekleniyor.',
            self::LEVEL_1 => 'E-posta ve telefon numarası başarıyla doğrulandı.',
            self::LEVEL_2 => 'Kimlik, şirket ve mesleki yeterlilik belgeleri incelenip onaylandı.',
            self::LEVEL_3 => 'Yüksek müşteri memnuniyeti, başarılı teslimat ve kusursuz sipariş performansı.',
            default => '',
        };
    }
}
