<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\SsoConnection;
use App\Models\User;
use App\Services\Sso\MicrosoftGraphGroups;
use App\Services\Sso\SsoGroupSynchronizer;
use App\Services\Sso\SsoProviderFactory;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Handles the OAuth dance for "Sign in with Microsoft / Google".
 *
 * Because a login page is shared by every société, the target société is
 * resolved from the e-mail domain the visitor types, then carried through the
 * flow in the session so the callback configures the right credentials.
 */
class SsoController extends Controller
{
    public function __construct(
        private readonly SsoProviderFactory $providers,
        private readonly MicrosoftGraphGroups $graphGroups,
        private readonly SsoGroupSynchronizer $synchronizer,
    ) {}

    /** Kick off the redirect to the provider for the resolved société. */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        if (! array_key_exists($provider, SsoConnection::PROVIDERS)) {
            abort(404);
        }

        $request->validate(['email' => ['required', 'email']]);
        $email = $request->input('email');

        $connection = $this->resolveConnection($provider, $email);

        if (! $connection) {
            return redirect()->route('login')->withErrors([
                'email' => __('Aucune connexion SSO :provider n\'est active pour ce domaine.', [
                    'provider' => SsoConnection::PROVIDERS[$provider],
                ]),
            ]);
        }

        // Remember which société / provider this flow belongs to.
        $request->session()->put('sso.society_id', $connection->society_id);
        $request->session()->put('sso.provider', $provider);
        $request->session()->put('sso.email_hint', $email);

        return $this->providers->make($connection)->redirect();
    }

    /** Handle the provider callback: identify, provision, sync groups, log in. */
    public function callback(Request $request, string $provider, Tenancy $tenancy): RedirectResponse
    {
        $societyId = $request->session()->pull('sso.society_id');
        $sessionProvider = $request->session()->pull('sso.provider');

        if (! $societyId || $sessionProvider !== $provider) {
            return $this->fail(__('La session SSO a expiré, merci de réessayer.'));
        }

        // Scope every query/creation to the resolved société for this request.
        $tenancy->set((int) $societyId);

        $connection = SsoConnection::where('society_id', $societyId)
            ->where('provider', $provider)
            ->first();

        if (! $connection || ! $connection->isUsable()) {
            return $this->fail(__('La connexion SSO n\'est plus disponible.'));
        }

        try {
            $ssoUser = $this->providers->make($connection)->user();
        } catch (\Throwable $e) {
            report($e);

            return $this->fail(__('La connexion avec le fournisseur a échoué.'));
        }

        $email = $ssoUser->getEmail();
        if (blank($email)) {
            return $this->fail(__('Le fournisseur n\'a pas communiqué d\'adresse e-mail.'));
        }

        if (! $connection->acceptsEmail($email)) {
            return $this->fail(__('Ce compte n\'appartient pas à un domaine autorisé.'));
        }

        $user = $this->findOrProvisionUser($connection, $ssoUser, $email);

        if (! $user) {
            return $this->fail(__('Aucun compte Managy n\'est associé à cette identité.'));
        }

        if (! $user->is_active) {
            return $this->fail(__('Ce compte est désactivé.'));
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        $this->synchronizeGroups($user, $connection, $ssoUser);

        // The gérant decides what happens to an SSO user who ends up with no
        // access at all (in no synchronised group and with no direct right):
        // either refuse the connection, or let them in with the read-only floor.
        if (! $user->hasAnyBusinessAccess()
            && Setting::get('sso_unmapped_policy', 'read_only') === 'deny') {
            return $this->fail(__('Votre accès n\'est pas encore configuré. Contactez votre administrateur.'));
        }

        $user->forceFill(['last_action_at' => now()])->save();
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'Connexion SSO ('.$connection->providerLabel().')',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /** Find the usable, enabled connection for a provider matching the e-mail. */
    private function resolveConnection(string $provider, string $email): ?SsoConnection
    {
        // Guest context: the société scope is off, so we see every connection.
        $candidates = SsoConnection::where('provider', $provider)
            ->where('enabled', true)
            ->get()
            ->filter(fn (SsoConnection $c) => $c->isUsable() && $c->acceptsEmail($email));

        // Prefer a connection that explicitly whitelists the domain over a
        // catch-all one, so a specific tenant always wins.
        return $candidates->sortByDesc(fn (SsoConnection $c) => $c->domains() === [] ? 0 : 1)->first();
    }

    private function findOrProvisionUser(SsoConnection $connection, $ssoUser, string $email): ?User
    {
        $subject = (string) $ssoUser->getId();

        $user = User::where('sso_provider', $connection->provider)
            ->where('sso_subject', $subject)
            ->first()
            ?? User::whereRaw('LOWER(email) = ?', [Str::lower($email)])->first();

        if ($user) {
            // Bind the SSO identity to the existing account on first SSO login.
            if (blank($user->sso_subject)) {
                $user->forceFill([
                    'sso_provider' => $connection->provider,
                    'sso_subject' => $subject,
                ])->save();
            }

            return $user;
        }

        if (! $connection->auto_provision_users) {
            return null;
        }

        [$prenom, $nom] = $this->splitName($ssoUser->getName(), $email);

        return User::create([
            'society_id' => $connection->society_id,
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'email_verified_at' => now(),
            'is_active' => true,
            'sso_provider' => $connection->provider,
            'sso_subject' => $subject,
        ]);
    }

    private function synchronizeGroups(User $user, SsoConnection $connection, $ssoUser): void
    {
        $groups = [];

        if ($connection->provider === SsoConnection::PROVIDER_MICROSOFT) {
            $groups = $this->graphGroups->forToken($ssoUser->token ?? null);
        }

        // Google Workspace group membership is not exposed in the OAuth token;
        // mappings simply won't resolve for Google until an admin-SDK lookup is
        // wired up. The synchroniser is a no-op with an empty group list.
        $this->synchronizer->sync($user, $groups);
    }

    /** @return array{0: ?string, 1: string} [prenom, nom] */
    private function splitName(?string $name, string $email): array
    {
        $name = trim((string) $name);

        if ($name === '') {
            return [null, Str::before($email, '@')];
        }

        $parts = preg_split('/\s+/', $name, 2);

        return count($parts) === 2 ? [$parts[0], $parts[1]] : [null, $parts[0]];
    }

    private function fail(string $message): RedirectResponse
    {
        Auth::logout();

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
