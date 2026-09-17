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

        $documents = $vendor->documents()->latest()->get();
        $criteria = $this->trustBadgeService->getCriteriaBreakdown($vendor);

        return view('vendor.documents.index', compact('vendor', 'documents', 'criteria'));
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403);
        }

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'in:tax_plate,company_registration,certificate,diploma,portfolio_accreditation,course,other'],
            'issuing_institution' => ['nullable', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:128'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:'.config('uploads.max_kb', 10240)],
        ]);

        if (in_array($validated['document_type'], ['tax_plate', 'company_registration'], true) && ! $vendor->hasPhysicalTrack()) {
            return back()->with('error', __('panel.tax_plate_only_physical') ?: 'Bu belge türü yalnızca fiziksel ürün / teklif sağlayan satıcılar içindir.');
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
