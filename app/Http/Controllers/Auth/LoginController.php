<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Society;
use App\Models\SsoConnection;
use App\Models\User;
use App\Services\Sso\SsoDirectory;
use App\Services\Sso\SsoProviderFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /** Failed attempts allowed per e-mail + IP before a temporary lockout. */
    private const MAX_ATTEMPTS = 5;

    /** How long (seconds) a failed attempt keeps counting toward the lockout. */
    private const DECAY_SECONDS = 60;

    /** Encrypted cookie remembering the last e-mail used, to prefill the field. */
    private const HINT_COOKIE = 'managy_login_hint';

    public function __construct(
        private readonly SsoDirectory $directory,
        private readonly SsoProviderFactory $providers,
    ) {}

    /** Generic page: a single e-mail field (identifier-first). */
    public function show(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->is_super_admin ? 'admin.dashboard' : 'dashboard');
        }

        // "Changer d'e-mail" comes back to the e-mail step.
        if ($request->boolean('fresh')) {
            $request->session()->forget(['login.step', 'login.email']);
        }

        // The password step is kept in session so it survives a failed attempt
        // (the form redirects back here on error).
        if ($request->session()->get('login.step') === 'password') {
            return view('auth.login', [
                'step' => 'password',
                'email' => $request->session()->get('login.email'),
            ]);
        }

        return view('auth.login', [
            'step' => 'identify',
            'email' => $request->cookie(self::HINT_COOKIE),
        ]);
    }

    /**
     * Resolve what to do with the typed e-mail: go straight to SSO when the
     * domain maps to a provider, otherwise reveal the password field.
     */
    public function identify(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $email = (string) $request->input('email');

        $connections = $this->directory->connectionsForEmail($email);

        if ($connections->count() === 1) {
            return $this->redirectToProvider($request, $connections->first());
        }

        if ($connections->count() > 1) {
            // One société offering several providers → let the visitor choose on
            // its dedicated page.
            $society = Society::find($connections->first()->society_id);

            if ($society) {
                return redirect()->route('login.society', $society->slug);
            }
        }

        // No SSO for this address → classic e-mail + password. Kept in session so
        // the step (and a failed attempt) round-trips through GET /login.
        $request->session()->put('login.step', 'password');
        $request->session()->put('login.email', $email);

        return redirect()->route('login');
    }

    /** Dedicated per-société page reached through its unique slug. */
    public function slug(Request $request, Society $society)
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->is_super_admin ? 'admin.dashboard' : 'dashboard');
        }

        return view('auth.login-society', [
            'society' => $society,
            'methods' => $this->directory->methodsFor($society),
            'email' => $request->cookie(self::HINT_COOKIE),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // A société may impose SSO: a non-gérant then cannot use a password.
        // The gérant (and platform super-admin) always keep this emergency path.
        $this->ensurePasswordLoginAllowed($credentials['email']);

        // Brute-force guard: refuse the attempt once too many failures piled up
        // for this e-mail + IP, and tell the user how long to wait.
        $this->ensureIsNotRateLimited($request);

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
        $request->session()->forget(['login.step', 'login.email']);
        $this->rememberEmail($credentials['email']);

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

    /** Start the OAuth flow for a resolved connection (mirrors SsoController). */
    private function redirectToProvider(Request $request, SsoConnection $connection): RedirectResponse
    {
        $request->session()->put('sso.society_id', $connection->society_id);
        $request->session()->put('sso.provider', $connection->provider);
        $request->session()->put('sso.email_hint', $request->input('email'));

        return $this->providers->make($connection)->redirect();
    }

    /** Refuse a password login when the société imposes SSO (gérant excepted). */
    private function ensurePasswordLoginAllowed(string $email): void
    {
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [Str::lower($email)])
            ->whereNull('deleted_at')
            ->first();

        if (! $user || $user->is_admin || $user->is_super_admin) {
            return;
        }

        if (! $this->directory->passwordAllowed($user->society_id)) {
            throw ValidationException::withMessages([
                'email' => __('Votre société utilise la connexion SSO (Microsoft / Google). Merci de vous connecter via votre fournisseur.'),
            ]);
        }
    }

    /** Persist the e-mail (encrypted cookie) to prefill it on the next visit. */
    private function rememberEmail(string $email): void
    {
        Cookie::queue(self::HINT_COOKIE, $email, 60 * 24 * 365);
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
