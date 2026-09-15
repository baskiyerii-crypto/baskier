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
            'tabelaMonthlyFee' => Setting::tabelaMonthlyFee(),
        ]);
    }

    public function activate(Request $request)
    {
        $vendor = $this->getVendor($request);
        $validated = $request->validate([
            'module' => ['required', 'in:freelancer,quotes,tabela'],
        ]);

        $module = $validated['module'];
        $fee = match ($module) {
            'freelancer' => Setting::freelancerMonthlyFee(),
            'quotes' => Setting::quotesMonthlyFee(),
            'tabela' => Setting::tabelaMonthlyFee(),
        };

        if ($vendor->balance < $fee) {
            return back()->with('error', 'Bakiye yetersiz. Önce bakiye yükleyin.');
        }

        $recent = $vendor->balanceTransactions()
            ->where('type', 'subscription_fee')
            ->where('description', $module.' modülü aylık abonelik ücreti')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->exists();

        if ($recent) {
            return back()->with('error', 'Bu modül için abonelik az önce işlendi. Lütfen birkaç saniye bekleyin.');
        }

        DB::transaction(function () use ($vendor, $module, $fee) {
            $locked = $vendor->newQuery()->whereKey($vendor->id)->lockForUpdate()->first();

            $duplicate = $locked->balanceTransactions()
                ->where('type', 'subscription_fee')
                ->where('description', $module.' modülü aylık abonelik ücreti')
                ->where('created_at', '>=', now()->subSeconds(60))
                ->exists();

            if ($duplicate) {
                return;
            }

            if ($locked->balance < $fee) {
                return;
            }

            $locked->decrement('balance', $fee);

            $fieldEnabled = $module.'_enabled';
            $fieldExpires = $module.'_expires_at';
            $currentEnd = $locked->{$fieldExpires};
            $start = $currentEnd && $currentEnd->isFuture() ? $currentEnd : now();

            $locked->update([
                $fieldEnabled => true,
                $fieldExpires => $start->copy()->addMonth(),
            ]);

            $locked->balanceTransactions()->create([
                'amount' => -$fee,
                'type' => 'subscription_fee',
                'reference_type' => 'subscription',
                'reference_id' => $locked->id,
                'description' => $module.' modülü aylık abonelik ücreti',
                'balance_after' => $locked->fresh()->balance,
            ]);
        });

        return back()->with('success', 'Abonelik başarıyla yenilendi/aktif edildi.');
    }
}
