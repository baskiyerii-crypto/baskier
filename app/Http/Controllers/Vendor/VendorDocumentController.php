<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorDocument;
use Illuminate\Http\Request;

class VendorDocumentController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403);
        }
        $documents = $vendor->documents()->latest()->get();

        return view('vendor.documents.index', compact('vendor', 'documents'));
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403);
        }

        $validated = $request->validate([
            'document_type' => ['required', 'in:tax_plate,certificate,diploma,course,other'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:'.config('uploads.max_kb', 12288)],
        ]);

        if ($validated['document_type'] === 'tax_plate' && ! $vendor->hasPhysicalTrack()) {
            return back()->with('error', __('panel.tax_plate_only_physical'));
        }

        $path = $request->file('file')->store('vendor-documents/'.$vendor->id, 'public');

        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => $validated['document_type'],
            'path' => $path,
            'status' => 'pending',
        ]);

        if ($validated['document_type'] === 'tax_plate') {
            $vendor->update(['verification_status' => 'pending']);
        }

        return back()->with('success', __('panel.document_uploaded'));
    }
}
