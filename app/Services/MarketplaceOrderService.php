<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Domain\PaymentStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Contract;
use App\Models\Order;
use App\Models\OrderContractAcceptance;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceOrderService
{
    public function __construct(
        private CommissionService $commissions,
        private StockReservationService $stockReservations
    ) {}

    /**
     * Prepare orders from cart or custom items in pending_payment state with temporary stock reservation.
     * Does NOT deduct definitive stock and does NOT clear the user's cart.
     *
     * @param list<array{product_id: int, variant_id: ?int, quantity: int}>|null $customItems
     * @return array{orders: list<Order>, total: string, checkout_token: string}
     */
    public function prepareCheckoutOrders(
        User $user,
        Address $shippingAddress,
        Address $billingAddress,
        array $invoiceData = [],
        string $paymentMethod = 'credit_card',
        ?string $idempotencyKey = null,
        ?array $customItems = null,
        ?string $ipAddress = null
    ): array {
        if ($shippingAddress->user_id !== $user->id || $billingAddress->user_id !== $user->id) {
            throw new \InvalidArgumentException('Seçilen adresler size ait değil.');
        }

        $checkoutToken = Str::uuid()->toString();

        // 1. Resolve checkout items
        $resolvedItems = [];
        if ($customItems !== null) {
            if (empty($customItems)) {
                throw new \InvalidArgumentException('Satın alınacak ürün bulunamadı.');
            }
            foreach ($customItems as $item) {
                $product = Product::with('vendor')->findOrFail((int) $item['product_id']);
                $variant = ! empty($item['variant_id'])
                    ? ProductVariant::where('id', (int) $item['variant_id'])->where('product_id', $product->id)->firstOrFail()
                    : null;
                $quantity = max(1, (int) ($item['quantity'] ?? 1));

                $resolvedItems[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $quantity,
                ];
            }
        } else {
            $cartItems = CartItem::where('user_id', $user->id)->with(['product.vendor', 'variant'])->get();
            if ($cartItems->isEmpty()) {
                throw new \InvalidArgumentException('Sepetiniz boş.');
            }
            foreach ($cartItems as $ci) {
                $resolvedItems[] = [
                    'product' => $ci->product,
                    'variant' => $ci->variant,
                    'quantity' => (int) $ci->quantity,
                ];
            }
        }

        // 2. Reserve stock for all items
        $reservationPayload = array_map(fn ($row) => [
            'product_id' => $row['product']['id'],
            'variant_id' => $row['variant']?->id,
            'quantity' => $row['quantity'],
        ], $resolvedItems);

        $this->stockReservations->reserveStock($reservationPayload, $checkoutToken, $user->id, 20);

        // 3. Group by vendor and create orders in PENDING_PAYMENT status
        $waitDays = Setting::commissionWaitDays();
        $shippingText = $shippingAddress->formatted . ' | ' . $shippingAddress->full_name . ' | ' . $shippingAddress->phone;

        return DB::transaction(function () use (
            $user,
            $resolvedItems,
            $waitDays,
            $shippingText,
            $shippingAddress,
            $billingAddress,
            $invoiceData,
            $paymentMethod,
            $idempotencyKey,
            $checkoutToken,
            $ipAddress
        ) {
            $byVendor = [];
            foreach ($resolvedItems as $item) {
                $vendorId = (int) $item['product']->vendor_id;
                $byVendor[$vendorId][] = $item;
            }

            $orders = [];
            $grandTotal = '0.00';

            $contract = Contract::query()->where('key', 'distance_sales')->where('is_active', true)->first();
            $contractHash = $contract ? hash('sha256', (string) $contract->content) : null;
            $contractVersion = $contract ? (int) $contract->version : 1;

            foreach ($byVendor as $vendorId => $group) {
                $subtotal = '0.00';
                foreach ($group as $entry) {
                    $p = $entry['product'];
                    $v = $entry['variant'];
                    $qty = $entry['quantity'];

                    $basePrice = (string) $p->price;
                    $adj = $v ? (string) ($v->price_adjustment ?? 0) : '0';
                    $unitPrice = bcadd($basePrice, $adj, 2);
                    $line = bcmul($unitPrice, (string) $qty, 2);
                    $subtotal = bcadd($subtotal, $line, 2);
                }

                [$rate, $commissionAmount, $vendorAmount] = $this->commissions->calculate((float) $subtotal, 'product');
                $grandTotal = bcadd($grandTotal, $subtotal, 2);

                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'user_id' => $user->id,
                    'vendor_id' => (int) $vendorId,
                    'type' => 'product',
                    'quote_id' => null,
                    'freelancer_job_id' => null,
                    'status' => OrderStatus::PENDING_PAYMENT,
                    'payment_status' => PaymentStatus::PENDING,
                    'payment_method' => $paymentMethod,
                    'idempotency_key' => $idempotencyKey,
                    'currency' => 'TRY',
                    'subtotal' => $subtotal,
                    'commission_rate' => $rate,
                    'commission_amount' => $commissionAmount,
                    'vendor_amount' => $vendorAmount,
                    'paid_at' => null,
                    'commission_ready_at' => null,
                    'shipping_address' => $shippingText,
                    'shipping_address_id' => $shippingAddress->id,
                    'billing_address_id' => $billingAddress->id,
                    'invoice_type' => $invoiceData['invoice_type'] ?? null,
                    'invoice_full_name' => $invoiceData['invoice_full_name'] ?? null,
                    'invoice_company_name' => $invoiceData['invoice_company_name'] ?? null,
                    'invoice_tax_number' => $invoiceData['invoice_tax_number'] ?? null,
                    'invoice_identity_number' => $invoiceData['invoice_identity_number'] ?? null,
                    'invoice_tax_office' => $invoiceData['invoice_tax_office'] ?? null,
                    'invoice_email' => $invoiceData['invoice_email'] ?? null,
                    'invoice_phone' => $invoiceData['invoice_phone'] ?? null,
                ]);

                foreach ($group as $entry) {
                    $p = $entry['product'];
                    $v = $entry['variant'];
                    $qty = $entry['quantity'];
                    $basePrice = (string) $p->price;
                    $adj = $v ? (string) ($v->price_adjustment ?? 0) : '0';
                    $unitPrice = bcadd($basePrice, $adj, 2);

                    $order->items()->create([
                        'product_id' => $p->id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'variant_id' => $v?->id,
                        'variant_name' => $v?->name,
                        'variant_sku' => $v?->sku,
                        'price' => $unitPrice,
                        'quantity' => $qty,
                        'digital_link' => $p->isDigital() ? $p->digital_link : null,
                    ]);
                }

                if ($contract) {
                    OrderContractAcceptance::create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'contract_id' => $contract->id,
                        'version' => $contractVersion,
                        'contract_hash' => $contractHash,
                        'checkout_token' => $checkoutToken,
                        'ip' => $ipAddress ?: request()->ip(),
                        'scrolled_at' => now(),
                        'accepted_at' => now(),
                    ]);
                }

                $orders[] = $order;
                try {
                    $order->loadMissing('vendor.user');
                    if ($order->vendor?->user) {
                        $order->vendor->user->notify(new \App\Notifications\OrderCreatedNotification($order));
                    }
                    app(\App\Services\NotificationService::class)->notify(
                        $user,
                        'Sipariş alındı',
                        '#'.$order->order_number,
                        ['type' => 'order'],
                        route('account.orders.show', $order)
                    );
                } catch (\Throwable) {
                }
            }

            return [
                'orders' => $orders,
                'total' => $grandTotal,
                'checkout_token' => $checkoutToken,
            ];
        });
    }

    /**
     * Confirm verified payment for an order, commit stock reservation and clear purchased items from cart.
     */
    public function confirmPaidOrder(Order $order, ?PaymentAttempt $paymentAttempt = null, array $meta = []): Order
    {
        return DB::transaction(function () use ($order, $paymentAttempt, $meta) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            if (! $lockedOrder) {
                return $order;
            }

            // If already confirmed, return idempotently
            if ($lockedOrder->payment_status === PaymentStatus::PAID && $lockedOrder->status === OrderStatus::CONFIRMED) {
                return $lockedOrder;
            }

            $waitDays = Setting::commissionWaitDays();

            $lockedOrder->update([
                'status' => OrderStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PAID,
                'paid_at' => now(),
                'commission_ready_at' => now()->addDays($waitDays),
                'payment_attempt_id' => $paymentAttempt?->id,
            ]);

            // Find matching contract acceptance to get checkout_token for committing reservation
            $acceptance = OrderContractAcceptance::where('order_id', $lockedOrder->id)->latest()->first();
            $checkoutToken = $acceptance?->checkout_token;

            if ($checkoutToken) {
                $this->stockReservations->commitReservations($checkoutToken, $lockedOrder);
            } else {
                // Fallback: commit directly per item if token missing
                foreach ($lockedOrder->items as $item) {
                    if ($item->variant_id) {
                        ProductVariant::where('id', $item->variant_id)->decrement('stock', $item->quantity);
                    } elseif ($item->product_id) {
                        $p = Product::find($item->product_id);
                        if ($p && ! $p->isDigital()) {
                            $p->decrement('stock', $item->quantity);
                        }
                    }
                }
            }

            // Record completed Payment entry
            Payment::updateOrCreate(
                [
                    'order_id' => $lockedOrder->id,
                    'reference' => $paymentAttempt?->conversation_id ?? ('ORD-' . $lockedOrder->order_number),
                ],
                [
                    'provider' => $paymentAttempt?->provider ?? $lockedOrder->payment_method ?? 'credit_card',
                    'amount' => $lockedOrder->subtotal,
                    'status' => 'completed',
                    'meta' => $meta,
                ]
            );

            // Selectively clear purchased items from user's cart
            foreach ($lockedOrder->items as $item) {
                CartItem::where('user_id', $lockedOrder->user_id)
                    ->where('product_id', $item->product_id)
                    ->where(function ($query) use ($item) {
                        if ($item->variant_id) {
                            $query->where('variant_id', $item->variant_id);
                        } else {
                            $query->whereNull('variant_id');
                        }
                    })
                    ->delete();
            }

            app(\App\Services\OrderService::class)->setTerminDueAt($lockedOrder);

            return $lockedOrder->fresh(['items', 'vendor', 'user']);
        });
    }

    /**
     * Cancel / Fail an order and release reserved stock.
     */
    public function failOrder(Order $order, string $reason = 'Ödeme başarısız'): void
    {
        DB::transaction(function () use ($order, $reason) {
            $locked = Order::where('id', $order->id)->lockForUpdate()->first();
            if (! $locked) {
                return;
            }

            if ($locked->payment_status === PaymentStatus::PAID) {
                return;
            }

            $locked->update([
                'status' => OrderStatus::CANCELLED,
                'payment_status' => PaymentStatus::FAILED,
                'notes' => trim(($locked->notes ? $locked->notes . ' | ' : '') . $reason),
            ]);

            $acceptance = OrderContractAcceptance::where('order_id', $locked->id)->latest()->first();
            if ($acceptance?->checkout_token) {
                $this->stockReservations->releaseReservations($acceptance->checkout_token);
            }
        });
    }

    /**
     * Backward-compatible pipeline used by legacy calls and tests.
     *
     * @return list<Order>
     */
    public function createPaidOrdersFromCart(User $user, Address $shippingAddress, Address $billingAddress, array $invoiceData = [], array $paymentData = []): array
    {
        $prepared = $this->prepareCheckoutOrders(
            $user,
            $shippingAddress,
            $billingAddress,
            $invoiceData,
            $paymentData['payment_method'] ?? 'credit_card'
        );

        $confirmed = [];
        foreach ($prepared['orders'] as $order) {
            $confirmed[] = $this->confirmPaidOrder($order);
        }

        return $confirmed;
    }
}
