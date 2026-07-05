<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Society;
use App\Models\User;
use App\Services\SocietyProvisioner;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Public sign-up. Creating an account creates a brand new SaaS space:
 * a société (company identity), its first user (the "gérant"), and a fully
 * seeded set of reference data — then logs the user straight in.
 */
class RegisterController extends Controller
{
    /** Fastest a genuine human could plausibly fill and submit the form. */
    private const MIN_FORM_SECONDS = 2;

    public function show(Request $request)
    {
        abort_unless(config('saas.registration_enabled'), 404);

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Stamp the moment the form is served; the submit handler rejects
        // anything that comes back implausibly fast (i.e. an automated bot).
        $request->session()->put('register_started_at', now()->timestamp);

        return view('auth.register');
    }

    public function store(Request $request, SocietyProvisioner $provisioner): RedirectResponse
    {
        abort_unless(config('saas.registration_enabled'), 404);

        $this->ensureNotABot($request);

        $data = $request->validate([
            // Société
            'company_name' => ['required', 'string', 'max:255'],
            'company_siret' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_postal_code' => ['nullable', 'string', 'max:20'],
            'company_city' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            // Gérant (first user)
            'prenom' => ['nullable', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $society = Society::create([
            'name' => $data['company_name'],
            'siret' => $data['company_siret'] ?? null,
            'phone' => $data['company_phone'] ?? null,
            'address' => $data['company_address'] ?? null,
            'postal_code' => $data['company_postal_code'] ?? null,
            'city' => $data['company_city'] ?? null,
            'website' => $data['company_website'] ?? null,
            'email' => $data['email'],
            'logo' => $request->hasFile('logo')
                ? $request->file('logo')->store('logos', 'public')
                : null,
        ]);

        // Create the owner *inside* the new tenant context so society_id is set.
        $user = app(Tenancy::class)->forSociety($society->id, fn () => User::create([
            'prenom' => $data['prenom'] ?? null,
            'nom' => $data['nom'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => true,      // first user is the "gérant"
            'is_active' => true,
        ]));

        $provisioner->provision($society);

        ActivityLog::create([
            'society_id' => $society->id,
            'user_id' => $user->id,
            'action' => 'register',
            'description' => 'Création de la société '.$society->name,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if (config('saas.email_verification')) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice');
        }

        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue ! Votre espace '.$society->name.' est prêt.');
    }

    /**
     * Cheap, dependency-free bot screening for the public sign-up form:
     *  - a hidden "homepage" honeypot no human ever fills, and
     *  - a minimum think-time between rendering and submitting the form.
     *
     * Either signal points to automation, so we reject with a generic message
     * rather than provisioning a throwaway société.
     */
    private function ensureNotABot(Request $request): void
    {
        if (filled($request->input('homepage'))) {
            throw ValidationException::withMessages([
                'email' => __('Envoi non valide. Merci de recharger la page et de réessayer.'),
            ]);
        }

        $startedAt = $request->session()->pull('register_started_at');

        if ($startedAt !== null && (now()->timestamp - (int) $startedAt) < self::MIN_FORM_SECONDS) {
            throw ValidationException::withMessages([
                'email' => __('Formulaire soumis trop rapidement. Merci de réessayer.'),
            ]);
        }
    }
}
