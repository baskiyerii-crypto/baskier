<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorDocument;
use App\Models\VendorProfileChangeRequest;
use App\Services\FreelancerTierService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class AdminVendorUpdateController extends Controller
{
    public function index(Request $request)
    {
        $documents = Schema::hasTable('vendor_documents')
            ? VendorDocument::query()
                ->with(['vendor.user'])
                ->when($request->get('status', 'pending') !== 'all', fn ($q) => $q->where('status', $request->get('status', 'pending')))
                ->latest()
                ->paginate(20, ['*'], 'docs_page')
            : new LengthAwarePaginator([], 0, 20, 1, ['pageName' => 'docs_page']);

        $profileRequests = Schema::hasTable('vendor_profile_change_requests')
            ? VendorProfileChangeRequest::query()
                ->with(['vendor.user'])
                ->when($request->get('profile_status', 'pending') !== 'all', fn ($q) => $q->where('status', $request->get('profile_status', 'pending')))
                ->latest()
                ->paginate(20, ['*'], 'profile_page')
            : new LengthAwarePaginator([], 0, 20, 1, ['pageName' => 'profile_page']);

        return view('admin.vendor-updates.index', compact('documents', 'profileRequests'));
    }

    public function approveDocument(Request $request, VendorDocument $document)
    {
        $document->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
        $vendor = $document->vendor;
        if ($vendor) {
            app(\App\Services\TrustBadgeService::class)->recalculateAndSave($vendor);
            if ($vendor->user) {
                $docLabel = class_exists(\App\Support\UiLabels::class) ? \App\Support\UiLabels::documentType($document->document_type) : $document->document_type;
                app(\App\Services\NotificationService::class)->notify(
                    $vendor->user,
                    'Belgeniz Onaylandı',
                    "Yüklemiş olduğunuz '{$docLabel}' belgesi yönetici tarafından onaylandı.",
                    ['type' => 'document_approved', 'document_id' => $document->id],
                    route('vendor.documents.index')
                );
            }
        }

        return back()->with('success', __('panel.document_approved'));
    }

    public function rejectDocument(Request $request, VendorDocument $document)
    {
        $reason = $request->input('rejection_reason', 'Yönetici tarafından onaylanmadı.');
        $document->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
        $vendor = $document->vendor;
        if ($vendor) {
            app(\App\Services\TrustBadgeService::class)->recalculateAndSave($vendor);
            if ($vendor->user) {
                $docLabel = class_exists(\App\Support\UiLabels::class) ? \App\Support\UiLabels::documentType($document->document_type) : $document->document_type;
                app(\App\Services\NotificationService::class)->notify(
                    $vendor->user,
                    'Belgeniz Onaylanmadı',
                    "Yüklemiş olduğunuz '{$docLabel}' belgesi onaylanmadı. Gerekçe: {$reason}",
                    ['type' => 'document_rejected', 'document_id' => $document->id, 'reason' => $reason],
                    route('vendor.documents.index')
                );
            }
        }

        return back()->with('success', __('panel.document_rejected'));
    }

    public function approveProfile(VendorProfileChangeRequest $vendorProfileChangeRequest)
    {
        if ($vendorProfileChangeRequest->status !== 'pending') {
            return back()->with('error', __('panel.already_processed'));
        }

        $vendor = $vendorProfileChangeRequest->vendor;
        $payload = $vendorProfileChangeRequest->payload ?? [];
        $allowed = ['name', 'description', 'phone', 'email', 'country_code', 'city', 'district', 'address', 'logo', 'map_embed_url', 'map_lat', 'map_lng', 'social_links'];
        $update = array_intersect_key($payload, array_flip($allowed));
        $vendor->update($update);
        $vendor->update(['profile_pending_payload' => null]);
        $vendorProfileChangeRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        if ($vendor->user) {
            app(\App\Services\NotificationService::class)->notify(
                $vendor->user,
                'Profil Değişikliği Onaylandı',
                'Mağaza profil bilgileriniz yönetici tarafından onaylandı ve vitrininizde güncellendi.',
                ['type' => 'profile_approved'],
                route('vendor.profile.edit')
            );
        }

        return back()->with('success', __('panel.profile_change_approved'));
    }

    public function rejectProfile(Request $request, VendorProfileChangeRequest $vendorProfileChangeRequest)
    {
        $adminNote = $request->input('admin_note');
        $vendorProfileChangeRequest->update([
            'status' => 'rejected',
            'admin_note' => $adminNote,
            'reviewed_at' => now(),
        ]);
        $vendor = $vendorProfileChangeRequest->vendor;
        $vendor->update(['profile_pending_payload' => null]);

        if ($vendor->user) {
            $reasonText = $adminNote ? " Not: {$adminNote}" : '';
            app(\App\Services\NotificationService::class)->notify(
                $vendor->user,
                'Profil Değişikliği Onaylanmadı',
                'Gönderdiğiniz profil değişiklik talebi yönetici tarafından onaylanmadı.' . $reasonText,
                ['type' => 'profile_rejected', 'admin_note' => $adminNote],
                route('vendor.profile.edit')
            );
        }

        return back()->with('success', __('panel.profile_change_rejected'));
    }
}
