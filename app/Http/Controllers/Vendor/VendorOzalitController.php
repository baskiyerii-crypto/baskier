<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class VendorOzalitController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor || ! $vendor->hasActiveOzalitModule()) {
            abort(403, 'Ozalit modülü aktif değil.');
        }

        $categoryIds = $vendor->quoteCategories()->pluck('categories.id');
        $requests = QuoteRequest::query()
            ->when(
                Schema::hasColumn('quote_requests', 'request_type'),
                fn ($q) => $q->where('request_type', 'ozalit'),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->where('status', 'open')
            ->when($categoryIds->isNotEmpty(), fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->with(['category', 'user', 'quotes' => fn ($q) => $q->where('vendor_id', $vendor->id)])
            ->latest()
            ->paginate(20);

        return view('vendor.ozalit.index', compact('vendor', 'requests'));
    }
}
