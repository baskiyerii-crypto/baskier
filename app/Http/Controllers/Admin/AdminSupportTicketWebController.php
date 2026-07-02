<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class AdminSupportTicketWebController extends Controller
{
    public function index(Request $request)
    {
        $q = SupportTicket::with(['user'])->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        $tickets = $q->paginate(20)->withQueryString();

        return view('admin.support-tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load(['user', 'messages.user', 'assignedAdmin']);

        return view('admin.support-tickets.show', compact('supportTicket'));
    }

    public function updateStatus(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,pending,closed'],
        ]);
        $supportTicket->update(['status' => $validated['status']]);

        return back()->with('success', 'Talep durumu güncellendi.');
    }

    public function assign(Request $request, SupportTicket $supportTicket)
    {
        $supportTicket->update(['assigned_admin_id' => $request->user()->id]);

        return back()->with('success', 'Talep size atandı.');
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

        return back()->with('success', 'Yanıt gönderildi.');
    }
}
