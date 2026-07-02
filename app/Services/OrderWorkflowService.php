<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderWorkflowService
{
    /**
     * @return array<string, list<string>>
     */
    public function allowedTransitions(): array
    {
        return [
            OrderStatus::PENDING => [OrderStatus::CONFIRMED, OrderStatus::CANCELLED],
            OrderStatus::CONFIRMED => [
                OrderStatus::DESIGN_REVIEW,
                OrderStatus::IN_PRODUCTION,
                OrderStatus::CANCELLED,
                OrderStatus::DISPUTED,
            ],
            OrderStatus::DESIGN_REVIEW => [
                OrderStatus::IN_PRODUCTION,
                OrderStatus::DISPUTED,
            ],
            OrderStatus::IN_PRODUCTION => [
                OrderStatus::DESIGN_REVIEW,
                OrderStatus::READY_TO_SHIP,
                OrderStatus::DISPUTED,
            ],
            OrderStatus::READY_TO_SHIP => [OrderStatus::SHIPPED, OrderStatus::DISPUTED],
            OrderStatus::SHIPPED => [OrderStatus::DELIVERED, OrderStatus::DISPUTED],
            OrderStatus::DELIVERED => [OrderStatus::COMPLETED, OrderStatus::DISPUTED],
            OrderStatus::COMPLETED => [],
            OrderStatus::CANCELLED => [],
            OrderStatus::DISPUTED => [OrderStatus::IN_PRODUCTION, OrderStatus::CANCELLED, OrderStatus::COMPLETED],
        ];
    }

    public function canTransition(Order $order, string $to): bool
    {
        $from = $order->status;
        $map = $this->allowedTransitions();

        return in_array($to, $map[$from] ?? [], true);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function transition(Order $order, string $to, User $actor): void
    {
        if (! $this->canTransition($order, $to)) {
            throw new \InvalidArgumentException('Geçersiz sipariş durumu geçişi: '.$order->status.' → '.$to);
        }

        $updates = ['status' => $to];
        if ($to === OrderStatus::SHIPPED) {
            $updates['delivered_at'] = null;
        }
        if ($to === OrderStatus::DELIVERED) {
            $updates['delivered_at'] = now();
        }

        $order->update($updates);
    }
}
