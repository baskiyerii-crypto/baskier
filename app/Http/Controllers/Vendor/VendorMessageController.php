<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class VendorMessageController extends Controller
{
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
        $conversations = Conversation::where('vendor_id', $vendor->id)->with(['user', 'order'])->withCount('messages')->orderByDesc('last_message_at')->paginate(20);
        return view('vendor.messages.index', compact('vendor', 'conversations'));
    }

    public function show(Request $request, Conversation $conversation)
    {
        $vendor = $this->getVendor($request);
        if ($conversation->vendor_id !== $vendor->id) {
            abort(403);
        }
        $conversation->load(['user', 'messages']);
        return view('vendor.messages.show', compact('conversation', 'vendor'));
    }

    public function store(Request $request, Conversation $conversation)
    {
        $vendor = $this->getVendor($request);
        if ($conversation->vendor_id !== $vendor->id) {
            abort(403);
        }
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        if (Message::containsRedirect($validated['body'])) {
            return back()->with('error', 'Mesajda link, telefon veya platform dışı yönlendirme kullanılamaz.');
        }
        $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'is_from_vendor' => true,
            'body' => $validated['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);
        return back()->with('success', 'Mesaj gönderildi.');
    }
}
