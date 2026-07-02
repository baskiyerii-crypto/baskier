<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'categories' => Category::count(),
            'business_types' => BusinessType::count(),
            'vendors' => Vendor::count(),
            'products' => Product::count(),
            'users' => User::count(),
            'orders' => Order::count(),
        ];
        $recentProducts = Product::with(['vendor', 'category'])->latest()->limit(10)->get();
        $recentOrders = Order::with(['user', 'vendor'])->latest()->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'recentProducts', 'recentOrders'));
    }
}
