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
use Illuminate\Validation\Rules\Password;

/**
 * Public sign-up. Creating an account creates a brand new SaaS space:
 * a société (company identity), its first user (the "gérant"), and a fully
 * seeded set of reference data — then logs the user straight in.
 */
class RegisterController extends Controller
{
    public function show()
    {
        abort_unless(config('saas.registration_enabled'), 404);

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function store(Request $request, SocietyProvisioner $provisioner): RedirectResponse
    {
        abort_unless(config('saas.registration_enabled'), 404);

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
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
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
}
