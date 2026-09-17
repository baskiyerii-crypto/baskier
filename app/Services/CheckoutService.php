<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Domain\PaymentStatus;
use App\Exceptions\PaymentNotConfiguredException;
use App\Models\Address;
use App\Models\BillingProfile;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private MarketplaceOrderService $orderService,
        private PaymentService $paymentService
    ) {}

    /**
     * Centralized checkout entrypoint for Web, API, and Quick-Buy.
     *
     * @param array{
     *     shipping_address_id: int,
     *     billing_address_id?: ?int,
     *     use_shipping_for_billing?: bool,
     *     payment_method: string,
     *     invoice_type: string,
     *     invoice_full_name: string,
     *     invoice_email: string,
     *     invoice_phone: string,
     *     invoice_identity_number?: ?string,
     *     invoice_company_name?: ?string,
     *     invoice_tax_number?: ?string,
     *     invoice_tax_office?: ?string,
     *     idempotency_key?: ?string,
     *     custom_items?: ?list<array{product_id: int, variant_id: ?int, quantity: int}>
     * } $data
     * @return array{
     *     success: bool,
     *     orders?: list<Order>,
     *     attempt?: PaymentAttempt,
     *     checkout_form_content?: ?string,
     *     payment_page_url?: ?string,
     *     requires_action?: bool,
     *     error?: string,
     *     correlation_id?: string
     * }
     */
    public function processCheckout(User $user, array $data): array
    {
        $correlationId = request()->header('X-Correlation-ID') ?: Str::uuid()->toString();
        $idempotencyKey = $data['idempotency_key'] ?? Str::uuid()->toString();

        Log::info('checkout_started', [
            'correlation_id' => $correlationId,
            'user_id' => $user->id,
            'payment_method' => $data['payment_method'],
            'idempotency_key' => $idempotencyKey,
            'is_custom_items' => ! empty($data['custom_items']),
        ]);

        // 1. Idempotency Check: if active/paid attempt exists with same key, return early
        $existingAttempt = PaymentAttempt::where('idempotency_key', $idempotencyKey)
            ->where('user_id', $user->id)
            ->first();

        if ($existingAttempt) {
            if ($existingAttempt->status === PaymentStatus::PAID) {
                $orders = Order::whereIn('id', (array) $existingAttempt->order_ids)->get()->all();

                return [
                    'success' => true,
                    'orders' => $orders,
                    'attempt' => $existingAttempt,
                    'requires_action' => false,
                    'correlation_id' => $correlationId,
                ];
            }

            if ($existingAttempt->status === PaymentStatus::PROCESSING && $existingAttempt->expires_at > now()) {
                $meta = (array) $existingAttempt->metadata;

                return [
                    'success' => true,
                    'attempt' => $existingAttempt,
                    'checkout_form_content' => $meta['checkoutFormContent'] ?? null,
                    'payment_page_url' => $meta['paymentPageUrl'] ?? null,
                    'requires_action' => true,
                    'correlation_id' => $correlationId,
                ];
            }
        }

        // 2. Validate addresses
        $shippingAddress = Address::where('id', $data['shipping_address_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $shippingAddress) {
            return [
                'success' => false,
                'error' => 'Geçerli bir teslimat adresi seçilmelidir.',
                'correlation_id' => $correlationId,
            ];
        }

        $useShippingForBilling = (bool) ($data['use_shipping_for_billing'] ?? false);
        $billingAddress = $shippingAddress;
        if (! $useShippingForBilling && ! empty($data['billing_address_id'])) {
            $billingAddress = Address::where('id', $data['billing_address_id'])
                ->where('user_id', $user->id)
                ->first() ?? $shippingAddress;
        }

        // 3. Save billing profile
        $invoiceData = [
            'invoice_type' => $data['invoice_type'],
            'invoice_full_name' => $data['invoice_full_name'],
            'invoice_email' => $data['invoice_email'],
            'invoice_phone' => $data['invoice_phone'],
            'invoice_identity_number' => $data['invoice_identity_number'] ?? null,
            'invoice_company_name' => $data['invoice_company_name'] ?? null,
            'invoice_tax_number' => $data['invoice_tax_number'] ?? null,
            'invoice_tax_office' => $data['invoice_tax_office'] ?? null,
        ];

        BillingProfile::query()->updateOrCreate(
            ['user_id' => $user->id, 'invoice_type' => $invoiceData['invoice_type']],
            $invoiceData
        );

        $paymentMethod = $data['payment_method'];
        $customItems = $data['custom_items'] ?? null;

        try {
            // 4. Prepare orders in pending_payment status with stock reservation
            $prepared = $this->orderService->prepareCheckoutOrders(
                $user,
                $shippingAddress,
                $billingAddress,
                $invoiceData,
                $paymentMethod,
                $idempotencyKey,
                $customItems
            );

            /** @var list<Order> $orders */
            $orders = $prepared['orders'];
            $totalAmount = $prepared['total'];
            $orderIds = array_map(fn (Order $o) => $o->id, $orders);

            // 5. Create PaymentAttempt
            $conversationId = 'BY-CONV-' . date('Ymd') . '-' . Str::random(10);
            $attempt = PaymentAttempt::create([
                'conversation_id' => $conversationId,
                'idempotency_key' => $idempotencyKey,
                'user_id' => $user->id,
                'provider' => $paymentMethod,
                'status' => PaymentStatus::PENDING,
                'amount' => $totalAmount,
                'currency' => 'TRY',
                'order_ids' => $orderIds,
                'expires_at' => now()->addMinutes(20),
            ]);

            // Link payment attempt to orders
            Order::whereIn('id', $orderIds)->update(['payment_attempt_id' => $attempt->id]);

            // 6. Route Payment Method
            if ($paymentMethod === 'bank_transfer' || $paymentMethod === 'cash_on_delivery') {
                Log::info('checkout_manual_payment_pending', [
                    'correlation_id' => $correlationId,
                    'attempt_id' => $attempt->id,
                    'orders' => $orderIds,
                    'method' => $paymentMethod,
                ]);

                return [
                    'success' => true,
                    'orders' => $orders,
                    'attempt' => $attempt,
                    'requires_action' => false,
                    'correlation_id' => $correlationId,
                ];
            }

            if ($paymentMethod === 'shopify') {
                return [
                    'success' => false,
                    'error' => 'Shopify ödeme yöntemi şu anda kapalıdır.',
                    'correlation_id' => $correlationId,
                ];
            }

            // Credit card -> iyzico Checkout Form
            if (! $this->paymentService->isIyzicoConfigured()) {
                throw new PaymentNotConfiguredException('Kredi kartı ile ödeme sağlayıcısı yapılandırılmamıştır.');
            }

            $iyzicoResult = $this->paymentService->initializeIyzicoCheckout(
                $attempt,
                $orders,
                $user,
                $shippingAddress,
                $billingAddress
            );

            return [
                'success' => true,
                'orders' => $orders,
                'attempt' => $attempt,
                'checkout_form_content' => $iyzicoResult['checkoutFormContent'] ?? null,
                'payment_page_url' => $iyzicoResult['paymentPageUrl'] ?? null,
                'requires_action' => true,
                'correlation_id' => $correlationId,
            ];

        } catch (PaymentNotConfiguredException $e) {
            Log::warning('checkout_payment_not_configured', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Ödeme sağlayıcısı şu anda aktif değildir. Lütfen havale/EFT yöntemini deneyin veya destek ile iletişime geçin.',
                'correlation_id' => $correlationId,
            ];
        } catch (\InvalidArgumentException $e) {
            Log::info('checkout_validation_failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'correlation_id' => $correlationId,
            ];
        } catch (\Throwable $e) {
            Log::error('checkout_unexpected_error', [
                'correlation_id' => $correlationId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Sipariş işlenirken bir sorun oluştu. Destek kodu: ' . substr($correlationId, 0, 8),
                'correlation_id' => $correlationId,
            ];
        }
    }
}
