<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionWhatsAppService
{
    public const STATUS_NOT_CONFIGURED = 'not_configured';
    public const STATUS_RETRYABLE = 'retryable';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUCCESS = 'success';

    public function baseUrl(): string
    {
        return rtrim((string) (Setting::get('evolution_base_url') ?: config('evolution.base_url')), '/');
    }

    public function apiKey(): string
    {
        return (string) (Setting::get('evolution_api_key') ?: config('evolution.api_key'));
    }

    public function instance(): string
    {
        return (string) (Setting::get('evolution_instance') ?: config('evolution.instance'));
    }

    public function isConfigured(): bool
    {
        return Setting::apiEnabled('evolution')
            && $this->baseUrl() !== ''
            && $this->apiKey() !== ''
            && $this->instance() !== '';
    }

    /**
     * @return array{status: string, message?: string, error?: string}
     */
    public function sendTextMessage(string $phone, string $message): array
    {
        if (! $this->isConfigured()) {
            return [
                'status' => self::STATUS_NOT_CONFIGURED,
                'message' => 'Evolution WhatsApp entegrasyonu yapılandırılmamış.',
            ];
        }

        $number = preg_replace('/\D+/', '', $phone) ?? '';
        if ($number === '') {
            return [
                'status' => self::STATUS_FAILED,
                'error' => 'Geçersiz telefon numarası.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey(),
                'Content-Type' => 'application/json',
            ])->timeout(15)->post($this->baseUrl().'/message/sendText/'.$this->instance(), [
                'number' => $number,
                'text' => $message,
            ]);

            if ($response->successful()) {
                return [
                    'status' => self::STATUS_SUCCESS,
                ];
            }

            if ($response->serverError()) {
                Log::warning('Evolution send server error', ['status' => $response->status(), 'body' => $response->body()]);
                return [
                    'status' => self::STATUS_RETRYABLE,
                    'error' => 'WhatsApp sunucu hatası: '.$response->status(),
                ];
            }

            Log::error('Evolution send client error', ['status' => $response->status(), 'body' => $response->body()]);
            return [
                'status' => self::STATUS_FAILED,
                'error' => 'WhatsApp isteği reddedildi: '.$response->status(),
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Evolution timeout', ['message' => $e->getMessage()]);
            return [
                'status' => self::STATUS_RETRYABLE,
                'error' => 'WhatsApp bağlantı zaman aşımı: '.$e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('Evolution exception', ['message' => $e->getMessage()]);
            return [
                'status' => self::STATUS_FAILED,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendText(string $phone, string $message): bool
    {
        $result = $this->sendTextMessage($phone, $message);

        return $result['status'] === self::STATUS_SUCCESS;
    }

    public function sendOtp(string $phone, string $code): bool
    {
        return $this->sendText($phone, __('panel.otp_whatsapp_message', ['code' => $code]));
    }
}
