<?php

namespace App\Notifications;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class QuoteOfferReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Quote $quote) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Yeni teklif',
            'body' => 'Mağazanızdan bir teklif aldınız.',
            'quote_id' => $this->quote->id,
            'quote_request_id' => $this->quote->quote_request_id,
        ];
    }
}
