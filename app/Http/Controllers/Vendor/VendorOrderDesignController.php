<?php

namespace App\Http\Controllers\Vendor;

use App\Domain\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DesignApproval;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorOrderDesignController extends Controller
{
    public function store(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor || (int) $order->vendor_id !== (int) $vendor->id) {
            abort(403);
        }
        $this->authorize('designAct', $order);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,zip'],
            'vendor_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $path = $request->file('file')->store('designs/'.$order->id, 'public');

        DB::transaction(function () use ($request, $order, $workflow, $validated, $path) {
            $nextRound = (int) $order->designApprovals()->max('round') + 1;
            DesignApproval::create([
                'order_id' => $order->id,
                'round' => $nextRound,
                'status' => 'pending',
                'design_file_path' => $path,
                'vendor_note' => $validated['vendor_note'] ?? null,
                'uploaded_by_user_id' => $request->user()->id,
            ]);

            $order->refresh();
            if (in_array($order->status, [OrderStatus::CONFIRMED, OrderStatus::IN_PRODUCTION], true)
                && $workflow->canTransition($order, OrderStatus::DESIGN_REVIEW)) {
                $workflow->transition($order, OrderStatus::DESIGN_REVIEW, $request->user());
            }
        });

        return back()->with('success', 'Tasarım dosyası yüklendi; müşteri onayına gönderildi.');
    }
}
