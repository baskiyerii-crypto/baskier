<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionWhatsAppService
{
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

    public function sendText(string $phone, string $message): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $number = preg_replace('/\D+/', '', $phone) ?? '';
        if ($number === '') {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey(),
                'Content-Type' => 'application/json',
            ])->timeout(20)->post($this->baseUrl().'/message/sendText/'.$this->instance(), [
                'number' => $number,
                'text' => $message,
            ]);

            if (! $response->successful()) {
                Log::warning('Evolution send failed', ['status' => $response->status(), 'body' => $response->body()]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Evolution exception', ['message' => $e->getMessage()]);

            return false;
        }
    }

    public function sendOtp(string $phone, string $code): bool
    {
        return $this->sendText($phone, __('panel.otp_whatsapp_message', ['code' => $code]));
    }
}
