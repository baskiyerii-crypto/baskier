<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Services\TrustBadgeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminVerificationQueueController extends Controller
{
    public function __construct(
        protected TrustBadgeService $trustBadgeService
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $search = $request->query('q');
        $type = $request->query('type');

        if (! Schema::hasTable('vendor_documents')) {
            $documents = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'expired' => 0, 'all' => 0];

            return view('admin.verifications.index', compact('documents', 'status', 'counts', 'search', 'type'));
        }

        $with = ['vendor.user'];
        if (Schema::hasColumn('vendor_documents', 'reviewed_by')) {
            $with[] = 'reviewer';
        }

        $query = VendorDocument::query()
            ->with($with)
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                if (Schema::hasColumn('vendor_documents', 'document_number')) {
                    $q->where('document_number', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('vendor_documents', 'issuing_institution')) {
                    $q->orWhere('issuing_institution', 'like', "%{$search}%");
                }
                $q->orWhereHas('vendor', fn ($vq) => $vq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($type) {
            $query->where('document_type', $type);
        }

        $today = Carbon::today();

        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
            if (Schema::hasColumn('vendor_documents', 'expires_at')) {
                $query->where(function ($q) use ($today) {
                    $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', $today);
                });
            }
        } elseif ($status === 'rejected') {
            $query->where('status', 'rejected');
        } elseif ($status === 'expired') {
            $query->where('status', 'approved');
            if (Schema::hasColumn('vendor_documents', 'expires_at')) {
                $query->whereNotNull('expires_at')->whereDate('expires_at', '<', $today);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $documents = $query->paginate(20)->withQueryString();

        $approvedQuery = VendorDocument::query()->where('status', 'approved');
        $expiredQuery = VendorDocument::query()->where('status', 'approved');
        if (Schema::hasColumn('vendor_documents', 'expires_at')) {
            $approvedQuery->where(fn ($q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', $today));
            $expiredQuery->whereNotNull('expires_at')->whereDate('expires_at', '<', $today);
        } else {
            $expiredQuery->whereRaw('1 = 0');
        }

        $counts = [
            'pending' => VendorDocument::query()->where('status', 'pending')->count(),
            'approved' => $approvedQuery->count(),
            'rejected' => VendorDocument::query()->where('status', 'rejected')->count(),
            'expired' => $expiredQuery->count(),
            'all' => VendorDocument::query()->count(),
        ];

        return view('admin.verifications.index', compact('documents', 'status', 'counts', 'search', 'type'));
    }

    public function approve(Request $request, VendorDocument $document)
    {
        $document->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        $vendor = $document->vendor;
        if ($vendor) {
            $this->trustBadgeService->recalculateAndSave($vendor);
        }

        return back()->with('success', 'Belge onaylandı. Satıcının güven seviyesi kurallara göre otomatik güncellendi.');
    }

    public function reject(Request $request, VendorDocument $document)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Ret gerekçesi yazılması zorunludur.',
            'rejection_reason.min' => 'Ret gerekçesi en az 5 karakter olmalıdır.',
        ]);

        $document->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $vendor = $document->vendor;
        if ($vendor) {
            $this->trustBadgeService->recalculateAndSave($vendor);
        }

        return back()->with('success', 'Belge reddedildi ve gerekçeli denetim kaydı oluşturuldu.');
    }

    public function suspendVendor(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'suspension_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'suspension_reason.required' => 'Askıya alma gerekçesi zorunludur.',
            'suspension_reason.min' => 'Gerekçe en az 5 karakter olmalıdır.',
        ]);

        $vendor->update([
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspended_by' => $request->user()->id,
            'suspension_reason' => $validated['suspension_reason'],
            'trust_level' => 0,
        ]);

        if ($vendor->user) {
            app(\App\Services\NotificationService::class)->notify(
                $vendor->user,
                'Hesabınız askıya alındı',
                $validated['suspension_reason'],
                ['type' => 'vendor_suspend'],
                route('vendor.dashboard')
            );
        }

        return back()->with('success', "Satıcı (#{$vendor->id}) gerekçeli olarak askıya alındı ve güven seviyesi sıfırlandı.");
    }

    public function unsuspendVendor(Request $request, Vendor $vendor)
    {
        $vendor->update([
            'is_suspended' => false,
            'suspended_at' => null,
            'suspended_by' => null,
            'suspension_reason' => null,
        ]);

        $this->trustBadgeService->recalculateAndSave($vendor);

        return back()->with('success', "Satıcının (#{$vendor->id}) askı durumu kaldırıldı ve hak ettiği güven seviyesi yeniden hesaplandı.");
    }
}
