<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class StaffController extends Controller
{
    public function index()
    {
        $this->authorize(Permissions::STAFF_MANAGE);

        $staff = User::withCount('interventions')->orderByDesc('is_admin')->orderBy('nom')->get();

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        $this->authorize(Permissions::STAFF_MANAGE);

        return view('staff.create', [
            'user' => new User(['is_active' => true]),
            'catalog' => Permissions::catalog(),
            'granted' => [],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize(Permissions::STAFF_MANAGE);

        $data = $request->validate($this->rules() + [
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = User::create([
            ...collect($data)->except(['password', 'permissions'])->all(),
            'password' => Hash::make($data['password']),
        ]);

        $this->syncPermissions($user, $request);

        return redirect()->route('staff.index')->with('success', 'Technicien créé.');
    }

    public function edit(User $staff)
    {
        $this->authorize(Permissions::STAFF_MANAGE);

        return view('staff.edit', [
            'user' => $staff,
            'catalog' => Permissions::catalog(),
            'granted' => $staff->permissionEntries()->pluck('permission')->all(),
        ]);
    }

    public function update(Request $request, User $staff)
    {
        $this->authorize(Permissions::STAFF_MANAGE);

        $data = $request->validate($this->rules($staff) + [
            'password' => ['nullable', 'confirmed', PasswordRule::defaults()],
        ]);

        $staff->fill(collect($data)->except(['password', 'permissions'])->all());
        if (! empty($data['password'])) {
            $staff->password = Hash::make($data['password']);
        }
        $staff->save();

        $this->syncPermissions($staff, $request);

        return redirect()->route('staff.index')->with('success', 'Technicien mis à jour.');
    }

    public function destroy(Request $request, User $staff)
    {
        $this->authorize(Permissions::STAFF_MANAGE);

        if ($staff->id === $request->user()->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        if ($staff->is_admin && User::where('is_admin', true)->count() <= 1) {
            return back()->with('error', 'Impossible de supprimer le dernier administrateur.');
        }

        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Technicien supprimé.');
    }

    private function rules(?User $staff = null): array
    {
        return [
            'prenom' => ['nullable', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'pseudo' => ['required', 'string', 'max:255', Rule::unique('users', 'pseudo')->ignore($staff)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff)],
            'telephone' => ['nullable', 'string', 'max:30'],
            'is_admin' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function syncPermissions(User $user, Request $request): void
    {
        $requested = collect($request->input('permissions', []))
            ->intersect(Permissions::all())
            ->values();

        $user->permissionEntries()->delete();
        foreach ($requested as $permission) {
            $user->permissionEntries()->create(['permission' => $permission]);
        }
    }
}
