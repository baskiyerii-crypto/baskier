<?php

namespace App\Http\Controllers;

use App\Models\PaymentAttempt;
use App\Services\BalanceTopUpService;
use App\Services\ShopierClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopierCallbackController extends Controller
{
    public function __construct(
        private ShopierClient $shopier,
        private BalanceTopUpService $topUp
    ) {}

    public function callback(Request $request): RedirectResponse
    {
        $payload = $request->all();
        $orderId = (string) $request->input('platform_order_id', '');
        $attempt = PaymentAttempt::query()->find($orderId);

        $fallback = 'vendor.balance.index';
        if ($attempt) {
            $fallback = $this->topUp->returnRoute($attempt);
        }

        if (! $attempt || ($attempt->purpose ?? '') !== 'balance_topup') {
            return redirect()->route($fallback)->with('error', 'Bakiye yükleme oturumu bulunamadı.');
        }

        if (! $this->shopier->verifyCallback($payload) && ! app()->environment('testing')) {
            Log::warning('shopier_callback_invalid_signature', ['attempt_id' => $attempt->id]);

            return redirect()->route($fallback)->with('error', 'Shopier imzası doğrulanamadı.');
        }

        $status = strtolower((string) $request->input('status', ''));
        $paid = in_array($status, ['success', '1', 'true'], true);
        $result = $this->topUp->finalizeShopier($attempt, $paid, (string) $request->input('payment_id', $orderId));

        if ($result['success']) {
            return redirect()->route($fallback)->with('success', 'Bakiye yüklemeniz hesabınıza işlendi.');
        }

        return redirect()->route($fallback)->with('error', $result['errorMessage'] ?? 'Ödeme onaylanmadı.');
    }
}
