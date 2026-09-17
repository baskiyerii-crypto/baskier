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
    public const STATUS_NOT_CONFIGURED = 'not_configured';
    public const STATUS_RETRYABLE = 'retryable';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUCCESS = 'success';

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

    /**
     * @return array{status: string, tracking_number?: ?string, label_url?: ?string, error?: ?string, message?: ?string}
     */
    public function createShipment(Order $order, ?string $carrierCode = null): array
    {
        if (! $this->isConfigured()) {
            return [
                'status' => self::STATUS_NOT_CONFIGURED,
                'message' => 'Basit Kargo entegrasyonu yapılandırılmamış.',
                'tracking_number' => null,
                'label_url' => null,
            ];
        }

        try {
            $res = Http::withToken(Setting::get('basitkargo_api_key'))
                ->timeout(20)
                ->post(rtrim(Setting::get('basitkargo_base_url'), '/').'/shipments', [
                    'order_number' => $order->order_number,
                    'carrier' => $carrierCode,
                    'address' => $order->shipping_address,
                    'amount' => $order->subtotal,
                ]);

            if ($res->successful()) {
                $data = $res->json() ?? [];
                return [
                    'status' => self::STATUS_SUCCESS,
                    'tracking_number' => $data['tracking_number'] ?? null,
                    'label_url' => $data['label_url'] ?? null,
                ];
            }

            if ($res->serverError()) {
                Log::warning('basitkargo_create_server_error', ['order' => $order->id, 'status' => $res->status()]);
                return [
                    'status' => self::STATUS_RETRYABLE,
                    'error' => 'Kargo sağlayıcı sunucu hatası: '.$res->status(),
                ];
            }

            Log::error('basitkargo_create_client_error', ['order' => $order->id, 'status' => $res->status(), 'body' => $res->body()]);
            return [
                'status' => self::STATUS_FAILED,
                'error' => 'Kargo sağlayıcı isteği reddetti: '.$res->status(),
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('basitkargo_create_timeout', ['order' => $order->id, 'error' => $e->getMessage()]);
            return [
                'status' => self::STATUS_RETRYABLE,
                'error' => 'Kargo sağlayıcı bağlantı zaman aşımı: '.$e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('basitkargo_create_failed', ['order' => $order->id, 'error' => $e->getMessage()]);
            return [
                'status' => self::STATUS_FAILED,
                'error' => $e->getMessage(),
            ];
        }
    }
}
