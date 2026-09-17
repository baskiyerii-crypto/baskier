<?php

namespace App\Domain;

final class PaymentStatus
{
    public const PENDING = 'pending';

    public const PROCESSING = 'processing';

    public const PAID = 'paid';

    public const FAILED = 'failed';

    public const EXPIRED = 'expired';

    public const REFUNDED = 'refunded';

    public const PARTIALLY_REFUNDED = 'partially_refunded';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::PROCESSING,
            self::PAID,
            self::FAILED,
            self::EXPIRED,
            self::REFUNDED,
            self::PARTIALLY_REFUNDED,
        ];
    }
}
