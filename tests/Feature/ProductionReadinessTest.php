<?php
namespace Tests\Feature;

use App\Http\Middleware\CaptureCorrelationId;
use App\Models\User;
use App\Services\PlatformMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    // ─── 1. Readiness Probe (/ready) ─────────────

    public function test_ready_endpoint_returns_200_when_healthy(): void
    {
        $response = $this->getJson('/ready');

        $response->assertOk();
        $response->assertExactJson([
            'status' => 'ready',
        ]);
    }

    public function test_api_ready_endpoint_returns_200_when_healthy(): void
    {
        $response = $this->getJson('/api/ready');

        $response->assertOk();
        $response->assertExactJson([
            'status' => 'ready',
        ]);
    }

    // ─── 2. Correlation ID Propagation ───────────

    public function test_correlation_id_is_attached_to_response_header(): void
    {
        $response = $this->getJson('/ready');

        $response->assertOk();
        $this->assertTrue($response->headers->has(CaptureCorrelationId::HEADER_NAME));
        $this->assertNotEmpty($response->headers->get(CaptureCorrelationId::HEADER_NAME));
    }

    public function test_existing_correlation_id_header_is_preserved(): void
    {
        $customId = 'test-trace-id-12345';

        $response = $this->withHeaders([
            CaptureCorrelationId::HEADER_NAME => $customId,
        ])->getJson('/ready');

        $response->assertOk();
        $this->assertSame($customId, $response->headers->get(CaptureCorrelationId::HEADER_NAME));
    }

    // ─── 3. Rate Limiting ────────────────────────

    public function test_login_rate_limiter_throttles_excessive_attempts(): void
    {
        RateLimiter::clear('login');

        // Send 5 rapid login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/giris', [
                'email'    => 'attacker@example.com',
                'password' => 'wrong-pass',
            ]);
        }

        // 6th attempt should be throttled (HTTP 429)
        $throttledResponse = $this->postJson('/giris', [
            'email'    => 'attacker@example.com',
            'password' => 'wrong-pass',
        ]);

        $throttledResponse->assertStatus(429);
    }

    // ─── 4. Admin Failed Jobs Dashboard ──────────

    public function test_admin_can_view_failed_jobs_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Insert a simulated failed job
        DB::table('failed_jobs')->insert([
            'uuid'       => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue'      => 'default',
            'payload'    => json_encode(['displayName' => 'App\\Jobs\\SendWhatsAppMessageJob']),
            'exception'  => "RuntimeException: Connection timeout\nat /app/Jobs/SendWhatsAppMessageJob.php:45",
            'failed_at'  => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.failed-jobs.index'));

        $response->assertOk();
        $response->assertSee('Başarısız Kuyruk İşleri');
        $response->assertSee('SendWhatsAppMessageJob');
    }

    public function test_non_admin_cannot_view_failed_jobs_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get(route('admin.failed-jobs.index'));

        $response->assertForbidden();
    }

    // ─── 5. Platform Metrics Dashboard ───────────

    public function test_admin_can_view_metrics_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.metrics.index'));

        $response->assertOk();
        $response->assertSee('Platform Sağlık ve Operasyon Metrikleri');
        $response->assertSee('Ödeme Başarı Oranı');
    }

    public function test_platform_metrics_service_computes_expected_structure(): void
    {
        $service = app(PlatformMetricsService::class);
        $metrics = $service->getMetrics();

        $this->assertArrayHasKey('payments', $metrics);
        $this->assertArrayHasKey('queue', $metrics);
        $this->assertArrayHasKey('stock_reservations', $metrics);
        $this->assertArrayHasKey('verification', $metrics);
        $this->assertArrayHasKey('disaster_recovery', $metrics);

        $this->assertIsFloat($metrics['payments']['success_rate_24h']);
        $this->assertIsInt($metrics['queue']['pending_jobs']);
    }

    // ─── 6. Backup & Restore Rehearsal ───────────

    public function test_platform_backup_and_restore_verification_commands(): void
    {
        // 1. Create backup
        $exitCodeBackup = Artisan::call('platform:backup');
        $this->assertSame(0, $exitCodeBackup, 'platform:backup should succeed');

        // 2. Run synthetic restore rehearsal
        $exitCodeRestore = Artisan::call('platform:restore-verify');
        $this->assertSame(0, $exitCodeRestore, 'platform:restore-verify should succeed');

        // 3. Verify settings were updated
        $this->assertNotEmpty(\App\Models\Setting::get('last_backup_restore_verified_at'));
    }
}