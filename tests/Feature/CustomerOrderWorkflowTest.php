<?php

namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\DesignApproval;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteRequest;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomerAndVendor(): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $vendorUser->id]);
        $vendorUser->update(['vendor_id' => $vendor->id]);

        return [$customer, $vendorUser, $vendor];
    }

    public function test_customer_can_view_orders_index_with_turkish_types_and_badges(): void
    {
        [$customer, $vendorUser, $vendor] = $this->createCustomerAndVendor();

        $order = Order::create([
            'order_number' => 'BY-TEST-999',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'subtotal' => 450.00,
            'commission_rate' => 10,
            'commission_amount' => 45.00,
            'vendor_amount' => 405.00,
        ]);

        $response = $this->actingAs($customer)->get(route('account.orders.index'));
        $response->assertOk()
            ->assertSee('BY-TEST-999')
            ->assertSee('Pazaryeri')
            ->assertSee('450,00');
    }

    public function test_customer_can_view_proof_and_approve_it_moving_order_to_in_production(): void
    {
        [$customer, $vendorUser, $vendor] = $this->createCustomerAndVendor();

        $order = Order::create([
            'order_number' => 'BY-TEST-PROOF-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::DESIGN_REVIEW,
            'payment_status' => 'paid',
            'subtotal' => 600.00,
            'commission_rate' => 10,
            'commission_amount' => 60.00,
            'vendor_amount' => 540.00,
        ]);

        $approval = DesignApproval::create([
            'order_id' => $order->id,
            'round' => 1,
            'design_file_path' => 'proofs/test-proof.pdf',
            'vendor_note' => 'Renkler CMYK ayarlandı, lütfen onaylayınız.',
            'status' => 'pending',
        ]);

        // Müşteri sipariş detayında provayı görür
        $response = $this->actingAs($customer)->get(route('account.orders.show', $order));
        $response->assertOk()
            ->assertSee('Dijital Baskı Provası')
            ->assertSee('Renkler CMYK ayarlandı')
            ->assertSee('Provayı Onaylıyorum');

        // Müşteri provayı onaylar -> Sipariş durumu otomatik olarak in_production olur
        $postResponse = $this->actingAs($customer)->post(route('account.orders.design.approve', [$order, $approval]));
        $postResponse->assertSessionHas('success');

        $this->assertEquals('approved', $approval->fresh()->status);
        $this->assertEquals(OrderStatus::IN_PRODUCTION, $order->fresh()->status);
    }

    public function test_customer_can_request_revision_on_proof(): void
    {
        [$customer, $vendorUser, $vendor] = $this->createCustomerAndVendor();

        $order = Order::create([
            'order_number' => 'BY-TEST-PROOF-2',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::DESIGN_REVIEW,
            'payment_status' => 'paid',
            'subtotal' => 600.00,
            'commission_rate' => 10,
            'commission_amount' => 60.00,
            'vendor_amount' => 540.00,
        ]);

        $approval = DesignApproval::create([
            'order_id' => $order->id,
            'round' => 1,
            'design_file_path' => 'proofs/test-proof.pdf',
            'vendor_note' => 'İlk prova',
            'status' => 'pending',
        ]);

        $postResponse = $this->actingAs($customer)->post(route('account.orders.design.revision', [$order, $approval]), [
            'customer_feedback' => 'Logo biraz daha sağa kaydırılsın lütfen.',
        ]);
        $postResponse->assertSessionHas('success');

        $this->assertEquals('revision_requested', $approval->fresh()->status);
        $this->assertEquals('Logo biraz daha sağa kaydırılsın lütfen.', $approval->fresh()->customer_feedback);

        // Müşteri detay sayfasında revizyon notunu görür
        $this->get(route('account.orders.show', $order))
            ->assertSee('Logo biraz daha sağa kaydırılsın lütfen.');
    }

    public function test_customer_sees_shipment_tracking_information(): void
    {
        [$customer, $vendorUser, $vendor] = $this->createCustomerAndVendor();

        $order = Order::create([
            'order_number' => 'BY-TEST-SHIP-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::SHIPPED,
            'payment_status' => 'paid',
            'subtotal' => 750.00,
            'commission_rate' => 10,
            'commission_amount' => 75.00,
            'vendor_amount' => 675.00,
            'shipped_at' => now(),
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'carrier' => 'Yurtiçi Kargo',
            'tracking_number' => 'YK-9876543210',
            'shipped_at' => now(),
        ]);

        $response = $this->actingAs($customer)->get(route('account.orders.show', $order));
        $response->assertOk()
            ->assertSee('Kargo ve Gönderi Takibi')
            ->assertSee('Yurtiçi Kargo')
            ->assertSee('YK-9876543210');
    }

    public function test_quote_selection_creates_confirmed_order_with_paid_payment_status(): void
    {
        [$customer, $vendorUser, $vendor] = $this->createCustomerAndVendor();
        $category = Category::factory()->create();

        $quoteRequest = QuoteRequest::create([
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'title' => 'Özel Katalog Baskısı',
            'request_type' => 'physical_quote',
            'status' => 'open',
        ]);

        $quote = Quote::create([
            'quote_request_id' => $quoteRequest->id,
            'vendor_id' => $vendor->id,
            'amount' => 1500.00,
            'delivery_days' => 5,
            'status' => 'pending',
        ]);

        $this->actingAs($customer)->post(route('quote-requests.select-quote', [$quoteRequest, $quote]))
            ->assertRedirect(route('quote-requests.show', $quoteRequest));

        $order = Order::where('quote_id', $quote->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(OrderStatus::CONFIRMED, $order->status);
        $this->assertEquals('paid', $order->payment_status);

        // Satıcı bu siparişi allowedTransitions üzerinden üretime alabilir
        $workflow = app(\App\Services\OrderWorkflowService::class);
        $this->assertTrue($workflow->canTransition($order, OrderStatus::IN_PRODUCTION));
    }
}
