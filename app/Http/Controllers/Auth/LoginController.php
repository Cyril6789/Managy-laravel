<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identifiant' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Identifier can be the pseudo (login handle) or the e-mail.
        $field = filter_var($credentials['identifiant'], FILTER_VALIDATE_EMAIL) ? 'email' : 'pseudo';

        $ok = Auth::attempt(
            [$field => $credentials['identifiant'], 'password' => $credentials['password']],
            $request->boolean('remember'),
        );

        if (! $ok) {
            throw ValidationException::withMessages([
                'identifiant' => __('Identifiant ou mot de passe incorrect.'),
            ]);
        }

        if (! Auth::user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'identifiant' => __('Ce compte est désactivé.'),
            ]);
        }

        $request->session()->regenerate();

        Auth::user()->forceFill(['last_action_at' => now()])->save();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'login',
            'description' => 'Connexion',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
