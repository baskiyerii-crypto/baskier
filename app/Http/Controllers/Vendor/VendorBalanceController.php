<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\BalanceTopUpService;
use App\Services\IyzicoClient;
use App\Services\ShopierClient;
use Illuminate\Http\Request;
use RuntimeException;

class VendorBalanceController extends Controller
{
    public function __construct(
        private BalanceTopUpService $topUp,
        private IyzicoClient $iyzico,
        private ShopierClient $shopier
    ) {}

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
        $transactions = $vendor->balanceTransactions()->latest()->paginate(20);
        $meetingFee = Setting::meetingFee();
        $isOutdoorPanel = $request->routeIs('outdoor-panel.*');
        $layout = $isOutdoorPanel ? 'layouts.outdoor' : 'layouts.vendor';
        $iyzicoReady = $this->iyzico->isConfigured();
        $shopierReady = $this->shopier->isConfigured();

        return view('vendor.balance.index', compact(
            'vendor',
            'transactions',
            'meetingFee',
            'isOutdoorPanel',
            'layout',
            'iyzicoReady',
            'shopierReady'
        ));
    }

    public function topUp(Request $request)
    {
        $vendor = $this->getVendor($request);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10', 'max:10000'],
            'provider' => ['required', 'in:iyzico,shopier'],
        ]);

        try {
            $started = $this->topUp->start(
                $vendor,
                $request->user(),
                (float) $validated['amount'],
                $validated['provider']
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! empty($started['form'])) {
            return view('vendor.balance.shopier-redirect', [
                'form' => $started['form'],
                'layout' => $request->routeIs('outdoor-panel.*') ? 'layouts.outdoor' : 'layouts.vendor',
            ]);
        }

        if (! empty($started['checkoutFormContent'])) {
            return view('checkout.iyzico', ['checkoutFormContent' => $started['checkoutFormContent']]);
        }

        if (! empty($started['redirect'])) {
            return redirect()->away($started['redirect']);
        }

        return back()->with('error', 'Ödeme sayfası açılamadı. Lütfen tekrar deneyin.');
    }
}
