<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\OrderStatus;
use App\Http\Requests\Api\V1\DesignApprovalRevisionRequest;
use App\Http\Requests\Api\V1\DesignApprovalStoreRequest;
use App\Models\DesignApproval;
use App\Models\Order;
use App\Services\NotificationService;
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

    public function vendorStore(DesignApprovalStoreRequest $request, Order $order, OrderWorkflowService $workflow)
    {
        $validated = $request->validated();
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

        $order = $order->fresh()->load('designApprovals');
        if ($order->user) {
            app(NotificationService::class)->notify(
                $order->user,
                'Dijital prova yüklendi',
                "#{$order->order_number} siparişiniz için prova yüklendi. İnceleyip onaylayabilirsiniz.",
                ['type' => 'design_proof', 'order_id' => $order->id],
                route('account.orders.show', $order)
            );
        }

        return $this->ok($order, 'Tasarım yüklendi.', null, 201);
    }

    public function approve(Request $request, DesignApproval $designApproval, OrderWorkflowService $workflow)
    {
        $order = $designApproval->order;
        $this->authorize('respond', $designApproval);

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

    public function revision(DesignApprovalRevisionRequest $request, DesignApproval $designApproval)
    {
        $order = $designApproval->order;

        if ($designApproval->status !== 'pending') {
            return $this->fail('Bu tasarım onayı için revizyon istenemez.', null, 422);
        }

        $validated = $request->validated();

        $designApproval->update([
            'status' => 'revision_requested',
            'customer_feedback' => $validated['customer_feedback'],
        ]);

        return $this->ok($order->fresh()->load('designApprovals'), 'Revizyon talebi gönderildi.');
    }
}
