<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentService
{
    public function humanize(string $text): string
    {
        $apiKey = Setting::get('openai_api_key');
        if (! $apiKey) {
            return $this->localHumanize($text);
        }

        try {
            $model = Setting::get('openai_model', 'gpt-4o-mini');
            $res = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Türkçe SEO blog metnini doğal, insan yazımı gibi yeniden yaz. Anahtar kelimeleri koru, abartılı AI tonunu kaldır.'],
                        ['role' => 'user', 'content' => $text],
                    ],
                    'temperature' => 0.7,
                ]);

            return trim((string) data_get($res->json(), 'choices.0.message.content', $text));
        } catch (\Throwable $e) {
            Log::warning('ai_humanize_failed', ['error' => $e->getMessage()]);

            return $this->localHumanize($text);
        }
    }

    private function localHumanize(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', trim($text)) ?: '';
        $text = str_ireplace(['sonuç olarak', 'bu bağlamda', 'özetle'], ['kısaca', 'pratikte', 'netleştirelim'], $text);

        return $text;
    }
}
