<?php

namespace App\Http\Controllers\Customer;

use App\Domain\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DesignApproval;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomerOrderDesignController extends Controller
{
    public function approve(Request $request, Order $order, DesignApproval $designApproval, OrderWorkflowService $workflow)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
        if (! $request->user()->can('designRespond', $order)) {
            abort(403);
        }
        if ((int) $designApproval->order_id !== (int) $order->id) {
            abort(404);
        }
        if ($designApproval->status !== 'pending') {
            return back()->with('error', 'Bu tasarım için onay işlemi yapılamaz.');
        }

        DB::transaction(function () use ($designApproval, $order, $workflow, $request) {
            $designApproval->update(['status' => 'approved']);
            $order->refresh();
            if ($order->status === OrderStatus::DESIGN_REVIEW && $workflow->canTransition($order, OrderStatus::IN_PRODUCTION)) {
                $workflow->transition($order, OrderStatus::IN_PRODUCTION, $request->user());
            }
        });

        return back()->with('success', 'Tasarım onaylandı.');
    }

    public function revision(Request $request, Order $order, DesignApproval $designApproval)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
        if (! $request->user()->can('designRespond', $order)) {
            abort(403);
        }
        if ((int) $designApproval->order_id !== (int) $order->id) {
            abort(404);
        }
        if ($designApproval->status !== 'pending') {
            return back()->with('error', 'Bu kayıt için revizyon istenemez.');
        }

        $validated = $request->validate([
            'customer_feedback' => ['required', 'string', 'max:2000'],
        ]);

        $designApproval->update([
            'status' => 'revision_requested',
            'customer_feedback' => $validated['customer_feedback'],
        ]);

        return back()->with('success', 'Revizyon talebiniz satıcıya iletildi.');
    }

    public function file(Request $request, Order $order, DesignApproval $designApproval)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
        if ((int) $designApproval->order_id !== (int) $order->id) {
            abort(404);
        }
        if (! $designApproval->design_file_path || ! Storage::disk('public')->exists($designApproval->design_file_path)) {
            abort(404, 'Prova dosyası bulunamadı.');
        }

        return Storage::disk('public')->response($designApproval->design_file_path);
    }
}
