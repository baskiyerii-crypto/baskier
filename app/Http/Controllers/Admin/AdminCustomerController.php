<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::query()
            ->where('role', 'customer')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term, $request) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                    if (Schema::hasColumn('users', 'public_id')) {
                        $inner->orWhere('public_id', 'like', '%'.preg_replace('/\D/', '', (string) $request->q).'%');
                    }
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        abort_unless($customer->role === 'customer', 404);
        $orders = Order::where('user_id', $customer->id)->with('vendor')->latest()->paginate(15, ['*'], 'orders_page');
        $quotes = QuoteRequest::where('user_id', $customer->id)->latest()->paginate(10, ['*'], 'quotes_page');

        return view('admin.customers.show', compact('customer', 'orders', 'quotes'));
    }

    public function toggleActive(User $customer)
    {
        abort_unless($customer->role === 'customer', 404);
        if (! Schema::hasColumn('users', 'is_active')) {
            return back()->with('error', 'is_active kolonu henüz migrate edilmedi.');
        }
        $customer->update(['is_active' => ! $customer->is_active]);

        return back()->with('success', __('panel.customer_updated'));
    }
}
