<?php

namespace App\Http\Controllers;

use App\Services\IyzicoClient;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IyzicoCallbackController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private IyzicoClient $iyzicoClient
    ) {}

    /**
     * Customer returns from 3DS / Hosted Checkout Form.
     */
    public function callback(Request $request): RedirectResponse
    {
        $token = $request->input('token');
        if (empty($token)) {
            return redirect()->route('cart.index')->with('error', 'Ödeme oturumu bulunamadı.');
        }

        try {
            $result = $this->paymentService->verifyAndProcessIyzicoCallback($token);

            $attempt = $result['attempt'] ?? null;
            if ($attempt && ($attempt->purpose ?? '') === 'balance_topup') {
                $route = app(\App\Services\BalanceTopUpService::class)->returnRoute($attempt);
                if ($result['success']) {
                    return redirect()->route($route)->with('success', 'Bakiye yüklemeniz hesabınıza işlendi.');
                }

                return redirect()->route($route)->with('error', 'Bakiye yükleme onaylanmadı: '.($result['errorMessage'] ?? 'İşlem reddedildi.'));
            }

            if ($result['success']) {
                $count = count($result['orders'] ?? []);
                $firstOrder = $result['orders'][0]->order_number ?? '';
                $msg = $count > 1
                    ? "Ödemeniz başarıyla alındı. {$count} adet siparişiniz oluşturuldu (#{$firstOrder})."
                    : "Ödemeniz başarıyla alındı. Sipariş no: #{$firstOrder}";

                return redirect()->route('account.orders.index')->with('success', $msg);
            }

            return redirect()->route('cart.index')->with('error', 'Ödeme onaylanmadı: ' . ($result['errorMessage'] ?? 'İşlem reddedildi.'));

        } catch (\Throwable $e) {
            Log::error('iyzico_callback_exception', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('cart.index')->with('error', 'Ödeme sonucu doğrulanırken bir hata oluştu. Destek ekibimizle iletişime geçebilirsiniz.');
        }
    }

    /**
     * Incoming signed webhook from iyzico.
     */
    public function webhook(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $signatureHeader = $request->header('X-IYZ-SIGNATURE-V3');

        if (! $this->iyzicoClient->verifyWebhookSignature($signatureHeader, $rawBody)) {
            Log::warning('iyzico_webhook_invalid_signature', [
                'ip' => $request->ip(),
                'signature' => $signatureHeader,
            ]);

            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        $payload = json_decode($rawBody, true) ?? [];
        $eventId = (string) ($payload['iyziEventId'] ?? $payload['token'] ?? $payload['paymentId'] ?? '');

        if (! empty($eventId)) {
            // Deduplicate event using Cache lock for 24 hours
            $cacheKey = 'iyzico_webhook_event_' . $eventId;
            if (Cache::has($cacheKey)) {
                Log::info('iyzico_webhook_duplicate_ignored', ['event_id' => $eventId]);

                return response()->json(['status' => 'success', 'message' => 'Event already processed']);
            }
            Cache::put($cacheKey, true, now()->addDay());
        }

        $token = $payload['token'] ?? null;
        if ($token) {
            try {
                $this->paymentService->verifyAndProcessIyzicoCallback($token);
            } catch (\Throwable $e) {
                Log::error('iyzico_webhook_processing_error', [
                    'event_id' => $eventId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
