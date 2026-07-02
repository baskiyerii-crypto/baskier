<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorSubscriptionController extends Controller
{
    private function getVendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403, 'Satıcı hesabı bulunamadı.');
        }

        return $vendor;
    }

    public function index(Request $request)
    {
        $vendor = $this->getVendor($request);

        return view('vendor.subscriptions.index', [
            'vendor' => $vendor,
            'freelancerMonthlyFee' => Setting::freelancerMonthlyFee(),
            'quotesMonthlyFee' => Setting::quotesMonthlyFee(),
        ]);
    }

    public function activate(Request $request)
    {
        $vendor = $this->getVendor($request);
        $validated = $request->validate([
            'module' => ['required', 'in:freelancer,quotes'],
        ]);

        $module = $validated['module'];
        $fee = $module === 'freelancer'
            ? Setting::freelancerMonthlyFee()
            : Setting::quotesMonthlyFee();

        if ($vendor->balance < $fee) {
            return back()->with('error', 'Bakiye yetersiz. Önce bakiye yükleyin.');
        }

        DB::transaction(function () use ($vendor, $module, $fee) {
            $vendor->decrement('balance', $fee);

            $fieldEnabled = $module . '_enabled';
            $fieldExpires = $module . '_expires_at';
            $currentEnd = $vendor->{$fieldExpires};
            $start = $currentEnd && $currentEnd->isFuture() ? $currentEnd : now();

            $vendor->update([
                $fieldEnabled => true,
                $fieldExpires => $start->copy()->addMonth(),
            ]);

            $vendor->balanceTransactions()->create([
                'amount' => -$fee,
                'type' => 'subscription_fee',
                'reference_type' => 'subscription',
                'reference_id' => $vendor->id,
                'description' => $module . ' modülü aylık abonelik ücreti',
                'balance_after' => $vendor->fresh()->balance,
            ]);
        });

        return back()->with('success', 'Abonelik başarıyla yenilendi/aktif edildi.');
    }
}
