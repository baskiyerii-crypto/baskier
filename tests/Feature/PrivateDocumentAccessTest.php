<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Services\DocumentStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PrivateDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    private DocumentStorageService $storageService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('public');
        $this->storageService = app(DocumentStorageService::class);
    }

    private function createVendor(): array
    {
        $user = User::factory()->create([
            'role' => 'vendor',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $vendor = Vendor::factory()->create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $user->forceFill(['vendor_id' => $vendor->id])->save();

        return [$user, $vendor];
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
    }

    public function test_owner_vendor_can_download_document_via_valid_signed_url(): void
    {
        [$owner, $vendor] = $this->createVendor();

        $path = 'vendor-documents/'.$vendor->id.'/vergi.pdf';
        Storage::disk('private')->put($path, '%PDF-1.4 sample content');

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'private',
            'file_path' => $path,
            'path' => $path,
            'original_filename' => 'vergi.pdf',
            'mime_type' => 'application/pdf',
            'status' => 'pending',
            'quarantine_status' => 'clean',
        ]);

        $signedUrl = $this->storageService->generateTemporaryDownloadUrl($doc, 10);

        $response = $this->actingAs($owner)->get($signedUrl);

        $response->assertStatus(200);
        $this->assertStringContainsString('%PDF-1.4 sample content', $response->streamedContent());
    }

    public function test_authorized_admin_can_download_document_via_valid_signed_url(): void
    {
        $admin = $this->createAdmin();
        [$owner, $vendor] = $this->createVendor();

        $path = 'vendor-documents/'.$vendor->id.'/diploma.pdf';
        Storage::disk('private')->put($path, '%PDF-1.4 admin diploma content');

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'diploma',
            'disk' => 'private',
            'file_path' => $path,
            'path' => $path,
            'original_filename' => 'diploma.pdf',
            'status' => 'pending',
            'quarantine_status' => 'clean',
        ]);

        $signedUrl = $this->storageService->generateTemporaryDownloadUrl($doc, 10);

        $response = $this->actingAs($admin)->get($signedUrl);

        $response->assertStatus(200);
        $this->assertStringContainsString('%PDF-1.4 admin diploma content', $response->streamedContent());
    }

    public function test_other_vendor_cannot_download_document_even_with_signed_url(): void
    {
        [$owner, $vendor] = $this->createVendor();
        [$otherUser, $otherVendor] = $this->createVendor();

        $path = 'vendor-documents/'.$vendor->id.'/secret.pdf';
        Storage::disk('private')->put($path, 'Confidential vendor tax data');

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'private',
            'file_path' => $path,
            'path' => $path,
            'status' => 'pending',
            'quarantine_status' => 'clean',
        ]);

        $signedUrl = $this->storageService->generateTemporaryDownloadUrl($doc, 10);

        // Accessing as a completely different vendor
        $response = $this->actingAs($otherUser)->get($signedUrl);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_download_document(): void
    {
        [$owner, $vendor] = $this->createVendor();
        $customer = User::factory()->create(['role' => 'customer']);

        $path = 'vendor-documents/'.$vendor->id.'/secret.pdf';
        Storage::disk('private')->put($path, 'Confidential data');

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'private',
            'file_path' => $path,
            'path' => $path,
            'status' => 'pending',
        ]);

        $signedUrl = $this->storageService->generateTemporaryDownloadUrl($doc, 10);

        $response = $this->actingAs($customer)->get($signedUrl);

        $response->assertStatus(403);
    }

    public function test_expired_signed_url_returns_403(): void
    {
        [$owner, $vendor] = $this->createVendor();

        $path = 'vendor-documents/'.$vendor->id.'/doc.pdf';
        Storage::disk('private')->put($path, 'PDF content');

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'private',
            'file_path' => $path,
            'path' => $path,
            'status' => 'pending',
        ]);

        // URL signed with past timestamp
        $expiredUrl = URL::temporarySignedRoute(
            'vendor.documents.download',
            now()->subMinute(),
            ['document' => $doc->id]
        );

        $response = $this->actingAs($owner)->get($expiredUrl);

        $response->assertStatus(403);
    }

    public function test_tampered_signature_returns_403(): void
    {
        [$owner, $vendor] = $this->createVendor();

        $path = 'vendor-documents/'.$vendor->id.'/doc.pdf';
        Storage::disk('private')->put($path, 'PDF content');

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'private',
            'file_path' => $path,
            'path' => $path,
            'status' => 'pending',
        ]);

        $validUrl = $this->storageService->generateTemporaryDownloadUrl($doc, 10);
        $tamperedUrl = $validUrl . 'tampered';

        $response = $this->actingAs($owner)->get($tamperedUrl);

        $response->assertStatus(403);
    }

    public function test_quarantined_document_blocks_access(): void
    {
        [$owner, $vendor] = $this->createVendor();

        $path = 'vendor-documents/'.$vendor->id.'/virus.pdf';
        Storage::disk('private')->put($path, 'Infected content');

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'private',
            'file_path' => $path,
            'path' => $path,
            'status' => 'pending',
            'quarantine_status' => 'quarantined',
        ]);

        $signedUrl = $this->storageService->generateTemporaryDownloadUrl($doc, 10);

        $response = $this->actingAs($owner)->get($signedUrl);

        $response->assertStatus(403);
    }

    public function test_dual_read_fallback_serves_legacy_public_file(): void
    {
        [$owner, $vendor] = $this->createVendor();

        $legacyPublicPath = 'vendor-documents/'.$vendor->id.'/legacy.pdf';
        // File only exists on public disk
        Storage::disk('public')->put($legacyPublicPath, '%PDF-1.4 legacy public file');

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'public',
            'path' => $legacyPublicPath,
            'status' => 'approved',
            'quarantine_status' => 'clean',
        ]);

        $signedUrl = $this->storageService->generateTemporaryDownloadUrl($doc, 10);

        $response = $this->actingAs($owner)->get($signedUrl);

        $response->assertStatus(200);
        $this->assertStringContainsString('%PDF-1.4 legacy public file', $response->streamedContent());
    }

    public function test_migration_command_copies_public_to_private_safely(): void
    {
        [$owner, $vendor] = $this->createVendor();

        $legacyPath = 'vendor-documents/'.$vendor->id.'/old_tax.pdf';
        Storage::disk('public')->put($legacyPath, '%PDF-1.4 legacy data to migrate');

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'public',
            'path' => $legacyPath,
            'status' => 'approved',
        ]);

        $this->artisan('documents:migrate-to-private')
            ->assertExitCode(0);

        $doc->refresh();
        $this->assertEquals('private', $doc->disk);
        $this->assertEquals($legacyPath, $doc->file_path);
        Storage::disk('private')->assertExists($legacyPath);

        // Verify public copy was not deleted prematurely during dual-read transition
        Storage::disk('public')->assertExists($legacyPath);
    }
}
