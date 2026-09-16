<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarketplaceOrderService
{
    /**
     * @return list<Order>
     */
    public function createPaidOrdersFromCart(User $user, Address $shippingAddress, Address $billingAddress, array $invoiceData = [], array $paymentData = []): array
    {
        if ($shippingAddress->user_id !== $user->id || $billingAddress->user_id !== $user->id) {
            throw new \InvalidArgumentException('Seçilen adresler size ait değil.');
        }

        $items = CartItem::where('user_id', $user->id)->with(['product.vendor', 'variant'])->get();
        if ($items->isEmpty()) {
            throw new \InvalidArgumentException('Sepetiniz boş.');
        }

        $rate = Setting::commissionRate();
        $waitDays = Setting::commissionWaitDays();
        $shippingText = $shippingAddress->formatted . ' | ' . $shippingAddress->full_name . ' | ' . $shippingAddress->phone;

        return DB::transaction(function () use ($user, $items, $rate, $waitDays, $shippingText, $shippingAddress, $billingAddress, $invoiceData, $paymentData) {
            $byVendor = $items->groupBy(fn (CartItem $ci) => $ci->product->vendor_id);
            $orders = [];

            foreach ($byVendor as $vendorId => $group) {
                $subtotal = '0';
                foreach ($group as $ci) {
                    $unitPrice = (float) $ci->product->price + (float) ($ci->variant?->price_adjustment ?? 0);
                    $line = bcmul((string) $unitPrice, (string) $ci->quantity, 2);
                    $subtotal = bcadd($subtotal, $line, 2);
                }

                $commissionAmount = round((float) $subtotal * $rate / 100, 2);
                $vendorAmount = round((float) $subtotal - $commissionAmount, 2);

                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'user_id' => $user->id,
                    'vendor_id' => (int) $vendorId,
                    'type' => 'product',
                    'quote_id' => null,
                    'freelancer_job_id' => null,
                    'status' => OrderStatus::CONFIRMED,
                    'payment_status' => 'paid',
                    'payment_method' => $paymentData['payment_method'] ?? 'credit_card',
                    'subtotal' => $subtotal,
                    'commission_rate' => $rate,
                    'commission_amount' => $commissionAmount,
                    'vendor_amount' => $vendorAmount,
                    'paid_at' => now(),
                    'commission_ready_at' => now()->addDays($waitDays),
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

                foreach ($group as $ci) {
                    $p = $ci->product;
                    $variant = $ci->variant;
                    $stock = $variant ? (int) $variant->stock : (int) $p->stock;
                    if ($stock < $ci->quantity) {
                        throw new \InvalidArgumentException('Yetersiz stok: ' . $p->name);
                    }
                    $unitPrice = (float) $p->price + (float) ($variant?->price_adjustment ?? 0);
                    if ($variant) {
                        $variant->decrement('stock', $ci->quantity);
                    } else {
                        $p->decrement('stock', $ci->quantity);
                    }

                    $order->items()->create([
                        'product_id' => $p->id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'variant_id' => $variant?->id,
                        'variant_name' => $variant?->name,
                        'variant_sku' => $variant?->sku,
                        'price' => $unitPrice,
                        'quantity' => $ci->quantity,
                        'digital_link' => $p->isDigital() ? $p->digital_link : null,
                    ]);
                }

                $orders[] = $order;
            }

            CartItem::where('user_id', $user->id)->delete();

            $orderService = app(\App\Services\OrderService::class);
            foreach ($orders as $created) {
                $orderService->setTerminDueAt($created);
            }

            return $orders;
        });
    }
}
