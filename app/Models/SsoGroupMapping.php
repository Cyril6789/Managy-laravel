<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Association between an SSO security group (by object id or name) and a Managy
 * permission group. Consumed by SsoGroupSynchronizer at login time.
 */
class SsoGroupMapping extends Model
{
    use BelongsToSociety;

    protected $fillable = ['society_id', 'permission_group_id', 'external_group'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }
}
