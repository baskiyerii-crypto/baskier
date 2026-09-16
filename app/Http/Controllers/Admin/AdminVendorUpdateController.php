<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorDocument;
use App\Models\VendorProfileChangeRequest;
use App\Services\FreelancerTierService;
use Illuminate\Http\Request;

class AdminVendorUpdateController extends Controller
{
    public function index(Request $request)
    {
        $documents = VendorDocument::query()
            ->with(['vendor.user'])
            ->when($request->get('status', 'pending') !== 'all', fn ($q) => $q->where('status', $request->get('status', 'pending')))
            ->latest()
            ->paginate(20, ['*'], 'docs_page');

        $profileRequests = VendorProfileChangeRequest::query()
            ->with(['vendor.user'])
            ->when($request->get('profile_status', 'pending') !== 'all', fn ($q) => $q->where('status', $request->get('profile_status', 'pending')))
            ->latest()
            ->paginate(20, ['*'], 'profile_page');

        return view('admin.vendor-updates.index', compact('documents', 'profileRequests'));
    }

    public function approveDocument(Request $request, VendorDocument $document, FreelancerTierService $tiers)
    {
        $document->update(['status' => 'approved']);
        $vendor = $document->vendor;
        if ($document->document_type === 'tax_plate') {
            $vendor->update(['verification_status' => 'verified']);
        }
        $override = $request->input('freelancer_tier');
        $tiers->apply($vendor, $override ?: null);

        return back()->with('success', __('panel.document_approved'));
    }

    public function rejectDocument(VendorDocument $document)
    {
        $document->update(['status' => 'rejected']);

        return back()->with('success', __('panel.document_rejected'));
    }

    public function approveProfile(VendorProfileChangeRequest $vendorProfileChangeRequest)
    {
        if ($vendorProfileChangeRequest->status !== 'pending') {
            return back()->with('error', __('panel.already_processed'));
        }

        $vendor = $vendorProfileChangeRequest->vendor;
        $payload = $vendorProfileChangeRequest->payload ?? [];
        $allowed = ['name', 'description', 'phone', 'email', 'city', 'district', 'address', 'logo', 'map_embed_url', 'map_lat', 'map_lng', 'social_links'];
        $update = array_intersect_key($payload, array_flip($allowed));
        $vendor->update($update);
        $vendor->update(['profile_pending_payload' => null]);
        $vendorProfileChangeRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        return back()->with('success', __('panel.profile_change_approved'));
    }

    public function rejectProfile(Request $request, VendorProfileChangeRequest $vendorProfileChangeRequest)
    {
        $vendorProfileChangeRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
            'reviewed_at' => now(),
        ]);
        $vendorProfileChangeRequest->vendor->update(['profile_pending_payload' => null]);

        return back()->with('success', __('panel.profile_change_rejected'));
    }
}
