<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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
            'ozalitMonthlyFee' => Setting::ozalitMonthlyFee(),
            'outdoorMonthlyFee' => Setting::outdoorMonthlyFee(),
        ]);
    }

    public function activate(Request $request)
    {
        $vendor = $this->getVendor($request);
        $validated = $request->validate([
            'module' => ['required', 'in:freelancer,quotes,tabela,ozalit,outdoor'],
        ]);

        $module = $validated['module'];
        $fee = match ($module) {
            'freelancer' => Setting::freelancerMonthlyFee(),
            'quotes' => Setting::quotesMonthlyFee(),
            'tabela' => Setting::tabelaMonthlyFee(),
            'ozalit' => Setting::ozalitMonthlyFee(),
            'outdoor' => Setting::outdoorMonthlyFee(),
        };

        $labels = [
            'freelancer' => 'Freelancer',
            'quotes' => 'Teklif Verme',
            'tabela' => 'Tabela',
            'ozalit' => 'Ozalit',
            'outdoor' => 'Açık hava',
        ];

        try {
            DB::transaction(function () use ($vendor, $module, $fee, $labels) {
                $locked = $vendor->newQuery()->whereKey($vendor->id)->lockForUpdate()->first();

                $duplicate = $locked->balanceTransactions()
                    ->where('type', 'subscription_fee')
                    ->where('reference_type', 'subscription_'.$module)
                    ->where('created_at', '>=', now()->subSeconds(90))
                    ->exists();

                if ($duplicate) {
                    throw new RuntimeException('Bu modül için abonelik az önce işlendi. Lütfen bekleyin.');
                }

                if ((float) $locked->balance < (float) $fee) {
                    throw new RuntimeException('Bakiye yetersiz. Önce bakiye yükleyin.');
                }

                $locked->decrement('balance', $fee);

                $fieldEnabled = $module.'_enabled';
                $fieldExpires = $module.'_expires_at';
                $currentEnd = $locked->{$fieldExpires};
                $start = $currentEnd && $currentEnd->isFuture() ? $currentEnd : now();

                // Only touch the selected module columns.
                $locked->update([
                    $fieldEnabled => true,
                    $fieldExpires => $start->copy()->addMonth(),
                ]);

                $locked->balanceTransactions()->create([
                    'amount' => -$fee,
                    'type' => 'subscription_fee',
                    'reference_type' => 'subscription_'.$module,
                    'reference_id' => $locked->id,
                    'description' => ($labels[$module] ?? $module).' modülü aylık abonelik ücreti',
                    'balance_after' => $locked->fresh()->balance,
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', ($labels[$module] ?? $module).' aboneliği aktif edildi. Sadece bu modül ücretlendirildi.');
    }
}
