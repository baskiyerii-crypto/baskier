<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorTabelaController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor || ! $vendor->hasActiveTabelaModule()) {
            abort(403, 'Tabela modülü aktif değil.');
        }

        $fee = Setting::tabelaMeetingFee();
        $conversations = Conversation::query()
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(20);

        return view('vendor.tabela.index', compact('vendor', 'fee', 'conversations'));
    }

    public function startMeeting(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor || ! $vendor->hasActiveTabelaModule()) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $fee = Setting::tabelaMeetingFee();
        if ($vendor->balance < $fee) {
            return back()->with('error', 'Görüşme ücreti için bakiye yetersiz.');
        }

        DB::transaction(function () use ($vendor, $fee, $validated) {
            $vendor->decrement('balance', $fee);
            $vendor->balanceTransactions()->create([
                'amount' => -$fee,
                'type' => 'tabela_meeting_fee',
                'reference_type' => 'meeting',
                'reference_id' => $validated['user_id'],
                'description' => 'Tabela görüşme ücreti',
                'balance_after' => $vendor->fresh()->balance,
            ]);

            Conversation::firstOrCreate(
                ['vendor_id' => $vendor->id, 'user_id' => $validated['user_id']],
                []
            );
        });

        return back()->with('success', 'Görüşme açıldı, ücret bakiyeden düşüldü.');
    }
}
