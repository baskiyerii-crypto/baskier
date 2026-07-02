<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\OrderStatus;
use App\Models\DesignApproval;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesignApprovalController extends ApiController
{
    public function index(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        return $this->ok($order->designApprovals()->latest('id')->get());
    }

    public function vendorStore(Request $request, Order $order, OrderWorkflowService $workflow)
    {
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

        return $this->ok($order->fresh()->load('designApprovals'), 'Tasarım yüklendi.', null, 201);
    }

    public function approve(Request $request, DesignApproval $designApproval, OrderWorkflowService $workflow)
    {
        $order = $designApproval->order;
        $this->authorize('designRespond', $order);

        if ($designApproval->status !== 'pending') {
            return $this->fail('Bu tasarım onayı artık işlem yapılamaz.', null, 422);
        }

        DB::transaction(function () use ($designApproval, $order, $workflow, $request) {
            $designApproval->update(['status' => 'approved']);
            $order->refresh();
            if ($order->status === OrderStatus::DESIGN_REVIEW && $workflow->canTransition($order, OrderStatus::IN_PRODUCTION)) {
                $workflow->transition($order, OrderStatus::IN_PRODUCTION, $request->user());
            }
        });

        return $this->ok($order->fresh()->load('designApprovals'), 'Tasarım onaylandı.');
    }

    public function revision(Request $request, DesignApproval $designApproval)
    {
        $order = $designApproval->order;
        $this->authorize('designRespond', $order);

        if ($designApproval->status !== 'pending') {
            return $this->fail('Bu tasarım onayı için revizyon istenemez.', null, 422);
        }

        $validated = $request->validate([
            'customer_feedback' => ['required', 'string', 'max:2000'],
        ]);

        $designApproval->update([
            'status' => 'revision_requested',
            'customer_feedback' => $validated['customer_feedback'],
        ]);

        return $this->ok($order->fresh()->load('designApprovals'), 'Revizyon talebi gönderildi.');
    }
}
