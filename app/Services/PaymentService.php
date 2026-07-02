<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService
{
    /**
     * Demo / placeholder: marks order as financially cleared and records a payment row.
     */
    public function recordDemoPayment(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'provider' => 'demo',
            'reference' => 'DEMO-'.$order->order_number,
            'amount' => $order->subtotal,
            'status' => 'completed',
            'meta' => ['note' => 'Replace with real PSP webhook verification.'],
        ]);
    }
}
