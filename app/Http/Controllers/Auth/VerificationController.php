<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/**
 * E-mail verification screens. Only reachable when saas.email_verification is
 * enabled; otherwise users are auto-verified at registration.
 */
class VerificationController extends Controller
{
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->fulfill();
        }

        return redirect()->route('dashboard')->with('success', 'Adresse e-mail confirmée.');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Un nouveau lien de vérification a été envoyé.');
    }
}
