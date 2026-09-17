<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PlatformNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public array $meta = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'meta' => $this->meta,
        ];
    }
}
