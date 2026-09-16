<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class CustomerSupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)->latest()->paginate(15);

        return view('customer.support.index', compact('tickets'));
    }

    public function create()
    {
        return view('customer.support.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'page_url' => ['nullable', 'string', 'max:500'],
            'page_path' => ['nullable', 'string', 'max:255'],
        ]);

        $body = $validated['body'];
        if (! empty($validated['page_path']) || ! empty($validated['page_url'])) {
            $ctx = trim(($validated['page_path'] ?? '').' '.($validated['page_url'] ?? ''));
            $body = "[Sayfa: {$ctx}]\n\n".$body;
        }

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'status' => 'open',
        ]);
        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $body,
        ]);

        return redirect()->route('account.support.show', $ticket)->with('success', 'Destek talebiniz oluşturuldu.');
    }

    public function show(Request $request, SupportTicket $supportTicket)
    {
        if ($supportTicket->user_id !== $request->user()->id) {
            abort(403);
        }
        $supportTicket->load(['messages.user']);

        return view('customer.support.show', compact('supportTicket'));
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        if ($supportTicket->user_id !== $request->user()->id) {
            abort(403);
        }
        if ($supportTicket->status === 'closed') {
            return back()->with('error', 'Kapatılmış talebe yanıt yazılamaz.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $supportTicket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Mesajınız kaydedildi.');
    }
}
