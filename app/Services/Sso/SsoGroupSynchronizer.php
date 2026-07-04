<?php

namespace App\Services\Sso;

use App\Models\PermissionGroup;
use App\Models\SsoGroupMapping;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Places a user in the Managy permission groups that correspond to their SSO
 * security groups, according to the société's sso_group_mappings.
 *
 * Only the memberships flagged source = "sso" are ever touched, so any group the
 * gérant assigned by hand (source = "manual") is preserved across re-syncs.
 *
 * Example: the directory group "sg_managy_tech_niv1" is mapped to the Managy
 * group "Tech_Niv1"; a user in that directory group therefore ends up in
 * "Tech_Niv1" and inherits its permissions — automatically, at every login.
 */
class SsoGroupSynchronizer
{
    /**
     * @param  list<array{id: string, name: string}>  $externalGroups
     */
    public function sync(User $user, array $externalGroups): void
    {
        // Normalise the identifiers we can match against (ids and names), lower-cased.
        $external = collect($externalGroups)
            ->flatMap(fn (array $g) => [Str::lower($g['id'] ?? ''), Str::lower($g['name'] ?? '')])
            ->filter()
            ->unique();

        $mappings = SsoGroupMapping::query()
            ->where('society_id', $user->society_id)
            ->get();

        $targetGroupIds = $mappings
            ->filter(fn (SsoGroupMapping $m) => $external->contains(Str::lower($m->external_group)))
            ->pluck('permission_group_id')
            ->unique()
            ->values();

        // Current SSO-managed memberships for this user.
        $currentSso = $user->groups()
            ->wherePivot('source', 'sso')
            ->pluck('permission_groups.id');

        // Detach SSO groups the user is no longer entitled to.
        $toDetach = $currentSso->diff($targetGroupIds);
        if ($toDetach->isNotEmpty()) {
            $user->groups()->detach($toDetach->all());
        }

        // Attach the newly matched groups (skip the ones already present, in any
        // source, so we never clobber a manual assignment's flag).
        $alreadyMember = $user->groups()->pluck('permission_groups.id');
        foreach ($targetGroupIds->diff($alreadyMember) as $groupId) {
            // Guard against a stale mapping pointing at a deleted group.
            if (PermissionGroup::where('id', $groupId)->exists()) {
                $user->groups()->attach($groupId, ['source' => 'sso']);
            }
        }
    }
}
