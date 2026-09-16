<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BasitKargoService
{
    public function isConfigured(): bool
    {
        return Setting::apiEnabled('basitkargo')
            && filled(Setting::get('basitkargo_api_key'))
            && filled(Setting::get('basitkargo_base_url'));
    }

    public function carriers(): array
    {
        if (! $this->isConfigured()) {
            return [
                ['code' => 'yurtici', 'name' => 'Yurtiçi Kargo'],
                ['code' => 'aras', 'name' => 'Aras Kargo'],
                ['code' => 'mng', 'name' => 'MNG Kargo'],
            ];
        }

        try {
            $res = Http::withToken(Setting::get('basitkargo_api_key'))
                ->timeout(20)
                ->get(rtrim(Setting::get('basitkargo_base_url'), '/').'/carriers');

            return $res->json('data') ?? $res->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('basitkargo_carriers_failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function createShipment(Order $order, ?string $carrierCode = null): Order
    {
        $tracking = 'BK-'.strtoupper(Str::random(10));
        $labelPath = 'shipping-labels/'.$order->id.'-'.$tracking.'.txt';
        Storage::disk('public')->put($labelPath, "Basit Kargo Etiket\nSiparis: {$order->order_number}\nTakip: {$tracking}\n");

        if ($this->isConfigured()) {
            try {
                $res = Http::withToken(Setting::get('basitkargo_api_key'))
                    ->timeout(30)
                    ->post(rtrim(Setting::get('basitkargo_base_url'), '/').'/shipments', [
                        'order_number' => $order->order_number,
                        'carrier' => $carrierCode,
                        'address' => $order->shipping_address,
                        'amount' => $order->subtotal,
                    ]);
                $data = $res->json() ?? [];
                $tracking = $data['tracking_number'] ?? $tracking;
                if (! empty($data['label_url'])) {
                    $labelPath = $data['label_url'];
                }
            } catch (\Throwable $e) {
                Log::warning('basitkargo_create_failed', ['order' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        $order->update([
            'tracking_number' => $tracking,
            'shipping_label_path' => $labelPath,
            'carrier_code' => $carrierCode,
        ]);

        return $order->fresh();
    }
}
