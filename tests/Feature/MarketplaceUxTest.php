<?php
namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_quantity_update_total_validation_and_ownership(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'category_id' => $category->id, 'price' => 125, 'stock' => 20]);
        $item = CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($user)->get(route('cart.index'))->assertOk()->assertSee('250,00')->assertSee('Adet');
        $this->put(route('cart.update', $item), ['quantity' => 3])->assertSessionHas('success');
        $this->get(route('cart.index'))->assertSee('375,00');
        $this->from(route('cart.index'))->put(route('cart.update', $item), ['quantity' => 0])->assertSessionHasErrors('quantity');
        $this->get(route('cart.index'))->assertSee('Lütfen aşağıdaki bilgileri kontrol edin:');
        $this->put(route('cart.update', $item), ['quantity' => 21])->assertSessionHas('error');
        $this->assertEquals(3, $item->fresh()->quantity);
        $this->actingAs(User::factory()->create())->put(route('cart.update', $item), ['quantity' => 1])->assertForbidden();
    }

    public function test_repeated_additions_cannot_exceed_quantity_limit(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['vendor_id' => Vendor::factory()->create()->id, 'category_id' => Category::factory()->create()->id, 'stock' => 2000]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 999]);
        $this->actingAs($user)->post(route('cart.add', $product), ['quantity' => 1])->assertSessionHas('error');
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'quantity' => 999]);
    }

    public function test_freelancer_can_open_and_quote_inbox_request_without_quotes_subscription(): void
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $user->id, 'freelancer_enabled' => true, 'quotes_enabled' => false]);
        $user->update(['vendor_id' => $vendor->id]);
        $request = QuoteRequest::create(['user_id' => User::factory()->create()->id, 'category_id' => Category::factory()->create()->id, 'title' => 'Kurumsal kimlik tasarımı', 'request_type' => 'freelancer', 'status' => 'open']);
        $this->actingAs($user)->get(route('vendor.freelancer.index'))->assertOk()->assertSee($request->title);
        $this->get(route('vendor.quote-requests.show', $request))->assertOk()->assertSee($request->title);
        $this->post(route('vendor.quote-requests.submit-quote', $request), ['amount' => 100])->assertSessionHas('error', 'Once bu talebe gorusme hakki almalisiniz.');
        \App\Models\QuoteMeetingCharge::create(['vendor_id' => $vendor->id, 'quote_request_id' => $request->id, 'amount' => 50]);
        $this->post(route('vendor.quote-requests.submit-quote', $request), ['amount' => 100, 'delivery_days' => 3])->assertSessionHas('success');
        $this->assertDatabaseHas('quotes', ['vendor_id' => $vendor->id, 'quote_request_id' => $request->id, 'amount' => 100]);
        $vendor->update(['freelancer_enabled' => false]);
        $this->actingAs($user->fresh());
        $this->get(route('vendor.quote-requests.show', $request))->assertRedirect(route('vendor.subscriptions.index'));
    }

    public function test_out_of_category_and_closed_requests_cannot_be_charged(): void
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $user->id, 'freelancer_enabled' => true, 'balance' => 1000]);
        $user->update(['vendor_id' => $vendor->id]);
        $category = Category::factory()->create();
        $vendor->quoteCategories()->attach($category);
        $request = QuoteRequest::create(['user_id' => User::factory()->create()->id, 'category_id' => Category::factory()->create()->id, 'title' => 'Başka kategori', 'request_type' => 'freelancer', 'status' => 'open']);
        $this->actingAs($user)->post(route('vendor.quote-requests.accept-meeting', $request))->assertForbidden();
        $request->update(['category_id' => $category->id, 'status' => 'closed']);
        $this->post(route('vendor.quote-requests.accept-meeting', $request))->assertSessionHas('error');
        $this->assertEquals(1000, $vendor->fresh()->balance);
        $this->assertDatabaseCount('quote_meeting_charges', 0);
    }

    public function test_admin_primary_pages_render(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['admin.dashboard', 'admin.products.index', 'admin.vendors.index', 'admin.support-tickets.index', 'admin.vendor-payout-requests.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }
}
