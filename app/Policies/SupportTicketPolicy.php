<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function view(User $user, SupportTicket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $ticket->user_id === $user->id;
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        return $this->view($user, $ticket);
    }
}
