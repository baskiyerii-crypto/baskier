<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        if ($conversation->user_id === $user->id) {
            return true;
        }
        if ($user->vendor_id && (int) $conversation->vendor_id === (int) $user->vendor_id) {
            return true;
        }

        return $user->isAdmin();
    }

    public function message(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
