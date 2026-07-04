<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A named bundle of permissions the gérant can assign to several users at once
 * (e.g. "Tech_Niv1"). Optionally bridged to one or more SSO security groups so
 * membership is granted automatically at SSO login.
 */
class PermissionGroup extends Model
{
    use BelongsToSociety;

    protected $fillable = ['society_id', 'name', 'description'];

    public function permissions(): HasMany
    {
        return $this->hasMany(PermissionGroupPermission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_group_user')
            ->withPivot('source')
            ->withTimestamps();
    }

    public function ssoMappings(): HasMany
    {
        return $this->hasMany(SsoGroupMapping::class);
    }

    /** @return list<string> permission keys this group grants. */
    public function permissionKeys(): array
    {
        return $this->permissions()->pluck('permission')->all();
    }

    /** Replace the group's permission set with the given (validated) keys. */
    public function syncPermissions(iterable $permissions): void
    {
        $this->permissions()->delete();
        foreach (collect($permissions)->unique() as $permission) {
            $this->permissions()->create(['permission' => $permission]);
        }
    }
}
