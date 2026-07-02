<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(30);

        return $this->ok($notifications);
    }

    public function markRead(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        if ($n->read_at === null) {
            $n->markAsRead();
        }

        return $this->ok(null, 'Okundu.');
    }
}
