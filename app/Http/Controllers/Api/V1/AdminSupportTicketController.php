<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SupportTicket;
use Illuminate\Http\Request;

class AdminSupportTicketController extends ApiController
{
    public function index(Request $request)
    {
        $q = SupportTicket::with(['user'])->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        return $this->ok($q->paginate(30));
    }

    public function show(Request $request, SupportTicket $supportTicket)
    {
        $supportTicket->load(['user', 'messages.user', 'assignedAdmin']);

        return $this->ok($supportTicket);
    }

    public function updateStatus(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,pending,closed'],
        ]);
        $supportTicket->update(['status' => $validated['status']]);

        return $this->ok($supportTicket->fresh(), 'Durum güncellendi.');
    }

    public function assign(Request $request, SupportTicket $supportTicket)
    {
        $supportTicket->update(['assigned_admin_id' => $request->user()->id]);

        return $this->ok($supportTicket->fresh(), 'Talep size atandı.');
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $supportTicket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => '[Yönetici] '.$validated['body'],
        ]);

        return $this->ok($supportTicket->fresh()->load('messages.user'), 'Yanıt gönderildi.', null, 201);
    }
}
