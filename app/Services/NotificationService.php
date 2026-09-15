<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notify(User $user, string $title, string $body, array $meta = []): void
    {
        // Web push subscriptions table hazır; VAPID anahtarları eklendiğinde gerçek push gönderilir.
        Log::info('notification', [
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'meta' => $meta,
        ]);
    }

    public function orderStatusChanged(User $user, string $orderNumber, string $status): void
    {
        $this->notify($user, 'Sipariş güncellendi', "#{$orderNumber} durumu: {$status}", [
            'type' => 'order_status',
            'order_number' => $orderNumber,
            'status' => $status,
        ]);
    }
}
