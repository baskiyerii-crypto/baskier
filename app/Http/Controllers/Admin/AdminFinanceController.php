<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\VendorBalanceTransaction;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminFinanceController extends Controller
{
    public function index(Request $request, AdminDashboardService $dashboard)
    {
        $metrics = $dashboard->metrics();
        $payments = Payment::query()->with('order')->latest()->paginate(20, ['*'], 'payments_page');
        $transactions = VendorBalanceTransaction::query()->with('vendor')->latest()->paginate(20, ['*'], 'tx_page');
        $orders = Order::query()->with(['user', 'vendor'])->latest()->paginate(20, ['*'], 'orders_page');

        return view('admin.finance.index', compact('metrics', 'payments', 'transactions', 'orders'));
    }

    public function updateExpenses(Request $request)
    {
        $validated = $request->validate([
            'platform_expenses' => ['required', 'numeric', 'min:0'],
        ]);
        Setting::set('platform_expenses', $validated['platform_expenses']);

        return back()->with('success', 'Platform giderleri güncellendi.');
    }

    public function export(): StreamedResponse
    {
        $filename = 'finans-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['order_number', 'vendor', 'subtotal', 'commission', 'status', 'created_at']);
            Order::with('vendor')->orderBy('id')->chunk(200, function ($chunk) use ($out) {
                foreach ($chunk as $order) {
                    fputcsv($out, [
                        $order->order_number,
                        $order->vendor?->name,
                        $order->subtotal,
                        $order->commission_amount,
                        $order->status,
                        optional($order->created_at)->toDateTimeString(),
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
