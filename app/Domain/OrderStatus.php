<?php

namespace App\Domain;

final class OrderStatus
{
    public const PENDING = 'pending';

    public const CONFIRMED = 'confirmed';

    public const DESIGN_REVIEW = 'design_review';

    public const IN_PRODUCTION = 'in_production';

    public const READY_TO_SHIP = 'ready_to_ship';

    public const SHIPPED = 'shipped';

    public const DELIVERED = 'delivered';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    public const DISPUTED = 'disputed';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::DESIGN_REVIEW,
            self::IN_PRODUCTION,
            self::READY_TO_SHIP,
            self::SHIPPED,
            self::DELIVERED,
            self::COMPLETED,
            self::CANCELLED,
            self::DISPUTED,
        ];
    }
}
