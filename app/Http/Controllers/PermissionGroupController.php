<?php

namespace App\Http\Controllers;

use App\Models\PermissionGroup;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * CRUD for the optional permission groups (e.g. "Tech_Niv1"): their permission
 * set, their members, and the SSO security groups mapped to them.
 */
class PermissionGroupController extends Controller
{
    public function index()
    {
        $this->authorize(Permissions::PERMISSION_GROUPS_MANAGE);

        $groups = PermissionGroup::withCount(['users', 'permissions', 'ssoMappings'])
            ->orderBy('name')
            ->get();

        return view('permission-groups.index', compact('groups'));
    }

    public function create()
    {
        $this->authorize(Permissions::PERMISSION_GROUPS_MANAGE);

        return view('permission-groups.create', [
            'group' => new PermissionGroup,
            'catalog' => Permissions::catalog(),
            'staff' => $this->staff(),
            'granted' => [],
            'members' => [],
            'mappings' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize(Permissions::PERMISSION_GROUPS_MANAGE);

        $data = $this->validateGroup($request);

        $group = PermissionGroup::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $this->syncRelations($group, $request);

        return redirect()->route('permission-groups.index')
            ->with('success', 'Groupe de permissions créé.');
    }

    public function edit(PermissionGroup $permissionGroup)
    {
        $this->authorize(Permissions::PERMISSION_GROUPS_MANAGE);

        return view('permission-groups.edit', [
            'group' => $permissionGroup,
            'catalog' => Permissions::catalog(),
            'staff' => $this->staff(),
            'granted' => $permissionGroup->permissionKeys(),
            'members' => $permissionGroup->users()->pluck('users.id')->all(),
            'mappings' => $permissionGroup->ssoMappings()->orderBy('external_group')->get(),
        ]);
    }

    public function update(Request $request, PermissionGroup $permissionGroup): RedirectResponse
    {
        $this->authorize(Permissions::PERMISSION_GROUPS_MANAGE);

        $data = $this->validateGroup($request, $permissionGroup);

        $permissionGroup->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $this->syncRelations($permissionGroup, $request);

        return redirect()->route('permission-groups.index')
            ->with('success', 'Groupe de permissions mis à jour.');
    }

    public function destroy(PermissionGroup $permissionGroup): RedirectResponse
    {
        $this->authorize(Permissions::PERMISSION_GROUPS_MANAGE);

        $permissionGroup->delete();

        return redirect()->route('permission-groups.index')
            ->with('success', 'Groupe de permissions supprimé.');
    }

    private function validateGroup(Request $request, ?PermissionGroup $group = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('permission_groups', 'name')
                    ->where('society_id', app(Tenancy::class)->id())
                    ->ignore($group?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'members' => ['nullable', 'array'],
            'members.*' => ['integer'],
            'sso_groups' => ['nullable', 'array'],
            'sso_groups.*' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /** Sync permissions, members (manual) and SSO group mappings from the form. */
    private function syncRelations(PermissionGroup $group, Request $request): void
    {
        // Permissions — keep only catalogued keys.
        $permissions = collect($request->input('permissions', []))
            ->intersect(Permissions::all())
            ->values();
        $group->syncPermissions($permissions);

        // Members — the checkboxes manage the *manual* memberships only; the ones
        // granted automatically via SSO (source = "sso") are always preserved so a
        // future login re-sync stays authoritative over them.
        $manualIds = User::whereIn('id', (array) $request->input('members', []))->pluck('id');
        $ssoMemberIds = $group->users()->wherePivot('source', 'sso')->pluck('users.id');

        $sync = [];
        foreach ($ssoMemberIds as $id) {
            $sync[$id] = ['source' => 'sso'];
        }
        foreach ($manualIds as $id) {
            $sync[$id] ??= ['source' => 'manual'];
        }

        $group->users()->sync($sync);

        // SSO group mappings — one external group identifier per non-empty row.
        $group->ssoMappings()->delete();
        collect($request->input('sso_groups', []))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->each(fn ($external) => $group->ssoMappings()->create(['external_group' => $external]));
    }

    /** Assignable team members of the current société. */
    private function staff()
    {
        return User::where('is_super_admin', false)
            ->orderByDesc('is_admin')
            ->orderBy('nom')
            ->get(['id', 'prenom', 'nom', 'email', 'is_admin']);
    }
}
