<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Conversation;
use App\Models\Order;
use App\Services\ModerationService;
use Illuminate\Http\Request;

class ConversationController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->isVendor() && $user->vendor) {
            $q = Conversation::where('vendor_id', $user->vendor->id)->with(['user', 'order']);
        } else {
            $q = Conversation::where('user_id', $user->id)->with(['vendor', 'order']);
        }

        return $this->ok($q->orderByDesc('last_message_at')->paginate(20));
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $conversation->load(['messages.user', 'vendor', 'user', 'order']);

        return $this->ok($conversation);
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $moderation = app(ModerationService::class)->moderateMessage($validated['body']);
        $message = $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'is_from_vendor' => $request->user()->isVendor(),
            'body' => $validated['body'],
            'display_body' => $moderation['display'],
            'blocked' => $moderation['blocked'],
            'moderation_flags' => $moderation['flags'],
        ]);
        $conversation->update(['last_message_at' => now()]);

        return $this->ok($message, 'Mesaj gönderildi.', null, 201);
    }

    public function openOrCreateForOrder(Request $request, Order $order)
    {
        $this->authorize('view', $order);
        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $order->user_id,
                'vendor_id' => $order->vendor_id,
            ],
            ['order_id' => $order->id]
        );
        if ($conversation->order_id === null) {
            $conversation->update(['order_id' => $order->id]);
        }

        return $this->ok($conversation->load(['vendor', 'order']), 'Konuşma hazır.');
    }
}
