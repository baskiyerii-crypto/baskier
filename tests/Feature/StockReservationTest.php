<?php

namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Domain\PaymentStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Contract;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Product;
use App\Models\StockReservation;
use App\Models\User;
use App\Models\Vendor;
use App\Services\MarketplaceOrderService;
use App\Services\StockReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    private User $user1;
    private User $user2;
    private Product $product;
    private Address $address1;
    private Address $address2;
    private StockReservationService $reservationService;
    private MarketplaceOrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        Contract::firstOrCreate(
            ['key' => 'distance_sales'],
            ['title' => 'Mesafeli Satış Sözleşmesi', 'content' => 'Sözleşme', 'version' => 1, 'is_active' => true]
        );

        $this->user1 = User::factory()->create(['role' => 'customer']);
        $this->user2 = User::factory()->create(['role' => 'customer']);

        $category = Category::create(['name' => 'Kategori', 'slug' => 'kategori', 'is_active' => true]);
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create(['user_id' => $vendorUser->id, 'name' => 'Vendor', 'slug' => 'vendor']);

        // Product has exactly 1 item in stock
        $this->product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Son Stok Ürünü',
            'slug' => 'son-stok-urunu',
            'price' => 100.00,
            'stock' => 1,
            'is_active' => true,
            'moderation_status' => 'approved',
        ]);

        $this->address1 = Address::create([
            'user_id' => $this->user1->id,
            'label' => 'Adres 1',
            'full_name' => 'Müşteri 1',
            'phone' => '05551111111',
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
            'line1' => 'Adres 1',
            'is_default' => true,
        ]);

        $this->address2 = Address::create([
            'user_id' => $this->user2->id,
            'label' => 'Adres 2',
            'full_name' => 'Müşteri 2',
            'phone' => '05552222222',
            'city' => 'İstanbul',
            'district' => 'Beşiktaş',
            'line1' => 'Adres 2',
            'is_default' => true,
        ]);

        $this->reservationService = app(StockReservationService::class);
        $this->orderService = app(MarketplaceOrderService::class);
    }

    public function test_two_users_cannot_reserve_the_same_last_stock(): void
    {
        $token1 = (string) Str::uuid();
        $token2 = (string) Str::uuid();

        // User 1 reserves the last item
        $items = [['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 1]];
        $reservations1 = $this->reservationService->reserveStock($items, $token1, $this->user1->id, 15);
        $this->assertCount(1, $reservations1);

        // User 2 attempts to reserve the same last item -> Must fail with InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        $this->reservationService->reserveStock($items, $token2, $this->user2->id, 15);
    }

    public function test_expired_reservation_allows_other_users_to_reserve(): void
    {
        $token1 = (string) Str::uuid();
        $token2 = (string) Str::uuid();

        $items = [['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 1]];

        // User 1 reserves the item with negative ttl to simulate expiration
        StockReservation::create([
            'product_id' => $this->product->id,
            'variant_id' => null,
            'user_id' => $this->user1->id,
            'checkout_token' => $token1,
            'quantity' => 1,
            'status' => 'reserved',
            'expires_at' => now()->subMinute(),
        ]);

        // Cleanup expired
        $this->reservationService->cleanupExpired();

        // User 2 now can successfully reserve the item
        $reservations2 = $this->reservationService->reserveStock($items, $token2, $this->user2->id, 15);
        $this->assertCount(1, $reservations2);
    }

    public function test_confirmed_paid_order_commits_stock_and_deducts_inventory(): void
    {
        CartItem::create([
            'user_id' => $this->user1->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $prepared = $this->orderService->prepareCheckoutOrders(
            $this->user1,
            $this->address1,
            $this->address1
        );

        $order = $prepared['orders'][0];
        $this->assertEquals(1, $this->product->fresh()->stock); // Stock not decremented yet

        // Confirm payment
        $confirmed = $this->orderService->confirmPaidOrder($order);

        $this->assertEquals(OrderStatus::CONFIRMED, $confirmed->status);
        $this->assertEquals(PaymentStatus::PAID, $confirmed->payment_status);

        // Inventory must now be decremented to 0
        $this->assertEquals(0, $this->product->fresh()->stock);

        // Cart item must be removed
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user1->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_failed_order_releases_reserved_stock(): void
    {
        CartItem::create([
            'user_id' => $this->user1->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $prepared = $this->orderService->prepareCheckoutOrders(
            $this->user1,
            $this->address1,
            $this->address1
        );

        $order = $prepared['orders'][0];

        // Fail order
        $this->orderService->failOrder($order, 'Kart limiti yetersiz');

        $order->refresh();
        $this->assertEquals(OrderStatus::CANCELLED, $order->status);
        $this->assertEquals(PaymentStatus::FAILED, $order->payment_status);

        // Reservation must be released
        $this->assertDatabaseHas('stock_reservations', [
            'order_id' => null,
            'status' => 'released',
        ]);

        // User 2 can now reserve it
        $token2 = (string) Str::uuid();
        $items = [['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 1]];
        $reservations2 = $this->reservationService->reserveStock($items, $token2, $this->user2->id, 15);
        $this->assertCount(1, $reservations2);
    }
}
