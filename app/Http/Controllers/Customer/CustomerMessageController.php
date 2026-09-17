<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ModerationService;
use Illuminate\Http\Request;

class CustomerMessageController extends Controller
{
    public function index(Request $request)
    {
        $conversations = Conversation::where('user_id', $request->user()->id)
            ->with(['vendor', 'order'])
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('customer.messages.index', compact('conversations'));
    }

    public function show(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);
        $conversation->load(['vendor', 'messages.user']);

        return view('customer.messages.show', compact('conversation'));
    }

    public function store(Request $request, Conversation $conversation, ModerationService $moderation)
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $result = $moderation->moderateMessage($validated['body']);
        if ($result['blocked']) {
            return back()->with('error', $moderation->rejectionMessage());
        }

        $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'is_from_vendor' => false,
            'body' => $validated['body'],
            'display_body' => $result['display'],
            'blocked' => false,
            'moderation_flags' => $result['flags'],
        ]);
        $conversation->update(['last_message_at' => now()]);

        if ($conversation->vendor?->user) {
            app(\App\Services\NotificationService::class)->notify(
                $conversation->vendor->user,
                'Yeni mesaj',
                'Müşteriden yeni bir mesajınız var.',
                ['type' => 'message'],
                route('vendor.messages.show', $conversation)
            );
        }

        return back()->with('success', 'Mesaj gönderildi.');
    }
}
