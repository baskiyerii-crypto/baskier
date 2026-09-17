<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderQuestion;
use App\Models\ProductQuestion;
use App\Services\ModerationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class VendorQuestionController extends Controller
{
    private function vendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        return $vendor;
    }

    public function products(Request $request)
    {
        $vendor = $this->vendor($request);
        $questions = ProductQuestion::query()
            ->where('vendor_id', $vendor->id)
            ->with(['product', 'customer'])
            ->latest()
            ->paginate(20);

        return view('vendor.questions.products', compact('vendor', 'questions'));
    }

    public function showProduct(Request $request, ProductQuestion $question)
    {
        abort_unless($question->vendor_id === $this->vendor($request)->id, 403);
        $question->load(['product', 'customer']);

        return view('vendor.questions.product-show', compact('question'));
    }

    public function answerProduct(Request $request, ProductQuestion $question, ModerationService $moderation, NotificationService $notifications)
    {
        abort_unless($question->vendor_id === $this->vendor($request)->id, 403);
        $validated = $request->validate(['answer' => ['required', 'string', 'max:4000']]);
        $result = $moderation->moderateMessage($validated['answer']);
        if ($result['blocked']) {
            return back()->with('error', $moderation->rejectionMessage());
        }

        $question->update([
            'answer' => $validated['answer'],
            'answered_at' => now(),
            'status' => ProductQuestion::STATUS_ANSWERED,
        ]);

        if ($question->customer) {
            $notifications->notify(
                $question->customer,
                'Ürün sorunuz yanıtlandı',
                $question->product?->name ?? 'Ürün',
                ['type' => 'product_question'],
                route('customer.product-questions.index')
            );
        }

        return back()->with('success', 'Yanıt kaydedildi.');
    }

    public function orders(Request $request)
    {
        $vendor = $this->vendor($request);
        $questions = OrderQuestion::query()
            ->where('vendor_id', $vendor->id)
            ->with(['order', 'customer'])
            ->latest()
            ->paginate(20);

        return view('vendor.questions.orders', compact('vendor', 'questions'));
    }

    public function showOrder(Request $request, OrderQuestion $question)
    {
        abort_unless($question->vendor_id === $this->vendor($request)->id, 403);
        $question->load(['order', 'customer', 'replies.user']);

        return view('vendor.questions.order-show', compact('question'));
    }

    public function replyOrder(Request $request, OrderQuestion $question, ModerationService $moderation, NotificationService $notifications)
    {
        abort_unless($question->vendor_id === $this->vendor($request)->id, 403);
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $result = $moderation->moderateMessage($validated['body']);
        if ($result['blocked']) {
            return back()->with('error', $moderation->rejectionMessage());
        }

        $question->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'is_from_vendor' => true,
        ]);
        $question->update(['status' => OrderQuestion::STATUS_ANSWERED]);

        if ($question->customer) {
            $notifications->notify(
                $question->customer,
                'Sipariş sorunuz yanıtlandı',
                '#'.($question->order?->order_number ?? ''),
                ['type' => 'order_question'],
                route('customer.order-questions.index')
            );
        }

        return back()->with('success', 'Yanıt gönderildi.');
    }

    public function storeOrder(Request $request, Order $order, ModerationService $moderation, NotificationService $notifications)
    {
        $vendor = $this->vendor($request);
        abort_unless($order->vendor_id === $vendor->id, 403);
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:2000'],
        ]);
        $result = $moderation->moderateMessage($validated['body']);
        if ($result['blocked']) {
            return back()->with('error', $moderation->rejectionMessage());
        }

        $question = OrderQuestion::create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'customer_id' => $order->user_id,
            'asked_by' => 'vendor',
            'subject' => $validated['subject'] ?: 'Sipariş sorusu',
            'status' => OrderQuestion::STATUS_OPEN,
        ]);
        $question->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'is_from_vendor' => true,
        ]);

        if ($order->user) {
            $notifications->notify(
                $order->user,
                'Satıcı sipariş sorusu sordu',
                '#'.$order->order_number,
                ['type' => 'order_question'],
                route('customer.order-questions.index')
            );
        }

        return back()->with('success', 'Sipariş sorusu iletildi.');
    }
}
