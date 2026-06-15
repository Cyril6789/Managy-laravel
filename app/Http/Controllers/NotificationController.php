<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function readAll(Request $request)
    {
        Auth::user()->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
