<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderQuestion;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Services\ModerationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CustomerQuestionController extends Controller
{
    public function products(Request $request)
    {
        if ($request->user()->isVendor()) {
            return redirect()->route('vendor.product-questions.index');
        }
        abort_unless($request->user()->isCustomer(), 403);

        $questions = ProductQuestion::query()
            ->where('customer_id', $request->user()->id)
            ->with(['product.images', 'vendor'])
            ->latest()
            ->paginate(20);

        return view('customer.questions.products', compact('questions'));
    }

    public function orders(Request $request)
    {
        if ($request->user()->isVendor()) {
            return redirect()->route('vendor.order-questions.index');
        }
        abort_unless($request->user()->isCustomer(), 403);

        $questions = OrderQuestion::query()
            ->where('customer_id', $request->user()->id)
            ->with(['order.items', 'vendor', 'replies'])
            ->latest()
            ->paginate(20);

        return view('customer.questions.orders', compact('questions'));
    }

    public function storeProduct(Request $request, Product $product, ModerationService $moderation, NotificationService $notifications)
    {
        abort_unless($request->user()->isCustomer(), 403);
        $validated = $request->validate(['question' => ['required', 'string', 'max:2000']]);
        $result = $moderation->moderateMessage($validated['question']);
        if ($result['blocked']) {
            return back()->with('error', $moderation->rejectionMessage());
        }

        $question = ProductQuestion::create([
            'product_id' => $product->id,
            'vendor_id' => $product->vendor_id,
            'customer_id' => $request->user()->id,
            'question' => $validated['question'],
            'status' => ProductQuestion::STATUS_OPEN,
        ]);

        if ($product->vendor?->user) {
            $notifications->notify(
                $product->vendor->user,
                'Ürün sorusu',
                $product->name.' için yeni soru.',
                ['type' => 'product_question'],
                route('vendor.product-questions.show', $question)
            );
        }

        return redirect()->route('products.show', $product)->with('success', 'Sorunuz satıcıya iletildi.');
    }

    public function storeOrder(Request $request, Order $order, ModerationService $moderation, NotificationService $notifications)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
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
            'vendor_id' => $order->vendor_id,
            'customer_id' => $request->user()->id,
            'asked_by' => 'customer',
            'subject' => $validated['subject'] ?: 'Sipariş sorusu',
            'status' => OrderQuestion::STATUS_OPEN,
        ]);
        $question->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'is_from_vendor' => false,
        ]);

        if ($order->vendor?->user) {
            $notifications->notify(
                $order->vendor->user,
                'Sipariş sorusu',
                ($validated['subject'] ?: 'Sipariş sorusu').': '.\Illuminate\Support\Str::limit($validated['body'], 80),
                ['type' => 'order_question'],
                route('vendor.order-questions.show', $question)
            );
        }

        return redirect()->route('account.orders.show', $order)->with('success', 'Sipariş sorunuz iletildi.');
    }

    public function replyOrder(Request $request, OrderQuestion $question, ModerationService $moderation)
    {
        abort_unless($question->customer_id === $request->user()->id, 403);
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $result = $moderation->moderateMessage($validated['body']);
        if ($result['blocked']) {
            return back()->with('error', $moderation->rejectionMessage());
        }

        $question->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'is_from_vendor' => false,
        ]);
        $question->update(['status' => OrderQuestion::STATUS_OPEN]);

        return redirect()->route('customer.order-questions.index')->with('success', 'Yanıt gönderildi.');
    }
}
