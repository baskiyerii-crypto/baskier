<?php
namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Jobs\CreateShipmentJob;
use App\Jobs\SendWhatsAppMessageJob;
use App\Mail\OtpMail;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Services\BasitKargoService;
use App\Services\EvolutionWhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AsyncIntegrationJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Butun dis API'leri devre disi birak (Not Configured durumuna getir)
        Setting::set('api_basitkargo_enabled', '0');
        Setting::set('api_evolution_enabled', '0');
    }

    // BasitKargoService

    public function test_basit_kargo_returns_not_configured_when_credentials_missing(): void
    {
        $result = (new BasitKargoService())->createShipment($this->makeOrder());
        $this->assertSame(BasitKargoService::STATUS_NOT_CONFIGURED, $result['status']);
    }

    public function test_basit_kargo_returns_retryable_on_5xx(): void
    {
        Setting::set('api_basitkargo_enabled', '1');
        Setting::set('basitkargo_api_key', 'test-key');
        Setting::set('basitkargo_base_url', 'https://fake-kargo.test');
        Http::fake(['fake-kargo.test/*' => Http::response(['error' => 'server error'], 503)]);
        $result = (new BasitKargoService())->createShipment($this->makeOrder());
        $this->assertSame(BasitKargoService::STATUS_RETRYABLE, $result['status']);
    }

    public function test_basit_kargo_returns_failed_on_4xx(): void
    {
        Setting::set('api_basitkargo_enabled', '1');
        Setting::set('basitkargo_api_key', 'test-key');
        Setting::set('basitkargo_base_url', 'https://fake-kargo.test');
        Http::fake(['fake-kargo.test/*' => Http::response(['error' => 'bad request'], 422)]);
        $result = (new BasitKargoService())->createShipment($this->makeOrder());
        $this->assertSame(BasitKargoService::STATUS_FAILED, $result['status']);
    }

    public function test_basit_kargo_returns_success_on_2xx(): void
    {
        Setting::set('api_basitkargo_enabled', '1');
        Setting::set('basitkargo_api_key', 'test-key');
        Setting::set('basitkargo_base_url', 'https://fake-kargo.test');
        Http::fake(['fake-kargo.test/*' => Http::response(['tracking_number' => 'BK-999', 'label_url' => 'https://fake-kargo.test/labels/BK-999.pdf'], 200)]);
        $result = (new BasitKargoService())->createShipment($this->makeOrder());
        $this->assertSame(BasitKargoService::STATUS_SUCCESS, $result['status']);
        $this->assertSame('BK-999', $result['tracking_number']);
    }

    // EvolutionWhatsAppService

    public function test_evolution_whatsapp_returns_not_configured_when_missing(): void
    {
        $result = (new EvolutionWhatsAppService())->sendTextMessage('905001234567', 'Test');
        $this->assertSame(EvolutionWhatsAppService::STATUS_NOT_CONFIGURED, $result['status']);
    }

    public function test_evolution_whatsapp_returns_success_on_2xx(): void
    {
        Setting::set('api_evolution_enabled', '1');
        Setting::set('evolution_base_url', 'https://fake-evo.test');
        Setting::set('evolution_api_key', 'evo-key');
        Setting::set('evolution_instance', 'inst1');
        Http::fake(['fake-evo.test/*' => Http::response(['key' => ['id' => 'msg123']], 200)]);
        $result = (new EvolutionWhatsAppService())->sendTextMessage('905001234567', 'Test');
        $this->assertSame(EvolutionWhatsAppService::STATUS_SUCCESS, $result['status']);
    }

    public function test_evolution_whatsapp_returns_retryable_on_5xx(): void
    {
        Setting::set('api_evolution_enabled', '1');
        Setting::set('evolution_base_url', 'https://fake-evo.test');
        Setting::set('evolution_api_key', 'evo-key');
        Setting::set('evolution_instance', 'inst1');
        Http::fake(['fake-evo.test/*' => Http::response([], 500)]);
        $result = (new EvolutionWhatsAppService())->sendTextMessage('905001234567', 'Test');
        $this->assertSame(EvolutionWhatsAppService::STATUS_RETRYABLE, $result['status']);
    }

    // Job dispatch

    public function test_create_shipment_job_is_pushed_to_queue(): void
    {
        Queue::fake();
        CreateShipmentJob::dispatch($this->makeOrder());
        Queue::assertPushed(CreateShipmentJob::class);
    }

    public function test_create_shipment_job_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new CreateShipmentJob($this->makeOrder()));
    }

    public function test_send_whatsapp_job_is_pushed_to_queue(): void
    {
        Queue::fake();
        SendWhatsAppMessageJob::dispatch('905001234567', 'Merhaba');
        Queue::assertPushed(SendWhatsAppMessageJob::class);
    }

    public function test_send_whatsapp_job_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new SendWhatsAppMessageJob('905001234567', 'msg'));
    }

    // OtpMail ShouldQueue

    public function test_otp_mail_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new OtpMail('123456'));
    }

    // Helpers

    private function makeOrder(): Order
    {
        $user   = User::factory()->create();
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);

        return Order::create([
            'order_number'     => 'BY-AIJT-' . uniqid(),
            'user_id'          => $user->id,
            'vendor_id'        => $vendor->id,
            'status'           => OrderStatus::PENDING_PAYMENT,
            'subtotal'         => 100.00,
            'commission_rate'  => 10.00,
            'commission_amount' => 10.00,
            'vendor_amount'    => 90.00,
        ]);
    }
}