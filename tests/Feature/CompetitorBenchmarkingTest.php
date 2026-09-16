<?php

namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Models\Address;
use App\Models\Category;
use App\Models\DesignApproval;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Quote;
use App\Models\QuoteRequest;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompetitorBenchmarkingTest extends TestCase
{
    use RefreshDatabase;

    private function createVendorAndCustomer(): array
    {
        $vendorUser = User::factory()->create(['role' => 'vendor', 'name' => 'Matbaa Satıcısı']);
        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'name' => 'Mega Matbaa Ltd.',
            'slug' => 'mega-matbaa',
            'balance' => 1500.00,
            'verification_status' => 'verified',
        ]);
        $vendorUser->update(['vendor_id' => $vendor->id]);

        $customer = User::factory()->create(['role' => 'customer', 'name' => 'Müşteri Can']);

        return [$vendorUser, $vendor, $customer];
    }

    /**
     * Test 1: Satıcı Siparişe Dijital Prova Dosyası Yükleyebiliyor mu? (Proof Upload)
     */
    public function test_vendor_can_upload_design_proof(): void
    {
        Storage::fake('public');
        [$vendorUser, $vendor, $customer] = $this->createVendorAndCustomer();

        $order = Order::create([
            'order_number' => 'BY-PROOF-TEST-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'subtotal' => 1200.00,
            'commission_rate' => 10,
            'commission_amount' => 120.00,
            'vendor_amount' => 1080.00,
        ]);

        $pdfFile = UploadedFile::fake()->create('baski-provası-v1.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($vendorUser)->post(route('vendor.orders.design.store', $order), [
            'file' => $pdfFile,
            'vendor_note' => 'Baskı ölçüleri 8.5x5.5 cm olarak hazırlandı.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Sipariş durumu otomatik olarak DESIGN_REVIEW oldu mu?
        $this->assertEquals(OrderStatus::DESIGN_REVIEW, $order->fresh()->status);

        // DesignApproval kaydı ve dosya oluştu mu?
        $approval = DesignApproval::where('order_id', $order->id)->first();
        $this->assertNotNull($approval);
        $this->assertEquals(1, $approval->round);
        $this->assertEquals('pending', $approval->status);
        $this->assertEquals('Baskı ölçüleri 8.5x5.5 cm olarak hazırlandı.', $approval->vendor_note);
        Storage::disk('public')->assertExists($approval->design_file_path);
    }

    /**
     * Test 2: Müşteri Revizyon İster -> Satıcı 2. Tur Provayı Yükler -> Müşteri Onaylar -> Otomatik Üretime Geçer
     */
    public function test_full_proofing_revision_and_approval_cycle(): void
    {
        Storage::fake('public');
        [$vendorUser, $vendor, $customer] = $this->createVendorAndCustomer();

        $order = Order::create([
            'order_number' => 'BY-PROOF-CYCLE-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'subtotal' => 1200.00,
            'commission_rate' => 10,
            'commission_amount' => 120.00,
            'vendor_amount' => 1080.00,
        ]);

        // Tur 1: Satıcı yükler
        $pdf1 = UploadedFile::fake()->create('prova-v1.pdf', 500, 'application/pdf');
        $this->actingAs($vendorUser)->post(route('vendor.orders.design.store', $order), [
            'file' => $pdf1,
            'vendor_note' => 'Taslak 1',
        ]);
        $approval1 = DesignApproval::where('order_id', $order->id)->latest('round')->first();

        // Müşteri revizyon ister
        $this->actingAs($customer)->post(route('account.orders.design.revision', [$order, $approval1]), [
            'customer_feedback' => 'Arka plan rengini lacivert yapabilir miyiz?',
        ]);
        $this->assertEquals('revision_requested', $approval1->fresh()->status);

        // Tur 2: Satıcı revize dosyayı yükler
        $pdf2 = UploadedFile::fake()->create('prova-v2.pdf', 500, 'application/pdf');
        $this->actingAs($vendorUser)->post(route('vendor.orders.design.store', $order), [
            'file' => $pdf2,
            'vendor_note' => 'Arka plan rengi lacivert olarak güncellendi.',
        ]);
        $approval2 = DesignApproval::where('order_id', $order->id)->latest('round')->first();
        $this->assertEquals(2, $approval2->round);

        // Müşteri onaylar
        $this->actingAs($customer)->post(route('account.orders.design.approve', [$order, $approval2]));
        $this->assertEquals('approved', $approval2->fresh()->status);

        // Sipariş durumu otomatik IN_PRODUCTION olmalı
        $this->assertEquals(OrderStatus::IN_PRODUCTION, $order->fresh()->status);
    }

    /**
     * Test 3: Kargo Takip Bilgisi Girildiğinde Müşteri Panelinde Doğru Görünmesi
     */
    public function test_vendor_can_ship_with_carrier_and_customer_views_tracking(): void
    {
        [$vendorUser, $vendor, $customer] = $this->createVendorAndCustomer();

        $order = Order::create([
            'order_number' => 'BY-SHIP-CYCLE-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'type' => 'product',
            'status' => OrderStatus::READY_TO_SHIP,
            'payment_status' => 'paid',
            'subtotal' => 800.00,
            'commission_rate' => 10,
            'commission_amount' => 80.00,
            'vendor_amount' => 720.00,
        ]);

        // Satıcı kargoya verir
        $response = $this->actingAs($vendorUser)->put(route('vendor.orders.update-status', $order), [
            'status' => OrderStatus::SHIPPED,
            'carrier' => 'Aras Kargo',
            'tracking_number' => 'ARAS-99887766',
        ]);
        $response->assertRedirect();
        $this->assertEquals(OrderStatus::SHIPPED, $order->fresh()->status);

        // Müşteri sipariş detayında kargo bilgilerini görür
        $customerView = $this->actingAs($customer)->get(route('account.orders.show', $order));
        $customerView->assertOk();
        $customerView->assertSee('Aras Kargo');
        $customerView->assertSee('ARAS-99887766');
    }

    /**
     * Test 4: Sepet, Varyant Seçimi ve Sipariş Oluşumu (Physical E-Commerce)
     */
    public function test_cart_and_checkout_with_product_variants(): void
    {
        [$vendorUser, $vendor, $customer] = $this->createVendorAndCustomer();
        $category = Category::factory()->create();

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Özel Kartvizit',
            'slug' => 'ozel-kartvizit',
            'price' => 200.00,
            'stock' => 100,
            'is_active' => true,
            'product_type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '1000 Adet - Kabartma Laklı',
            'price_adjustment' => 150.00, // Toplam: 350 TL
            'stock' => 50,
            'sku' => 'KV-LAK-1000',
        ]);

        $address = Address::create([
            'user_id' => $customer->id,
            'title' => 'Ofis',
            'full_name' => 'Can Müşteri',
            'phone' => '05551234567',
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
            'line1' => 'Moda Cad. No: 10',
            'is_default' => true,
        ]);

        // Sepete ekle
        $addRes = $this->actingAs($customer)->post(route('cart.add', $product), [
            'quantity' => 2,
            'variant_id' => $variant->id,
        ]);
        $addRes->assertRedirect();

        // Checkout yap
        $checkoutRes = $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_address_id' => $address->id,
            'use_shipping_for_billing' => 1,
            'payment_method' => 'bank_transfer',
            'accept_distance_sales' => 1,
            'bank_iban' => 'TR123456789012345678901234',
            'invoice_type' => 'individual',
            'invoice_full_name' => 'Can Müşteri',
            'invoice_email' => 'can@example.com',
            'invoice_phone' => '05551234567',
        ]);
        $checkoutRes->assertRedirect(route('account.orders.index'));

        // Stok 50'den 48'e düştü mü?
        $this->assertEquals(48, $variant->fresh()->stock);

        // Sipariş oluştu mu?
        $createdOrder = Order::where('user_id', $customer->id)->latest()->first();
        $this->assertNotNull($createdOrder);
        $this->assertEquals(700.00, (float) $createdOrder->subtotal); // 2 * 350
        $this->assertEquals(OrderStatus::CONFIRMED, $createdOrder->status);
    }
}
