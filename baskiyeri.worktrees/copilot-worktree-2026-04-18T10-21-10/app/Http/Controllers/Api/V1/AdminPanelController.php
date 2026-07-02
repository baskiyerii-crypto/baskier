<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;

class AdminPanelController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'stats' => [
                'categories' => Category::count(),
                'business_types' => BusinessType::count(),
                'vendors' => Vendor::count(),
                'products' => Product::count(),
                'users' => User::count(),
                'orders' => Order::count(),
            ],
            'message' => 'Tam yönetim için web üzerinden admin panelini kullanın.',
        ]);
    }
}
