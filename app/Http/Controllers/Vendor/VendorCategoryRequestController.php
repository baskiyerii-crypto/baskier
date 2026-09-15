<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\VendorCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class VendorCategoryRequestController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        $channel = $request->get('channel', 'physical_quote');
        $categories = Category::query()
            ->when(Schema::hasColumn('categories', 'channel'), fn ($q) => $q->where('channel', $channel))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $currentIds = $vendor->quoteCategories()->pluck('categories.id')->all();
        $pending = VendorCategoryRequest::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $vendor->load('quoteCategories');

        return view('vendor.categories.index', compact('vendor', 'categories', 'channel', 'currentIds', 'pending'));
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        $validated = $request->validate([
            'channel' => ['required', 'in:physical_quote,freelancer,tabela'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['exists:categories,id'],
        ]);

        VendorCategoryRequest::create([
            'vendor_id' => $vendor->id,
            'channel' => $validated['channel'],
            'category_ids' => array_values(array_unique($validated['category_ids'])),
            'status' => 'pending',
        ]);

        return back()->with('success', __('panel.category_request_sent'));
    }
}
