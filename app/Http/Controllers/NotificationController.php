<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function readAll(Request $request)
    {
        Auth::user()->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }

    /** Mark a single notification as read, then go to its target. */
    public function read(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->forceFill(['read_at' => $notification->read_at ?? now()])->save();

        return redirect($notification->url ?: route('dashboard'));
    }
}
