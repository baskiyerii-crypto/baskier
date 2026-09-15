<?php

namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createVendorUser(): array
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $user->forceFill(['vendor_id' => $vendor->id])->save();

        return [$user, $vendor];
    }

    public function test_vendor_dashboard_shows_correct_metrics(): void
    {
        [$user, $vendor] = $this->createVendorUser();

        // Siparişler oluştur
        Order::create([
            'order_number' => 'BY-TEST-001',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::CONFIRMED,
            'subtotal' => 1000.00,
            'commission_rate' => 10.00,
            'commission_amount' => 100.00,
            'vendor_amount' => 900.00,
        ]);

        Order::create([
            'order_number' => 'BY-TEST-002',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::DESIGN_REVIEW,
            'subtotal' => 500.00,
            'commission_rate' => 10.00,
            'commission_amount' => 50.00,
            'vendor_amount' => 450.00,
        ]);

        Order::create([
            'order_number' => 'BY-TEST-003',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::IN_PRODUCTION,
            'subtotal' => 2000.00,
            'commission_rate' => 10.00,
            'commission_amount' => 200.00,
            'vendor_amount' => 1800.00,
        ]);

        $response = $this->actingAs($user)->get(route('vendor.dashboard'));

        $response->assertOk();
        $response->assertViewHas('ordersCount', 3);
        $response->assertViewHas('ordersPending', 3);
        $response->assertViewHas('proofPendingCount', 1);
        $response->assertViewHas('readyToShipCount', 1);
        $response->assertViewHas('totalRevenue', 3150.00);
    }

    public function test_vendor_order_status_transitions_and_shipment_tracking(): void
    {
        [$user, $vendor] = $this->createVendorUser();

        $order = Order::create([
            'order_number' => 'BY-TEST-ORD-1',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::CONFIRMED,
            'subtotal' => 1000.00,
            'commission_rate' => 10.00,
            'commission_amount' => 100.00,
            'vendor_amount' => 900.00,
        ]);

        // 1. Confirmed -> In Production
        $response = $this->actingAs($user)->put(route('vendor.orders.update-status', $order), [
            'status' => OrderStatus::IN_PRODUCTION,
        ]);
        $response->assertRedirect();
        $this->assertEquals(OrderStatus::IN_PRODUCTION, $order->fresh()->status);

        // 2. In Production -> Ready To Ship
        $response = $this->actingAs($user)->put(route('vendor.orders.update-status', $order), [
            'status' => OrderStatus::READY_TO_SHIP,
        ]);
        $response->assertRedirect();
        $this->assertEquals(OrderStatus::READY_TO_SHIP, $order->fresh()->status);

        // 3. Ready To Ship -> Shipped (Kargo Bilgileri ile)
        $response = $this->actingAs($user)->put(route('vendor.orders.update-status', $order), [
            'status' => OrderStatus::SHIPPED,
            'carrier' => 'Yurtiçi Kargo',
            'tracking_number' => 'YK-123456789',
        ]);
        $response->assertRedirect();
        $this->assertEquals(OrderStatus::SHIPPED, $order->fresh()->status);

        // Shipment kaydı oluşmuş mu kontrol et
        $shipment = Shipment::where('order_id', $order->id)->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('Yurtiçi Kargo', $shipment->carrier);
        $this->assertEquals('YK-123456789', $shipment->tracking_number);

        // 4. Shipped -> Delivered
        $response = $this->actingAs($user)->put(route('vendor.orders.update-status', $order), [
            'status' => OrderStatus::DELIVERED,
        ]);
        $response->assertRedirect();
        $this->assertEquals(OrderStatus::DELIVERED, $order->fresh()->status);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        [$user, $vendor] = $this->createVendorUser();

        $order = Order::create([
            'order_number' => 'BY-TEST-ORD-2',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::CONFIRMED,
            'subtotal' => 1000.00,
            'commission_rate' => 10.00,
            'commission_amount' => 100.00,
            'vendor_amount' => 900.00,
        ]);

        // Confirmed iken doğrudan Delivered yapılamaz
        $response = $this->actingAs($user)->put(route('vendor.orders.update-status', $order), [
            'status' => OrderStatus::DELIVERED,
        ]);
        $response->assertSessionHas('error');
        $this->assertEquals(OrderStatus::CONFIRMED, $order->fresh()->status);
    }

    public function test_vendor_cannot_manage_another_vendors_order(): void
    {
        [$user, $vendor] = $this->createVendorUser();
        [$otherUser, $otherVendor] = $this->createVendorUser();

        $otherOrder = Order::create([
            'order_number' => 'BY-TEST-OTHER',
            'user_id' => $otherUser->id,
            'vendor_id' => $otherVendor->id,
            'status' => OrderStatus::CONFIRMED,
            'subtotal' => 500.00,
            'commission_rate' => 10.00,
            'commission_amount' => 50.00,
            'vendor_amount' => 450.00,
        ]);

        $this->actingAs($user)
            ->get(route('vendor.orders.show', $otherOrder))
            ->assertStatus(403);

        $this->actingAs($user)
            ->put(route('vendor.orders.update-status', $otherOrder), [
                'status' => OrderStatus::IN_PRODUCTION,
            ])
            ->assertStatus(403);
    }

    public function test_vendor_orders_index_view_renders_with_filters(): void
    {
        [$user, $vendor] = $this->createVendorUser();

        Order::create([
            'order_number' => 'BY-SEARCH-TARGET',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::CONFIRMED,
            'subtotal' => 1000.00,
            'commission_rate' => 10.00,
            'commission_amount' => 100.00,
            'vendor_amount' => 900.00,
        ]);

        Order::create([
            'order_number' => 'BY-OTHER-ORD',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::DELIVERED,
            'subtotal' => 2000.00,
            'commission_rate' => 10.00,
            'commission_amount' => 200.00,
            'vendor_amount' => 1800.00,
        ]);

        // Search test
        $response = $this->actingAs($user)->get(route('vendor.orders.index', ['q' => 'TARGET']));
        $response->assertOk();
        $response->assertSee('BY-SEARCH-TARGET');
        $response->assertDontSee('BY-OTHER-ORD');

        // Status filter test
        $responseStatus = $this->actingAs($user)->get(route('vendor.orders.index', ['status' => OrderStatus::DELIVERED]));
        $responseStatus->assertOk();
        $responseStatus->assertSee('BY-OTHER-ORD');
        $responseStatus->assertDontSee('BY-SEARCH-TARGET');
    }

    public function test_vendor_orders_show_view_renders_items_and_proofing(): void
    {
        [$user, $vendor] = $this->createVendorUser();

        $order = Order::create([
            'order_number' => 'BY-DETAIL-123',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::CONFIRMED,
            'subtotal' => 1250.00,
            'commission_rate' => 15.00,
            'commission_amount' => 187.50,
            'vendor_amount' => 1062.50,
            'shipping_address' => 'Atatürk Cad. No: 123 Kadıköy/İstanbul',
        ]);

        $order->items()->create([
            'name' => 'Özel Baskılı Karton Bardak',
            'variant_name' => '8 oz - Tek Duvar',
            'sku' => 'BARDAK-8OZ',
            'price' => 2.50,
            'quantity' => 500,
        ]);

        $order->designApprovals()->create([
            'round' => 1,
            'status' => 'revision_requested',
            'design_file_path' => 'designs/test.pdf',
            'vendor_note' => 'Prova hazırlandı.',
            'customer_feedback' => 'Logo biraz daha sağa kaydırılsın lütfen.',
            'uploaded_by_user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('vendor.orders.show', $order));

        $response->assertOk();
        $response->assertSee('BY-DETAIL-123');
        $response->assertSee('Özel Baskılı Karton Bardak');
        $response->assertSee('8 oz - Tek Duvar');
        $response->assertSee('500 adet');
        $response->assertSee('₺1.062,50');
        $response->assertSee('Atatürk Cad. No: 123 Kadıköy/İstanbul');
        $response->assertSee('Logo biraz daha sağa kaydırılsın lütfen.');
        $response->assertSee('Üretime Al');
    }

    public function test_vendor_products_index_renders_and_supports_filters(): void
    {
        [$user, $vendor] = $this->createVendorUser();
        $category = \App\Models\Category::create(['name' => 'Kutu & Koli', 'slug' => 'kutu-koli', 'is_active' => true]);

        \App\Models\Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Özel Baskılı Koli',
            'slug' => 'ozel-baskili-koli',
            'price' => 50.00,
            'stock' => 100,
            'is_active' => true,
            'product_type' => 'physical',
        ]);

        \App\Models\Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Logo Tasarım Paketi',
            'slug' => 'logo-tasarim-paketi',
            'price' => 1500.00,
            'stock' => 999,
            'is_active' => false,
            'product_type' => 'digital',
        ]);

        // Search test
        $resSearch = $this->actingAs($user)->get(route('vendor.products.index', ['q' => 'Koli']));
        $resSearch->assertOk();
        $resSearch->assertSee('Özel Baskılı Koli');
        $resSearch->assertDontSee('Logo Tasarım Paketi');

        // Status filter test
        $resActive = $this->actingAs($user)->get(route('vendor.products.index', ['status' => 'active']));
        $resActive->assertOk();
        $resActive->assertSee('Özel Baskılı Koli');
        $resActive->assertDontSee('Logo Tasarım Paketi');

        $resPassive = $this->actingAs($user)->get(route('vendor.products.index', ['status' => 'passive']));
        $resPassive->assertOk();
        $resPassive->assertSee('Logo Tasarım Paketi');
        $resPassive->assertDontSee('Özel Baskılı Koli');

        // Create form view
        $this->actingAs($user)->get(route('vendor.products.create'))->assertOk();
    }

    public function test_vendor_payout_request_flow_and_overdraft_prevention(): void
    {
        [$user, $vendor] = $this->createVendorUser();
        $vendor->update(['balance' => 500.00]);

        // 1. Index sayfası çekilebilir bakiyeyi göstermeli
        $response = $this->actingAs($user)->get(route('vendor.payout-requests.index'));
        $response->assertOk();
        $response->assertSee('₺500,00');

        // 2. Başarılı talep oluşturma (300 TL)
        $postRes = $this->actingAs($user)->post(route('vendor.payout-requests.store'), [
            'amount' => 300.00,
            'iban' => 'TR123456789012345678901234',
            'account_holder' => 'Ahmet Yılmaz',
        ]);
        $postRes->assertSessionHas('success');

        // 3. İkinci talep: Kullanılabilir bakiye 200 TL iken 250 TL istenirse engellenmeli
        $secondPost = $this->actingAs($user)->post(route('vendor.payout-requests.store'), [
            'amount' => 250.00,
            'iban' => 'TR123456789012345678901234',
            'account_holder' => 'Ahmet Yılmaz',
        ]);
        $secondPost->assertSessionHas('error');

        // 4. Geçersiz IBAN reddedilmeli
        $invalidIbanPost = $this->actingAs($user)->post(route('vendor.payout-requests.store'), [
            'amount' => 50.00,
            'iban' => 'INVALID_IBAN_123',
            'account_holder' => 'Ahmet Yılmaz',
        ]);
        $invalidIbanPost->assertSessionHasErrors('iban');
    }
}
