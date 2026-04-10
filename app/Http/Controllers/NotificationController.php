<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($this->belongsToCurrentUser($request, $notification), 403);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()?->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        return back()->with('success', 'Semua notifikasi sudah dibaca.');
    }

    protected function belongsToCurrentUser(Request $request, DatabaseNotification $notification): bool
    {
        $user = $request->user();

        return $user !== null
            && $notification->notifiable_type === $user::class
            && (int) $notification->notifiable_id === (int) $user->getKey();
    }
}
