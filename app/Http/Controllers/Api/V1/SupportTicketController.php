<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends ApiController
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return $this->ok($tickets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'status' => 'open',
        ]);
        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return $this->ok($ticket->load('messages'), 'Destek talebi oluşturuldu.', null, 201);
    }

    public function show(Request $request, SupportTicket $supportTicket)
    {
        $this->authorize('view', $supportTicket);
        $supportTicket->load('messages.user');

        return $this->ok($supportTicket);
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $this->authorize('reply', $supportTicket);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $supportTicket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return $this->ok($supportTicket->fresh()->load('messages.user'), 'Mesajınız kaydedildi.', null, 201);
    }
}
