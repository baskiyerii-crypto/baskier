<?php

namespace App\Services;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarketplaceOrderService
{
    /**
     * @return list<Order>
     */
    public function createPaidOrdersFromCart(User $user, Address $address): array
    {
        if ($address->user_id !== $user->id) {
            throw new \InvalidArgumentException('Adres size ait değil.');
        }

        $items = CartItem::where('user_id', $user->id)->with('product.vendor')->get();
        if ($items->isEmpty()) {
            throw new \InvalidArgumentException('Sepetiniz boş.');
        }

        $rate = Setting::commissionRate();
        $waitDays = Setting::commissionWaitDays();
        $shippingText = $address->formatted . ' | ' . $address->full_name . ' | ' . $address->phone;

        return DB::transaction(function () use ($user, $items, $rate, $waitDays, $shippingText) {
            $byVendor = $items->groupBy(fn (CartItem $ci) => $ci->product->vendor_id);
            $orders = [];

            foreach ($byVendor as $vendorId => $group) {
                $subtotal = '0';
                foreach ($group as $ci) {
                    $line = bcmul((string) $ci->product->price, (string) $ci->quantity, 2);
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
                    'status' => 'paid',
                    'subtotal' => $subtotal,
                    'commission_rate' => $rate,
                    'commission_amount' => $commissionAmount,
                    'vendor_amount' => $vendorAmount,
                    'paid_at' => now(),
                    'commission_ready_at' => now()->addDays($waitDays),
                    'shipping_address' => $shippingText,
                ]);

                foreach ($group as $ci) {
                    $p = $ci->product;
                    if ($p->stock < $ci->quantity) {
                        throw new \InvalidArgumentException('Yetersiz stok: ' . $p->name);
                    }
                    $p->decrement('stock', $ci->quantity);

                    $order->items()->create([
                        'product_id' => $p->id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'price' => $p->price,
                        'quantity' => $ci->quantity,
                        'digital_link' => $p->isDigital() ? $p->digital_link : null,
                    ]);
                }

                $orders[] = $order;
            }

            CartItem::where('user_id', $user->id)->delete();

            return $orders;
        });
    }
}
