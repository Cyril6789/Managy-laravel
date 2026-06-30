<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    /**
     * Public entry point. Visitors see the marketing landing page; authenticated
     * users are sent to the right home (business dashboard or admin supervision).
     */
    public function home()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->is_super_admin ? 'admin.dashboard' : 'dashboard');
        }

        return view('marketing.landing');
    }
}
