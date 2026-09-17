<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\BasitKargoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Order $order,
        public ?string $carrierCode = null
    ) {}

    public function handle(BasitKargoService $service): void
    {
        $result = $service->createShipment($this->order, $this->carrierCode);

        if ($result['status'] === BasitKargoService::STATUS_NOT_CONFIGURED) {
            Log::info('BasitKargo not configured, skipping shipment job.', [
                'order_id' => $this->order->id,
            ]);

            return;
        }

        if ($result['status'] === BasitKargoService::STATUS_RETRYABLE) {
            Log::warning('BasitKargo temporary failure, will retry.', [
                'order_id' => $this->order->id,
                'error' => $result['error'] ?? null,
            ]);

            throw new \RuntimeException('BasitKargo retryable error: '.($result['error'] ?? 'timeout'));
        }

        if ($result['status'] === BasitKargoService::STATUS_FAILED) {
            Log::error('BasitKargo permanent failure.', [
                'order_id' => $this->order->id,
                'error' => $result['error'] ?? null,
            ]);

            return;
        }

        if ($result['status'] === BasitKargoService::STATUS_SUCCESS && ! empty($result['tracking_number'])) {
            $carrier = $this->carrierCode ?: 'basitkargo';

            $this->order->update([
                'tracking_number' => $result['tracking_number'],
                'shipping_label_path' => $result['label_url'] ?? null,
                'carrier_code' => $this->carrierCode,
            ]);

            Shipment::updateOrCreate(
                ['order_id' => $this->order->id],
                [
                    'carrier' => $carrier,
                    'tracking_number' => $result['tracking_number'],
                    'shipped_at' => now(),
                ]
            );

            Log::info('BasitKargo shipment created successfully.', [
                'order_id' => $this->order->id,
                'tracking' => $result['tracking_number'],
            ]);
        }
    }
}
