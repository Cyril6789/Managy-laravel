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
            return redirect()->route(Auth::user()->is_super_admin ? 'admin.dashboard' : 'dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Login is e-mail only: the société is then derived automatically from
        // the authenticated user — the user never has to choose one.
        $ok = Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $request->boolean('remember'),
        );

        if (! $ok) {
            throw ValidationException::withMessages([
                'email' => __('Identifiant ou mot de passe incorrect.'),
            ]);
        }

        if (! Auth::user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('Ce compte est désactivé.'),
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

        // Platform super-admin lands on the supervision area, not a société app.
        if (Auth::user()->is_super_admin) {
            return redirect()->intended(route('admin.dashboard'));
        }

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
