<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Services\AdminDashboardService;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index(AdminDashboardService $dashboard)
    {
        $metrics = $dashboard->metrics();
        $trend = $dashboard->revenueTrend(30);
        $statusBreakdown = $dashboard->orderStatusBreakdown();
        $stats = [
            'categories' => Category::count(),
            'business_types' => BusinessType::count(),
            'vendors' => $metrics['vendors'],
            'products' => Product::count(),
            'users' => User::count(),
            'orders' => $metrics['orders'],
        ];
        $recentProducts = Product::with(['vendor', 'category'])->latest()->limit(10)->get();
        $recentOrders = Order::with(['user', 'vendor'])->latest()->limit(8)->get();
        $riskyVendors = collect();
        if (Schema::hasColumn('vendors', 'risk_band')) {
            $riskyVendors = Vendor::query()
                ->where('risk_band', 'risky')
                ->when(Schema::hasColumn('vendors', 'risk_score'), fn ($q) => $q->orderBy('risk_score'))
                ->limit(8)
                ->get();
        }

        $pendingProductApprovals = Schema::hasColumn('products', 'moderation_status')
            ? Product::where('moderation_status', 'pending')->count()
            : 0;
        $pendingCategoryRequests = Schema::hasTable('vendor_category_requests')
            ? \App\Models\VendorCategoryRequest::where('status', 'pending')->count()
            : 0;
        $pendingDocumentApprovals = Schema::hasTable('vendor_documents')
            ? \App\Models\VendorDocument::where('status', 'pending')->count()
            : 0;

        return view('admin.dashboard', compact(
            'stats',
            'metrics',
            'trend',
            'statusBreakdown',
            'recentProducts',
            'recentOrders',
            'riskyVendors',
            'pendingProductApprovals',
            'pendingCategoryRequests',
            'pendingDocumentApprovals'
        ));
    }
}
