<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationWebController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        $layout = $this->layoutFor($request);

        return view('notifications.index', compact('notifications', 'layout'));
    }

    public function open(Request $request, string $id)
    {
        /** @var DatabaseNotification $notification */
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $url = $notification->data['url'] ?? null;
        if (is_string($url) && $url !== '') {
            return redirect($url);
        }

        return redirect()->route('notifications.index');
    }

    public function markAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Tüm bildirimler okundu.');
    }

    private function layoutFor(Request $request): string
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return 'layouts.admin';
        }
        if ($user->isVendor()) {
            return 'layouts.vendor';
        }

        return 'layouts.account';
    }
}
