<?php

namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Domain\PaymentStatus;
use App\Models\DocumentRequirementTemplate;
use App\Models\OohRepresentation;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\PayoutRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Services\EarningsCreditService;
use App\Services\PanelNavBadgeService;
use App\Services\PayoutService;
use App\Services\ShopierClient;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PanelOpsFlowTest extends TestCase
{
    use RefreshDatabase;

    private const IBAN = 'TR330006100519786457841326';

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-09-18 12:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * @return array{0: User, 1: Vendor}
     */
    private function vendorUser(array $vendorAttrs = []): array
    {
        $user = User::factory()->create(['role' => 'vendor', 'is_active' => true]);
        $vendor = Vendor::factory()->create(array_merge([
            'user_id' => $user->id,
            'is_active' => true,
            'registration_tracks' => ['physical_products'],
            'balance' => 0,
        ], $vendorAttrs));
        $user->forceFill(['vendor_id' => $vendor->id])->save();

        return [$user, $vendor->fresh()];
    }

    /**
     * @return array{0: User, 1: Vendor}
     */
    private function outdoorUser(string $role, string $name, array $extra = []): array
    {
        return $this->vendorUser(array_merge([
            'name' => $name,
            'registration_tracks' => ['outdoor'],
            'outdoor_enabled' => true,
            'outdoor_expires_at' => now()->addMonth(),
            'outdoor_role' => $role,
            'owner_kind' => $role === Vendor::OUTDOOR_ROLE_OWNER ? Vendor::OWNER_KIND_COMPANY : null,
            'city' => $extra['city'] ?? 'İstanbul',
            'email' => $extra['email'] ?? strtolower(str_replace(' ', '', $name)).'@secret.test',
            'phone' => $extra['phone'] ?? '05551234567',
        ], $extra));
    }

    public function test_earnings_credit_once_then_payout_dates_and_sticky_admin_badge(): void
    {
        [$user, $vendor] = $this->vendorUser(['balance' => 0]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $order = Order::create([
            'order_number' => 'BY-EARN-1',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::CONFIRMED,
            'subtotal' => 200,
            'commission_rate' => 10,
            'commission_amount' => 20,
            'vendor_amount' => 180,
            'commission_ready_at' => now()->subDay(),
            'payout_approved' => false,
        ]);

        $credits = app(EarningsCreditService::class);
        $this->assertSame(1, $credits->creditReadyOrders());
        $this->assertFalse($credits->creditOrder($order->fresh()));
        $vendor->refresh();
        $this->assertEquals(180.0, (float) $vendor->balance);
        $this->assertNotNull($order->fresh()->balance_credited_at);
        $this->assertTrue((bool) $order->fresh()->payout_approved);
        $this->assertDatabaseHas('vendor_balance_transactions', [
            'vendor_id' => $vendor->id,
            'type' => 'hakedis',
            'reference_id' => $order->id,
        ]);

        $this->artisan('earnings:credit-ready')->assertSuccessful();
        $this->assertEquals(180.0, (float) $vendor->fresh()->balance);

        $payouts = app(PayoutService::class);
        $row = $payouts->request($vendor->fresh(), 50, self::IBAN, 'Test Hesap');
        $this->assertNotNull($row->requested_at);
        $this->assertNull($row->approved_at);
        $this->assertSame('manual', $row->source);
        $this->assertSame(self::IBAN, $vendor->fresh()->payout_iban);

        $badges = app(PanelNavBadgeService::class)->forUser($admin);
        $this->assertSame(1, $badges['admin_payouts']);
        $admin->unreadNotifications->markAsRead();
        $this->assertSame(1, app(PanelNavBadgeService::class)->forUser($admin->fresh())['admin_payouts']);

        $payouts->approve($row, 'Havale yapıldı, IBAN silinmesin');
        $this->assertNotNull($row->fresh()->approved_at);
        $this->assertSame(self::IBAN, $row->fresh()->iban);
        $this->assertEquals(130.0, (float) $vendor->fresh()->balance);
        $this->assertSame(0, app(PanelNavBadgeService::class)->forUser($admin->fresh())['admin_payouts']);
    }

    public function test_payout_rejects_weekend_and_below_minimum_auto_request_opens_once(): void
    {
        [$user, $vendor] = $this->vendorUser([
            'balance' => 80,
            'payout_iban' => self::IBAN,
            'payout_account_holder' => 'Oto Hesap',
        ]);
        Setting::set('payout_min_amount', '50');
        Setting::set('payout_auto_after_days', '7');
        $payouts = app(PayoutService::class);

        try {
            $payouts->request($vendor, 10, self::IBAN, 'Oto Hesap');
            $this->fail('Asgari tutarın altı kabul edilmemeli.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Asgari', $e->getMessage());
        }

        Carbon::setTestNow(Carbon::parse('2026-09-20 12:00:00'));
        try {
            $payouts->request($vendor->fresh(), 50, self::IBAN, 'Oto Hesap');
            $this->fail('Pazar günü manuel çekim açılamamalı.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('para çekme günü değil', $e->getMessage());
        }

        Carbon::setTestNow(Carbon::parse('2026-09-18 12:00:00'));
        $tx = $vendor->balanceTransactions()->create([
            'amount' => 80,
            'type' => 'hakedis',
            'reference_type' => 'order',
            'reference_id' => 1,
            'description' => 'eski hakedis',
            'balance_after' => 80,
        ]);
        $tx->forceFill([
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ])->save();

        $this->artisan('payouts:auto-request')->assertSuccessful();
        $this->assertSame(1, PayoutRequest::query()->where('vendor_id', $vendor->id)->where('source', 'auto')->count());
        $this->artisan('payouts:auto-request')->assertSuccessful();
        $this->assertSame(1, PayoutRequest::query()->where('vendor_id', $vendor->id)->count());
    }

    public function test_iyzico_topup_credits_wallet_without_touching_order_checkout(): void
    {
        [$user, $vendor] = $this->vendorUser();
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'order_number' => 'BY-KEEP-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::PENDING_PAYMENT,
            'payment_status' => PaymentStatus::PENDING,
            'subtotal' => 200,
            'commission_amount' => 20,
            'vendor_amount' => 180,
        ]);

        Setting::set('api_iyzico_enabled', '1');
        Setting::set('iyzico_mode', 'sandbox');
        Setting::set('iyzico_api_key', 'sandbox-test-api-key');
        Setting::set('iyzico_secret_key', 'sandbox-test-secret-key');
        Setting::set('iyzico_base_url', 'https://sandbox-api.iyzipay.com');

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'initialize')) {
                return Http::response([
                    'status' => 'success',
                    'token' => 'topup-token',
                    'paymentPageUrl' => 'https://sandbox-api.iyzipay.com/pay-page',
                ], 200);
            }

            return Http::response([
                'status' => 'success',
                'paymentStatus' => 'SUCCESS',
                'paymentId' => 'PAY-TOPUP-1',
                'currency' => 'TRY',
                'paidPrice' => '100.00',
            ], 200);
        });

        $this->actingAs($user)->post(route('vendor.balance.topup'), [
            'amount' => 100,
            'provider' => 'iyzico',
        ])->assertRedirect('https://sandbox-api.iyzipay.com/pay-page');

        $this->post(route('payment.iyzico.callback'), ['token' => 'topup-token'])
            ->assertRedirect(route('vendor.balance.index'));

        $this->assertEquals(100.0, (float) $vendor->fresh()->balance);
        $this->assertSame(OrderStatus::PENDING_PAYMENT, $order->fresh()->status);
        $this->assertSame(PaymentStatus::PENDING, $order->fresh()->payment_status);
        $this->assertDatabaseHas('payment_attempts', [
            'purpose' => 'balance_topup',
            'vendor_id' => $vendor->id,
            'status' => PaymentStatus::PAID,
        ]);

        $this->post(route('payment.iyzico.callback'), ['token' => 'topup-token']);
        $this->assertEquals(100.0, (float) $vendor->fresh()->balance);
    }

    public function test_shopier_topup_form_and_signed_callback_are_idempotent(): void
    {
        [$user, $vendor] = $this->vendorUser();
        Setting::set('api_shopier_enabled', '1');
        Setting::set('shopier_api_key', 'shopier-key');
        Setting::set('shopier_api_secret', 'shopier-secret');
        Setting::set('shopier_mode', 'test');

        $this->actingAs($user)->post(route('vendor.balance.topup'), [
            'amount' => 75,
            'provider' => 'shopier',
        ])->assertOk()->assertSee('shopier-form', false);

        $attempt = PaymentAttempt::query()->where('purpose', 'balance_topup')->latest('id')->firstOrFail();
        $payload = [
            'platform_order_id' => (string) $attempt->id,
            'status' => 'success',
            'random_nr' => 'rnd-test-1',
            'payment_id' => 'SHP-1',
        ];
        $payload['signature'] = app(ShopierClient::class)->verifyCallback($payload) ? 'x' : base64_encode(
            hash_hmac('sha256', $payload['random_nr'].$payload['platform_order_id'].$payload['status'], 'shopier-secret', true)
        );
        $this->assertTrue(app(ShopierClient::class)->verifyCallback($payload));

        $this->post(route('payment.shopier.callback'), $payload)
            ->assertRedirect(route('vendor.balance.index'));
        $this->assertEquals(75.0, (float) $vendor->fresh()->balance);

        $this->post(route('payment.shopier.callback'), $payload);
        $this->assertEquals(75.0, (float) $vendor->fresh()->balance);

        $fail = PaymentAttempt::create([
            'conversation_id' => 'BY-TOPUP-FAIL',
            'idempotency_key' => 'fail-1',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'provider' => 'shopier',
            'purpose' => 'balance_topup',
            'status' => PaymentStatus::PROCESSING,
            'amount' => 40,
            'currency' => 'TRY',
            'order_ids' => [],
        ]);
        $this->post(route('payment.shopier.callback'), [
            'platform_order_id' => (string) $fail->id,
            'status' => 'failure',
            'random_nr' => 'x',
            'signature' => 'x',
        ]);
        $this->assertEquals(75.0, (float) $vendor->fresh()->balance);
    }

    public function test_subscription_reminders_fire_once_for_seven_and_one_day_windows(): void
    {
        [$user, $vendor] = $this->vendorUser([
            'outdoor_enabled' => true,
            'registration_tracks' => ['physical_products', 'outdoor'],
            'outdoor_expires_at' => now()->addDays(7),
            'quotes_expires_at' => now()->addDay(),
        ]);

        $this->artisan('subscriptions:remind')->assertSuccessful();
        $this->artisan('subscriptions:remind')->assertSuccessful();

        $notes = $user->fresh()->notifications;
        $this->assertCount(2, $notes);
        $modules = $notes->map(fn ($n) => $n->data['meta']['module'] ?? null)->sort()->values()->all();
        $this->assertSame(['outdoor', 'quotes'], $modules);

        $this->actingAs($user)->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertViewHas('moduleEnds', function ($ends) {
                return $ends->has('outdoor') && $ends->has('quotes');
            });
    }

    public function test_admin_document_templates_drive_kyc_cta(): void
    {
        [$user, $vendor] = $this->vendorUser();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($user)->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Doğrulamaya başla');

        $this->actingAs($admin)->get(route('admin.document-requirements.index'))->assertOk();
        $this->actingAs($admin)->post(route('admin.document-requirements.store'), [
            'audience' => 'physical',
            'document_type' => 'insurance_policy',
            'label' => 'Sigorta poliçesi',
            'required' => '1',
            'requires_file' => '1',
            'sort_order' => 40,
        ])->assertRedirect();

        $this->assertDatabaseHas('document_requirement_templates', [
            'audience' => 'physical',
            'document_type' => 'insurance_policy',
            'required' => 1,
        ]);

        $this->actingAs($user)->get(route('vendor.documents.index'))
            ->assertOk()
            ->assertSee('Sigorta poliçesi')
            ->assertSee('Doğrulamaya başla');

        $tpl = DocumentRequirementTemplate::query()
            ->where('audience', 'physical')
            ->where('document_type', 'tax_plate')
            ->firstOrFail();
        $this->actingAs($admin)->put(route('admin.document-requirements.update', $tpl), [
            'label' => 'Vergi levhası (güncel)',
            'required' => '1',
            'requires_file' => '1',
            'is_active' => '1',
            'sort_order' => 10,
        ])->assertRedirect();
        $this->assertSame('Vergi levhası (güncel)', $tpl->fresh()->label);
    }

    public function test_outdoor_directory_connects_without_pii_and_dashboard_metrics(): void
    {
        [$ownerUser, $owner] = $this->outdoorUser(Vendor::OUTDOOR_ROLE_OWNER, 'Sahip Mecra', [
            'city' => 'İzmir',
            'email' => 'owner-hidden@secret.test',
            'phone' => '05550001111',
        ]);
        [$agencyUser, $agency] = $this->outdoorUser(Vendor::OUTDOOR_ROLE_AGENCY, 'Gizli Ajans', [
            'city' => 'Ankara',
            'email' => 'agency-hidden@secret.test',
            'phone' => '02120002222',
        ]);

        $this->actingAs($ownerUser)->get(route('outdoor-panel.directory'))
            ->assertOk()
            ->assertSee('Gizli Ajans')
            ->assertSee('Ankara')
            ->assertDontSee('agency-hidden@secret.test')
            ->assertDontSee('02120002222');

        $this->actingAs($ownerUser)->post(route('outdoor-panel.directory.connect'), [
            'vendor_id' => $agency->id,
        ])->assertRedirect();

        $row = OohRepresentation::query()->firstOrFail();
        $this->assertSame(OohRepresentation::STATUS_PENDING, $row->status);
        $this->assertSame(1, $agencyUser->fresh()->notifications()->count());

        $this->actingAs($agencyUser)->post(route('outdoor-panel.representations.accept', $row))
            ->assertRedirect();
        $this->assertSame(OohRepresentation::STATUS_ACTIVE, $row->fresh()->status);

        $this->actingAs($ownerUser)->get(route('outdoor-panel.directory'))
            ->assertSee('Bağlı')
            ->assertDontSee('agency-hidden@secret.test');

        $this->actingAs($ownerUser)->get(route('outdoor-panel.dashboard'))
            ->assertOk()
            ->assertViewHas('representationCount', 1)
            ->assertViewHas('monthRevenue');

        $this->actingAs($ownerUser)->get(route('outdoor-panel.payout-requests.index'))
            ->assertOk()
            ->assertSee('Talep tarihi');
    }

    public function test_customer_and_admin_dashboards_expose_extra_kpis(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        [$vendorUser, $vendor] = $this->vendorUser();
        Order::create([
            'order_number' => 'BY-CUST-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::CONFIRMED,
            'subtotal' => 80,
            'vendor_amount' => 70,
        ]);

        $this->actingAs($customer)->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Açık sipariş')
            ->assertSee('Açık teklif')
            ->assertSee('Mesaj');

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Çekim talepleri')
            ->assertSee('7 gün içinde biten modül')
            ->assertSee('KYC evrak kuyruğu');

        $this->actingAs($vendorUser)->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Bu ay net hakediş')
            ->assertSee('Cüzdan');
    }
}
