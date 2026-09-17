<?php

namespace App\Jobs;

use App\Services\EvolutionWhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public string $phone,
        public string $message
    ) {}

    public function handle(EvolutionWhatsAppService $service): void
    {
        $result = $service->sendTextMessage($this->phone, $this->message);

        if ($result['status'] === EvolutionWhatsAppService::STATUS_NOT_CONFIGURED) {
            Log::info('WhatsApp not configured, skipping message job.', [
                'phone' => $this->phone,
            ]);

            return;
        }

        if ($result['status'] === EvolutionWhatsAppService::STATUS_RETRYABLE) {
            Log::warning('WhatsApp temporary failure, will retry.', [
                'phone' => $this->phone,
                'error' => $result['error'] ?? null,
            ]);

            throw new \RuntimeException('WhatsApp retryable error: '.($result['error'] ?? 'timeout'));
        }

        if ($result['status'] === EvolutionWhatsAppService::STATUS_FAILED) {
            Log::error('WhatsApp permanent failure.', [
                'phone' => $this->phone,
                'error' => $result['error'] ?? null,
            ]);

            return;
        }

        Log::info('WhatsApp message sent successfully.', [
            'phone' => $this->phone,
        ]);
    }
}
