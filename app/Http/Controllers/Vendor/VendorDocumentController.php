<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorDocument;
use App\Services\DocumentStorageService;
use App\Services\TrustBadgeService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class VendorDocumentController extends Controller
{
    public function __construct(
        protected DocumentStorageService $storageService,
        protected TrustBadgeService $trustBadgeService
    ) {}

    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403);
        }

        $documents = \Illuminate\Support\Facades\Schema::hasTable('vendor_documents')
            ? $vendor->documents()->latest()->get()
            : collect();

        try {
            $criteria = $this->trustBadgeService->getCriteriaBreakdown($vendor);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('vendor_documents_criteria_failed', [
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage(),
            ]);
            $criteria = [
                'current_level' => (int) ($vendor->trust_level ?? 0),
                'is_suspended' => (bool) ($vendor->is_suspended ?? false),
                'suspension_reason' => $vendor->suspension_reason ?? null,
                'level_1' => ['passed' => false, 'email_verified' => false, 'phone_verified' => false],
                'level_2' => ['passed' => false, 'has_physical' => false, 'physical_doc_approved' => null, 'has_freelancer' => false, 'freelancer_doc_approved' => null],
                'level_3' => ['passed' => false, 'completed_orders_count' => 0, 'rating_average' => 0, 'total_orders_last_12m' => 0, 'problematic_orders' => 0, 'dispute_rate_percent' => 0, 'no_sanctions' => true],
            ];
        }

        $isOutdoorPanel = $request->routeIs('outdoor-panel.*');
        $layout = $isOutdoorPanel ? 'layouts.outdoor' : 'layouts.vendor';

        return view('vendor.documents.index', compact('vendor', 'documents', 'criteria', 'isOutdoorPanel', 'layout'));
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403);
        }

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'in:tax_plate,company_registration,certificate,diploma,portfolio_accreditation,course,outdoor_permit,municipality_authority,trade_registry,other'],
            'issuing_institution' => ['nullable', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:128'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:'.config('uploads.max_kb', 10240)],
        ]);

        if (in_array($validated['document_type'], ['tax_plate', 'company_registration'], true) && ! $vendor->hasPhysicalTrack() && ! $vendor->hasOutdoorTrack()) {
            return back()->with('error', __('panel.tax_plate_only_physical') ?: 'Bu belge türü yalnızca fiziksel ürün / teklif sağlayan satıcılar içindir.');
        }

        if ($validated['document_type'] === 'outdoor_permit' && ! $vendor->hasOutdoorTrack()) {
            return back()->with('error', 'Bu belge türü yalnızca açık hava satıcıları içindir.');
        }

        if (in_array($validated['document_type'], ['diploma', 'certificate', 'portfolio_accreditation'], true) && ! $vendor->hasFreelancerTrack()) {
            return back()->with('error', 'Bu belge türü yalnızca freelancer satıcılar içindir.');
        }

        try {
            $storageData = $this->storageService->storeUploadedDocument($request->file('file'), $vendor->id);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Belge yüklenirken bir sorun oluştu. Lütfen tekrar deneyin.');
        }

        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => $validated['document_type'],
            'issuing_institution' => $validated['issuing_institution'] ?? null,
            'document_number' => $validated['document_number'] ?? null,
            'issued_at' => $validated['issued_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'disk' => $storageData['disk'],
            'file_path' => $storageData['file_path'],
            'path' => $storageData['file_path'],
            'original_filename' => $storageData['original_filename'],
            'mime_type' => $storageData['mime_type'],
            'file_size' => $storageData['file_size'],
            'quarantine_status' => $storageData['quarantine_status'],
            'status' => 'pending',
        ]);

        if ($validated['document_type'] === 'tax_plate' && $vendor->verification_status !== 'verified') {
            $vendor->update(['verification_status' => 'pending']);
        }

        $this->trustBadgeService->recalculateAndSave($vendor);

        return back()->with('success', __('panel.document_uploaded') ?: 'Belgeniz başarıyla yüklendi ve inceleme kuyruğuna alındı.');
    }

    public function download(Request $request, VendorDocument $document)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'İmzanın geçerlilik süresi dolmuş veya bağlantı geçersiz.');
        }

        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $canAccess = $user->isAdmin()
            || ($user->isVendor() && ((int) $user->vendor_id === (int) $document->vendor_id || (int) $user->vendor?->id === (int) $document->vendor_id));

        if (! $canAccess) {
            abort(403, 'Bu belgeyi görüntüleme yetkiniz bulunmamaktadır.');
        }

        return $this->storageService->getFileResponse($document);
    }
}
