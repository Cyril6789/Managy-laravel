<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Society;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * SaaS supervision area, reserved to the platform super-admin. Provides a
 * cross-société overview: who signed up, when, and how active each space is.
 *
 * The super-admin has no société, so the tenant global scope is inactive here
 * and these queries legitimately span every space.
 */
class AdminController extends Controller
{
    public function dashboard()
    {
        $usersBySociety = User::query()->withoutGlobalScope('society')
            ->select('society_id', DB::raw('count(*) as c'))
            ->whereNotNull('society_id')->groupBy('society_id')->pluck('c', 'society_id');

        $interventionsBySociety = Intervention::query()->withoutGlobalScope('society')
            ->select('society_id', DB::raw('count(*) as c'))
            ->groupBy('society_id')->pluck('c', 'society_id');

        $clientsBySociety = Client::query()->withoutGlobalScope('society')
            ->select('society_id', DB::raw('count(*) as c'))
            ->groupBy('society_id')->pluck('c', 'society_id');

        $societies = Society::query()->orderByDesc('created_at')->get()->map(fn (Society $s) => [
            'society' => $s,
            'users' => (int) ($usersBySociety[$s->id] ?? 0),
            'interventions' => (int) ($interventionsBySociety[$s->id] ?? 0),
            'clients' => (int) ($clientsBySociety[$s->id] ?? 0),
        ]);

        $stats = [
            'societies' => $societies->count(),
            'active' => $societies->filter(fn ($r) => $r['society']->is_active)->count(),
            'users' => (int) $usersBySociety->sum(),
            'interventions' => (int) $interventionsBySociety->sum(),
            'clients' => (int) $clientsBySociety->sum(),
            'last7days' => Society::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.dashboard', compact('societies', 'stats'));
    }

    public function society(Society $society)
    {
        $context = fn ($query) => $query->withoutGlobalScope('society')->where('society_id', $society->id);

        $data = [
            'society' => $society,
            'users' => User::query()->withoutGlobalScope('society')
                ->where('society_id', $society->id)->orderByDesc('is_admin')->orderBy('nom')->get(),
            'interventions' => $context(Intervention::query())->count(),
            'clients' => $context(Client::query())->count(),
        ];

        return view('admin.society', $data);
    }

    public function toggle(Society $society)
    {
        $society->update(['is_active' => ! $society->is_active]);

        return back()->with('success', $society->is_active
            ? 'Société réactivée.'
            : 'Société suspendue.');
    }

    public function toggleInvoiceModule(Society $society)
    {
        $society->update(['invoice_enabled' => ! $society->invoice_enabled]);

        return back()->with('success', $society->invoice_enabled
            ? 'Module de facturation PDF activé.'
            : 'Module de facturation PDF désactivé : l’ancien suivi de statut est utilisé.');
    }

    /**
     * Account page for the super-admin. The regular /profil area lives behind
     * the "has a société" middleware, which the super-admin never satisfies, so
     * it gets its own minimal page here to update credentials.
     */
    public function account(Request $request)
    {
        return view('admin.account', ['user' => $request->user()]);
    }

    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'prenom' => ['nullable', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user)->whereNull('deleted_at')],
        ]);

        $user->update($data);

        return back()->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $request->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Mot de passe modifié.');
    }
}
