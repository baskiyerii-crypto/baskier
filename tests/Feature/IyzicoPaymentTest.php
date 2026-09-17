<?php

namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Domain\PaymentStatus;
use App\Models\Address;
use App\Models\Category;
use App\Models\Contract;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Services\IyzicoClient;
use App\Services\MarketplaceOrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IyzicoPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Order $order;
    private PaymentAttempt $attempt;
    private IyzicoClient $iyzicoClient;
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        Contract::firstOrCreate(
            ['key' => 'distance_sales'],
            ['title' => 'Sözleşme', 'content' => 'Metin', 'version' => 1, 'is_active' => true]
        );

        // Configure sandbox iyzico credentials in settings
        Setting::set('api_iyzico_enabled', '1');
        Setting::set('payment_provider', 'iyzico');
        Setting::set('iyzico_mode', 'sandbox');
        Setting::set('iyzico_api_key', 'sandbox-test-api-key');
        Setting::set('iyzico_secret_key', 'sandbox-test-secret-key');
        Setting::set('iyzico_webhook_secret', 'sandbox-test-webhook-secret');
        Setting::set('iyzico_base_url', 'https://sandbox-api.iyzipay.com');

        $this->user = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'Baskı', 'slug' => 'baski', 'is_active' => true]);
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create(['user_id' => $vendorUser->id, 'name' => 'Vendor 1', 'slug' => 'vendor-1']);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Kartvizit',
            'slug' => 'kartvizit',
            'price' => 200.00,
            'stock' => 20,
            'is_active' => true,
            'moderation_status' => 'approved',
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-1001',
            'user_id' => $this->user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::PENDING_PAYMENT,
            'payment_status' => PaymentStatus::PENDING,
            'payment_method' => 'credit_card',
            'subtotal' => 200.00,
            'commission_rate' => 10,
            'commission_amount' => 20.00,
            'vendor_amount' => 180.00,
        ]);

        $this->order->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => 200.00,
            'quantity' => 1,
        ]);

        $this->attempt = PaymentAttempt::create([
            'conversation_id' => 'BY-CONV-TEST-12345',
            'idempotency_key' => 'IDEMP-TEST-12345',
            'user_id' => $this->user->id,
            'provider' => 'iyzico',
            'status' => PaymentStatus::PROCESSING,
            'amount' => 200.00,
            'currency' => 'TRY',
            'order_ids' => [$this->order->id],
            'metadata' => [
                'token' => 'token-test-xyz',
            ],
        ]);

        $this->order->update(['payment_attempt_id' => $this->attempt->id]);

        $this->iyzicoClient = app(IyzicoClient::class);
        $this->paymentService = app(PaymentService::class);
    }

    public function test_iyzico_v2_headers_generate_correct_hmac_sha256(): void
    {
        $headers = $this->iyzicoClient->generateV2Headers('/test-uri', '{"amount":100}');

        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertArrayHasKey('x-iyzi-rnd', $headers);
        $this->assertStringStartsWith('IYZWSv2 sandbox-test-api-key:', $headers['Authorization']);
    }

    public function test_webhook_with_valid_signature_succeeds_and_deduplicates(): void
    {
        Http::fake([
            'https://sandbox-api.iyzipay.com/payment/iyzipay/checkoutform/auth/ecom/detail' => Http::response([
                'status' => 'success',
                'paymentStatus' => 'SUCCESS',
                'paymentId' => 'PAY-999888',
                'currency' => 'TRY',
                'paidPrice' => '200.00',
            ], 200),
        ]);

        $rawBody = json_encode([
            'iyziEventId' => 'EVT-1001',
            'token' => 'token-test-xyz',
            'status' => 'SUCCESS',
        ]);

        $signature = hash_hmac('sha256', $rawBody, 'sandbox-test-webhook-secret');

        // First webhook call using raw content
        $response1 = $this->call(
            'POST',
            route('payment.iyzico.webhook'),
            [],
            [],
            [],
            [
                'HTTP_X_IYZ_SIGNATURE_V3' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $rawBody
        );

        $response1->assertStatus(200);
        $response1->assertJson(['status' => 'success']);

        // Order must be marked CONFIRMED and PAID
        $this->order->refresh();
        $this->assertEquals(OrderStatus::CONFIRMED, $this->order->status);
        $this->assertEquals(PaymentStatus::PAID, $this->order->payment_status);

        // Second webhook call with duplicate event ID
        $response2 = $this->call(
            'POST',
            route('payment.iyzico.webhook'),
            [],
            [],
            [],
            [
                'HTTP_X_IYZ_SIGNATURE_V3' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $rawBody
        );

        $response2->assertStatus(200);
        $response2->assertJson(['message' => 'Event already processed']);
    }

    public function test_webhook_with_invalid_signature_is_rejected_with_401(): void
    {
        $rawBody = json_encode(['token' => 'token-test-xyz']);
        $fakeSignature = 'invalid-signature-hash-value';

        $response = $this->call(
            'POST',
            route('payment.iyzico.webhook'),
            [],
            [],
            [],
            [
                'HTTP_X_IYZ_SIGNATURE_V3' => $fakeSignature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $rawBody
        );

        $response->assertStatus(401);

        // Order must NOT be modified
        $this->order->refresh();
        $this->assertEquals(OrderStatus::PENDING_PAYMENT, $this->order->status);
    }

    public function test_callback_verifies_server_to_server_and_confirms_order(): void
    {
        Http::fake([
            'https://sandbox-api.iyzipay.com/payment/iyzipay/checkoutform/auth/ecom/detail' => Http::response([
                'status' => 'success',
                'paymentStatus' => 'SUCCESS',
                'paymentId' => 'PAY-555444',
                'currency' => 'TRY',
                'paidPrice' => '200.00',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->post(route('payment.iyzico.callback'), [
            'token' => 'token-test-xyz',
        ]);

        $response->assertRedirect(route('account.orders.index'));

        $this->order->refresh();
        $this->assertEquals(OrderStatus::CONFIRMED, $this->order->status);
        $this->assertEquals(PaymentStatus::PAID, $this->order->payment_status);
        $this->assertNotNull($this->order->paid_at);
    }
}
