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
            'document_type' => ['required', 'in:tax_plate'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.config('uploads.max_kb', 5120)],
        ]);

        $path = $request->file('file')->store('vendor-documents/'.$vendor->id, 'public');

        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => $validated['document_type'],
            'path' => $path,
            'status' => 'pending',
        ]);

        $vendor->update(['verification_status' => 'pending']);

        return back()->with('success', 'Belge yüklendi. Yönetici onayı bekleniyor.');
    }
}
