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

class AdminDashboardController extends Controller
{
    public function index(AdminDashboardService $dashboard)
    {
        $metrics = $dashboard->metrics();
        $trend = $dashboard->revenueTrend(30);
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
        $riskyVendors = Vendor::query()->where('risk_band', 'risky')->orderBy('risk_score')->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'metrics', 'trend', 'recentProducts', 'recentOrders', 'riskyVendors'));
    }
}
