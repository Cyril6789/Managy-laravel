<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /** Failed attempts allowed per e-mail + IP before a temporary lockout. */
    private const MAX_ATTEMPTS = 5;

    /** How long (seconds) a failed attempt keeps counting toward the lockout. */
    private const DECAY_SECONDS = 60;

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

        // Brute-force guard: refuse the attempt once too many failures piled up
        // for this e-mail + IP, and tell the user how long to wait.
        $this->ensureIsNotRateLimited($request);

        // Login is e-mail only: the société is then derived automatically from
        // the authenticated user — the user never has to choose one.
        $ok = Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $request->boolean('remember'),
        );

        if (! $ok) {
            RateLimiter::hit($this->throttleKey($request), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => __('Identifiant ou mot de passe incorrect.'),
            ]);
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey($request), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => __('Ce compte est désactivé.'),
            ]);
        }

        // Successful login clears the counter so a legitimate user is never
        // penalised for a few earlier typos.
        RateLimiter::clear($this->throttleKey($request));

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

    /**
     * Stop the request when the e-mail + IP pair has exhausted its allowed
     * attempts, surfacing the remaining lockout time to the user.
     */
    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('Trop de tentatives de connexion. Réessayez dans :seconds secondes.', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    /**
     * Rate-limit bucket scoped to the submitted e-mail and the client IP, so a
     * lockout targets a single credential guess instead of the whole IP.
     */
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }
}
