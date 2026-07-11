<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Society;
use App\Models\SsoConnection;
use App\Models\User;
use App\Services\Sso\SsoDirectory;
use App\Services\Sso\SsoProviderFactory;
use App\Support\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;
    private const HINT_COOKIE = 'managy_login_hint';

    public function __construct(
        private readonly SsoDirectory $directory,
        private readonly SsoProviderFactory $providers,
    ) {}

    public function show(Request $request)
    {
        if (Auth::check()) {
            return $this->authenticatedRedirect(Auth::user());
        }

        if ($society = $request->attributes->get('tenant')) {
            return view('auth.login-society', [
                'society' => $society,
                'methods' => $this->directory->methodsFor($society),
                'email' => $request->cookie(self::HINT_COOKIE),
            ]);
        }

        if ($request->boolean('fresh')) {
            $request->session()->forget(['login.step', 'login.email']);
        }

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

    public function identify(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $email = (string) $request->input('email');
        $connections = $this->directory->connectionsForEmail($email);

        if ($connections->count() === 1) {
            $society = Society::find($connections->first()->society_id);

            if ($society) {
                return redirect()->away(TenantUrl::forSociety($society, '/login'));
            }
        }

        if ($connections->count() > 1) {
            $society = Society::find($connections->first()->society_id);

            if ($society) {
                return redirect()->away(TenantUrl::forSociety($society, '/login'));
            }
        }

        $request->session()->put('login.step', 'password');
        $request->session()->put('login.email', $email);

        $user = User::withoutSocietyScope()
            ->whereRaw('LOWER(email) = ?', [Str::lower($email)])
            ->whereNull('deleted_at')
            ->first();

        if ($user && ! $user->is_super_admin && $user->society_id) {
            $society = Society::find($user->society_id);

            if ($society) {
                return redirect()->away(TenantUrl::forSociety($society, '/login'));
            }
        }

        return redirect()->route('login');
    }

    /** Backward compatibility for old /login/{slug} bookmarks. */
    public function slug(Request $request, Society $society): RedirectResponse
    {
        return redirect()->away(TenantUrl::forSociety($society, '/login'), 302);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensurePasswordLoginAllowed($credentials['email']);
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

        return $this->authenticatedRedirect(Auth::user());
    }

    public function logout(Request $request): RedirectResponse
    {
        $society = $request->attributes->get('tenant');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $society
            ? redirect()->away(TenantUrl::forSociety($society, '/login'))
            : redirect()->away(TenantUrl::central('/login'));
    }

    private function authenticatedRedirect(User $user): RedirectResponse
    {
        if ($user->is_super_admin) {
            return redirect()->away(TenantUrl::central('/admin'));
        }

        $society = Society::findOrFail($user->society_id);

        return redirect()->away(TenantUrl::forSociety($society, '/tableau-de-bord'));
    }

    private function redirectToProvider(Request $request, SsoConnection $connection): RedirectResponse
    {
        $request->session()->put('sso.society_id', $connection->society_id);
        $request->session()->put('sso.provider', $connection->provider);
        $request->session()->put('sso.email_hint', $request->input('email'));

        return $this->providers->make($connection)->redirect();
    }

    private function ensurePasswordLoginAllowed(string $email): void
    {
        $user = User::withoutSocietyScope()
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

    private function rememberEmail(string $email): void
    {
        Cookie::queue(self::HINT_COOKIE, $email, 60 * 24 * 365);
    }

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

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }
}
