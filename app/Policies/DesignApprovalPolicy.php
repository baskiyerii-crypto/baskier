<?php

namespace App\Policies;

use App\Models\DesignApproval;
use App\Models\Order;
use App\Models\User;

class DesignApprovalPolicy
{
    public function view(User $user, DesignApproval $designApproval): bool
    {
        return app(OrderPolicy::class)->view($user, $designApproval->order);
    }

    public function upload(User $user, Order $order): bool
    {
        return app(OrderPolicy::class)->designAct($user, $order);
    }

    public function respond(User $user, DesignApproval $designApproval): bool
    {
        return app(OrderPolicy::class)->designRespond($user, $designApproval->order);
    }
}
