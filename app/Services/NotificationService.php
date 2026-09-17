<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PlatformNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function notify(User $user, string $title, string $body, array $meta = [], ?string $url = null): void
    {
        try {
            $user->notify(new PlatformNotification($title, $body, $url, $meta));
        } catch (\Throwable $e) {
            Log::info('notification', [
                'user_id' => $user->id,
                'title' => $title,
                'body' => $body,
                'meta' => $meta,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function orderStatusChanged(User $user, string $orderNumber, string $status, ?string $url = null): void
    {
        $this->notify($user, 'Sipariş güncellendi', "#{$orderNumber} durumu: {$status}", [
            'type' => 'order_status',
            'order_number' => $orderNumber,
            'status' => $status,
        ], $url);
    }
}
