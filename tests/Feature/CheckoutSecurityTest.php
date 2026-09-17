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
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Vendor $vendor;
    private Product $product;
    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        Contract::firstOrCreate(
            ['key' => 'distance_sales'],
            ['title' => 'Mesafeli Satış Sözleşmesi', 'content' => 'Sözleşme metni', 'version' => 1, 'is_active' => true]
        );

        $this->user = User::factory()->create(['role' => 'customer']);

        $category = Category::create([
            'name' => 'Kartvizit',
            'slug' => 'kartvizit',
            'is_active' => true,
        ]);

        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $this->vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'name' => 'Baskıcı Vendor',
            'slug' => 'baskici-vendor',
            'commission_rate' => 10,
        ]);

        $this->product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $category->id,
            'name' => 'Standart Kartvizit',
            'slug' => 'standart-kartvizit',
            'price' => 150.00,
            'stock' => 10,
            'is_active' => true,
            'moderation_status' => 'approved',
        ]);

        $this->address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Evim',
            'full_name' => 'Ahmet Yılmaz',
            'phone' => '05551234567',
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
            'line1' => 'Moda Cad. No: 5',
            'is_default' => true,
        ]);
    }

    public function test_quick_buy_does_not_modify_existing_cart(): void
    {
        // Add an item to user's cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseCount('cart_items', 1);

        // Perform Quick Buy on a different product
        $anotherProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->product->category_id,
            'name' => 'Broşür',
            'slug' => 'brosur',
            'price' => 300.00,
            'stock' => 5,
            'is_active' => true,
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($this->user)->post(route('cart.add', $anotherProduct), [
            'quantity' => 1,
            'buy_now' => '1',
        ]);

        $response->assertRedirect(route('checkout.index'));

        // Cart items should STILL contain ONLY the original item with qty 2
        $cartItems = CartItem::where('user_id', $this->user->id)->get();
        $this->assertCount(1, $cartItems);
        $this->assertEquals($this->product->id, $cartItems->first()->product_id);
        $this->assertEquals(2, $cartItems->first()->quantity);
    }

    public function test_bank_transfer_and_cash_on_delivery_remain_pending(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)->post(route('checkout.store'), [
            'shipping_address_id' => $this->address->id,
            'payment_method' => 'bank_transfer',
            'accept_distance_sales' => '1',
            'invoice_type' => 'individual',
            'invoice_full_name' => 'Ahmet Yılmaz',
            'invoice_email' => 'ahmet@example.com',
            'invoice_phone' => '05551234567',
        ]);

        $response->assertRedirect(route('account.orders.index'));

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(OrderStatus::PENDING_PAYMENT, $order->status);
        $this->assertEquals(PaymentStatus::PENDING, $order->payment_status);

        // Stock must NOT be deducted yet!
        $this->assertEquals(10, $this->product->fresh()->stock);

        // Cart must NOT be deleted yet!
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_same_idempotency_key_prevents_duplicate_orders(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $idempotencyKey = (string) Str::uuid();

        $payload = [
            'shipping_address_id' => $this->address->id,
            'payment_method' => 'bank_transfer',
            'accept_distance_sales' => '1',
            'idempotency_key' => $idempotencyKey,
            'invoice_type' => 'individual',
            'invoice_full_name' => 'Ahmet Yılmaz',
            'invoice_email' => 'ahmet@example.com',
            'invoice_phone' => '05551234567',
        ];

        // First submit
        $this->actingAs($this->user)->post(route('checkout.store'), $payload);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payment_attempts', 1);

        // Mark the attempt paid to simulate completed charge
        $attempt = PaymentAttempt::where('idempotency_key', $idempotencyKey)->first();
        $attempt->update(['status' => PaymentStatus::PAID]);

        // Second duplicate submit with same idempotency key
        $this->actingAs($this->user)->post(route('checkout.store'), $payload);

        // Order count must still be 1! No duplicate orders!
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payment_attempts', 1);
    }

    public function test_api_checkout_never_creates_unverified_paid_order(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/checkout', [
            'address_id' => $this->address->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(201);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        // Status must NOT be confirmed / paid without real payment!
        $this->assertEquals(OrderStatus::PENDING_PAYMENT, $order->status);
        $this->assertEquals(PaymentStatus::PENDING, $order->payment_status);
    }

    public function test_multi_vendor_cart_splits_into_separate_orders_under_single_attempt(): void
    {
        $vendorUser2 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = Vendor::create([
            'user_id' => $vendorUser2->id,
            'name' => 'İkinci Vendor',
            'slug' => 'ikinci-vendor',
            'commission_rate' => 15,
        ]);

        $product2 = Product::create([
            'vendor_id' => $vendor2->id,
            'category_id' => $this->product->category_id,
            'name' => 'Tabela',
            'slug' => 'tabela',
            'price' => 500.00,
            'stock' => 3,
            'is_active' => true,
            'moderation_status' => 'approved',
        ]);

        CartItem::create(['user_id' => $this->user->id, 'product_id' => $this->product->id, 'quantity' => 1]);
        CartItem::create(['user_id' => $this->user->id, 'product_id' => $product2->id, 'quantity' => 1]);

        $this->actingAs($this->user)->post(route('checkout.store'), [
            'shipping_address_id' => $this->address->id,
            'payment_method' => 'bank_transfer',
            'accept_distance_sales' => '1',
            'invoice_type' => 'individual',
            'invoice_full_name' => 'Ahmet Yılmaz',
            'invoice_email' => 'ahmet@example.com',
            'invoice_phone' => '05551234567',
        ]);

        // Must create 2 distinct orders (one per vendor)
        $orders = Order::where('user_id', $this->user->id)->get();
        $this->assertCount(2, $orders);

        $this->assertEquals(1, $orders->where('vendor_id', $this->vendor->id)->count());
        $this->assertEquals(1, $orders->where('vendor_id', $vendor2->id)->count());

        // Both must be linked to the same PaymentAttempt
        $attempt = PaymentAttempt::where('user_id', $this->user->id)->first();
        $this->assertNotNull($attempt);
        $this->assertEquals('650.00', (string) $attempt->amount);
    }
}
