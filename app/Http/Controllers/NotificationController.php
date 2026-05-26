<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Redirect to the URL in the notification data if available
        $url = $notification->data['url'] ?? route('notifications.index');
        return redirect($url);
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function destroy(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();
        return back()->with('success', 'Notifikasi dihapus.');
    }

    public function destroyAll()
    {
        auth()->user()->notifications()->delete();
        return back()->with('success', 'Semua notifikasi dihapus.');
    }
}
