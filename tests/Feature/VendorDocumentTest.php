<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('public');
    }

    private function createVendor(array $userAttrs = [], array $vendorAttrs = []): array
    {
        $user = User::factory()->create(array_merge([
            'role' => 'vendor',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ], $userAttrs));

        $vendor = Vendor::factory()->create(array_merge([
            'user_id' => $user->id,
            'registration_tracks' => ['physical_products', 'physical_quote'],
            'is_active' => true,
        ], $vendorAttrs));

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

    public function test_vendor_can_view_documents_and_verification_page(): void
    {
        [$user, $vendor] = $this->createVendor();

        $response = $this->actingAs($user)->get(route('vendor.documents.index'));

        $response->assertStatus(200);
        $response->assertSee('Belgeler ve Doğrulama');
        $response->assertSee('Güven ve Doğrulama Durumu');
        $response->assertSee('Yeni Belge Yükle');
    }

    public function test_vendor_can_upload_valid_document_to_private_storage(): void
    {
        [$user, $vendor] = $this->createVendor();

        $file = UploadedFile::fake()->createWithContent('vergi_levhasi.pdf', "%PDF-1.4\nTest PDF content");

        $response = $this->actingAs($user)->post(route('vendor.documents.store'), [
            'document_type' => 'tax_plate',
            'issuing_institution' => 'Gelir İdaresi Başkanlığı',
            'document_number' => 'VRG-2026-9988',
            'issued_at' => '2026-01-15',
            'expires_at' => '2027-01-15',
            'file' => $file,
        ]);

        $response->assertSessionHas('success');

        $document = VendorDocument::where('vendor_id', $vendor->id)->first();
        $this->assertNotNull($document);
        $this->assertEquals('tax_plate', $document->document_type);
        $this->assertEquals('Gelir İdaresi Başkanlığı', $document->issuing_institution);
        $this->assertEquals('VRG-2026-9988', $document->document_number);
        $this->assertEquals('private', $document->disk);
        $this->assertEquals('clean', $document->quarantine_status);
        $this->assertEquals('pending', $document->status);

        // Verify stored in private storage disk
        Storage::disk('private')->assertExists($document->file_path);
    }

    public function test_upload_rejects_disallowed_file_types(): void
    {
        [$user, $vendor] = $this->createVendor();

        $dangerousFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($user)->post(route('vendor.documents.store'), [
            'document_type' => 'tax_plate',
            'file' => $dangerousFile,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('vendor_documents', 0);
    }

    public function test_physical_track_document_cannot_be_uploaded_by_pure_freelancer(): void
    {
        [$user, $vendor] = $this->createVendor([], [
            'registration_tracks' => ['freelancer'],
        ]);

        $file = UploadedFile::fake()->create('vergi.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->post(route('vendor.documents.store'), [
            'document_type' => 'tax_plate',
            'file' => $file,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('vendor_documents', 0);
    }

    public function test_freelancer_track_document_cannot_be_uploaded_by_pure_physical_vendor(): void
    {
        [$user, $vendor] = $this->createVendor([], [
            'registration_tracks' => ['physical_products'],
        ]);

        $file = UploadedFile::fake()->create('diploma.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->post(route('vendor.documents.store'), [
            'document_type' => 'diploma',
            'file' => $file,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('vendor_documents', 0);
    }

    public function test_admin_verification_queue_lists_documents(): void
    {
        $admin = $this->createAdmin();
        [$user, $vendor] = $this->createVendor();

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'private',
            'file_path' => 'vendor-documents/'.$vendor->id.'/doc.pdf',
            'path' => 'vendor-documents/'.$vendor->id.'/doc.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.verifications.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee($vendor->name);
        $response->assertSee('Vergi levhası');
    }

    public function test_admin_can_approve_document_with_audit_trail(): void
    {
        $admin = $this->createAdmin();
        [$user, $vendor] = $this->createVendor();

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'private',
            'file_path' => 'vendor-documents/'.$vendor->id.'/doc.pdf',
            'path' => 'vendor-documents/'.$vendor->id.'/doc.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.verifications.approve', $doc));

        $response->assertSessionHas('success');

        $doc->refresh();
        $this->assertEquals('approved', $doc->status);
        $this->assertEquals($admin->id, $doc->reviewed_by);
        $this->assertNotNull($doc->reviewed_at);
        $this->assertNull($doc->rejection_reason);
    }

    public function test_admin_rejection_requires_mandatory_reason(): void
    {
        $admin = $this->createAdmin();
        [$user, $vendor] = $this->createVendor();

        $doc = VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'disk' => 'private',
            'file_path' => 'vendor-documents/'.$vendor->id.'/doc.pdf',
            'path' => 'vendor-documents/'.$vendor->id.'/doc.pdf',
            'status' => 'pending',
        ]);

        // Missing reason
        $responseNoReason = $this->actingAs($admin)->post(route('admin.verifications.reject', $doc), [
            'rejection_reason' => '',
        ]);
        $responseNoReason->assertSessionHasErrors('rejection_reason');
        $this->assertEquals('pending', $doc->fresh()->status);

        // Valid reason
        $responseValid = $this->actingAs($admin)->post(route('admin.verifications.reject', $doc), [
            'rejection_reason' => 'Yüklenen belgenin son geçerlilik tarihi geçmiş, güncel vergi levhası yükleyiniz.',
        ]);
        $responseValid->assertSessionHas('success');

        $doc->refresh();
        $this->assertEquals('rejected', $doc->status);
        $this->assertEquals($admin->id, $doc->reviewed_by);
        $this->assertNotNull($doc->reviewed_at);
        $this->assertStringContainsString('güncel vergi levhası', $doc->rejection_reason);
    }

    public function test_admin_can_suspend_and_unsuspend_vendor_with_audit_reason(): void
    {
        $admin = $this->createAdmin();
        [$user, $vendor] = $this->createVendor();

        // Suspend
        $responseSuspend = $this->actingAs($admin)->post(route('admin.verifications.suspend', $vendor), [
            'suspension_reason' => 'Şüpheli aktivite ve müşteri şikayetleri nedeniyle askıya alındı.',
        ]);
        $responseSuspend->assertSessionHas('success');

        $vendor->refresh();
        $this->assertTrue($vendor->is_suspended);
        $this->assertEquals($admin->id, $vendor->suspended_by);
        $this->assertEquals(0, $vendor->trust_level);

        // Unsuspend
        $responseUnsuspend = $this->actingAs($admin)->post(route('admin.verifications.unsuspend', $vendor));
        $responseUnsuspend->assertSessionHas('success');

        $vendor->refresh();
        $this->assertFalse($vendor->is_suspended);
        $this->assertNull($vendor->suspension_reason);
    }
}
