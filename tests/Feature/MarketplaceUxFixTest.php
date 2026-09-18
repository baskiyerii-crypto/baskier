<?php

namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Models\Category;
use App\Models\DesignApproval;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceUxFixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: User, 2: Vendor}
     */
    private function pair(): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $vendorUser->id, 'balance' => 10]);
        $vendorUser->update(['vendor_id' => $vendor->id]);

        return [$customer, $vendorUser, $vendor];
    }

    public function test_customer_order_show_and_approve_do_not_500(): void
    {
        [$customer, $vendorUser, $vendor] = $this->pair();
        $order = Order::create([
            'order_number' => 'BY-UX-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::DESIGN_REVIEW,
            'payment_status' => 'paid',
            'subtotal' => 100,
            'commission_rate' => 10,
            'commission_amount' => 10,
            'vendor_amount' => 90,
        ]);
        $approval = DesignApproval::create([
            'order_id' => $order->id,
            'round' => 1,
            'status' => 'pending',
            'design_file_path' => 'designs/missing.pdf',
        ]);

        $this->actingAs($customer)->get(route('account.orders.show', $order))->assertOk();
        $confirmed = Order::create([
            'order_number' => 'BY-UX-CONF',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'subtotal' => 40,
            'commission_rate' => 10,
            'commission_amount' => 4,
            'vendor_amount' => 36,
        ]);
        $this->actingAs($customer)->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('BY-UX-CONF')
            ->assertSee('Onaylandı');
        $this->actingAs($customer)->post(route('account.orders.design.approve', [$order, $approval]))
            ->assertRedirect();
        $this->actingAs($customer)->get(route('account.orders.show', $order))->assertOk();
    }

    public function test_vendor_confirm_payment_notifies_and_returns(): void
    {
        [$customer, $vendorUser, $vendor] = $this->pair();
        $order = Order::create([
            'order_number' => 'BY-UX-PAY',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::PENDING_PAYMENT,
            'payment_status' => 'pending',
            'subtotal' => 100,
            'commission_rate' => 10,
            'commission_amount' => 10,
            'vendor_amount' => 90,
        ]);

        $this->actingAs($vendorUser)->post(route('vendor.orders.confirm-payment', $order))->assertRedirect();
        $this->actingAs($vendorUser)->get(route('vendor.orders.show', $order))->assertOk();
        $this->assertSame(OrderStatus::CONFIRMED, $order->fresh()->status);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $customer->id]);
    }

    public function test_proof_file_authorized_and_stranger_forbidden(): void
    {
        Storage::fake('public');
        [$customer, $vendorUser, $vendor] = $this->pair();
        $stranger = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'order_number' => 'BY-UX-FILE',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::DESIGN_REVIEW,
            'payment_status' => 'paid',
            'subtotal' => 50,
            'commission_rate' => 10,
            'commission_amount' => 5,
            'vendor_amount' => 45,
        ]);
        $path = UploadedFile::fake()->create('prova.pdf', 20, 'application/pdf')->store('designs/'.$order->id, 'public');
        $approval = DesignApproval::create([
            'order_id' => $order->id,
            'round' => 1,
            'status' => 'pending',
            'design_file_path' => $path,
        ]);

        $this->actingAs($customer)->get(route('account.orders.design.file', [$order, $approval]))->assertOk();
        $this->actingAs($vendorUser)->get(route('vendor.orders.design.file', [$order, $approval]))->assertOk();
        $this->actingAs($stranger)->get(route('account.orders.design.file', [$order, $approval]))->assertForbidden();
    }

    public function test_vendor_proof_upload_notifies_customer(): void
    {
        Storage::fake('public');
        [$customer, $vendorUser, $vendor] = $this->pair();
        $order = Order::create([
            'order_number' => 'BY-UX-PROOF',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'subtotal' => 80,
            'commission_rate' => 10,
            'commission_amount' => 8,
            'vendor_amount' => 72,
        ]);

        $this->actingAs($vendorUser)->post(route('vendor.orders.design.store', $order), [
            'file' => UploadedFile::fake()->image('prova.jpg'),
            'vendor_note' => 'Prova hazır',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $customer->id]);
    }

    public function test_vendor_is_redirected_from_customer_question_urls(): void
    {
        [, $vendorUser] = $this->pair();
        $this->actingAs($vendorUser)->get(route('customer.order-questions.index'))
            ->assertRedirect(route('vendor.order-questions.index'));
        $this->actingAs($vendorUser)->get(route('customer.product-questions.index'))
            ->assertRedirect(route('vendor.product-questions.index'));
    }

    public function test_favorite_toggle_json_does_not_require_reload(): void
    {
        [$customer] = $this->pair();
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'category_id' => $category->id]);

        $this->actingAs($customer)->postJson(route('favorites.toggle', $product))
            ->assertOk()
            ->assertJson(['favorited' => true]);
    }

    public function test_checkout_without_contract_scroll_is_rejected(): void
    {
        [$customer] = $this->pair();
        $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_address_id' => 1,
            'payment_method' => 'bank_transfer',
            'accept_distance_sales' => '1',
            'accept_kvkk' => '1',
            'invoice_type' => 'individual',
            'invoice_full_name' => 'A',
            'invoice_email' => 'a@b.com',
            'invoice_phone' => '05551234567',
        ])->assertSessionHasErrors(['contract_scrolled_at', 'kvkk_scrolled_at']);
    }

    public function test_admin_can_gift_vendor_balance(): void
    {
        [, , $vendor] = $this->pair();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.vendors.gift-balance', $vendor), [
            'amount' => 25,
            'description' => 'Jest',
        ])->assertRedirect();

        $this->assertEquals(35.0, (float) $vendor->fresh()->balance);
        $this->assertDatabaseHas('vendor_balance_transactions', [
            'vendor_id' => $vendor->id,
            'type' => 'gift',
        ]);
    }
}
