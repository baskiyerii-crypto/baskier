<?php

namespace App\Notifications;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuoteRequestMatchedNotification extends Notification
{
    use Queueable;

    public function __construct(public QuoteRequest $quoteRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Yeni teklif talebi eşleşmesi',
            'body' => $this->quoteRequest->title,
            'quote_request_id' => $this->quoteRequest->id,
        ];
    }
}
