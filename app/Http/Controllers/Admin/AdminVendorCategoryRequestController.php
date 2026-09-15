<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorCategoryRequest;
use Illuminate\Http\Request;

class AdminVendorCategoryRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = VendorCategoryRequest::query()
            ->with(['vendor.user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.vendor-category-requests.index', compact('requests'));
    }

    public function approve(VendorCategoryRequest $vendorCategoryRequest)
    {
        if ($vendorCategoryRequest->status !== 'pending') {
            return back()->with('error', __('panel.already_processed'));
        }

        $vendor = $vendorCategoryRequest->vendor;
        $ids = $vendorCategoryRequest->category_ids ?? [];
        $existing = $vendor->quoteCategories()->pluck('categories.id')->all();
        $vendor->quoteCategories()->sync(array_values(array_unique(array_merge($existing, $ids))));

        $vendorCategoryRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        return back()->with('success', __('panel.category_request_approved'));
    }

    public function reject(Request $request, VendorCategoryRequest $vendorCategoryRequest)
    {
        if ($vendorCategoryRequest->status !== 'pending') {
            return back()->with('error', __('panel.already_processed'));
        }

        $vendorCategoryRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', __('panel.category_request_rejected'));
    }
}
