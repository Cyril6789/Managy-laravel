<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use BelongsToSociety, HasFactory, Notifiable;

    protected $fillable = [
        'society_id',
        'prenom',
        'nom',
        'pseudo',
        'email',
        'telephone',
        'password',
        'email_verified_at',
        'is_admin',
        'is_super_admin',
        'is_active',
        'two_factor_enabled',
        'sso_provider',
        'sso_subject',
        'chat_status',
        'preferences',
        'last_action_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_action_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'preferences' => 'array',
        ];
    }

    // ----- Relationships -----------------------------------------------------

    public function interventions(): BelongsToMany
    {
        return $this->belongsToMany(Intervention::class)
            ->withPivot('assigned_at');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function stickyNotes(): HasMany
    {
        return $this->hasMany(StickyNote::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function permissionEntries(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /** Permission groups (e.g. "Tech_Niv1") the user belongs to. */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(PermissionGroup::class, 'permission_group_user')
            ->withPivot('source')
            ->withTimestamps();
    }

    public function absences(): HasMany
    {
        return $this->hasMany(TechnicianAbsence::class);
    }

    /** Whether the technician is absent at the given moment / window. */
    public function isAbsentBetween(Carbon $start, ?Carbon $end = null): bool
    {
        $end = $end ?: $start->copy()->addMinute();

        return $this->absences()
            ->where('debut', '<', $end)
            ->where('fin', '>', $start)
            ->exists();
    }

    // ----- Permissions -------------------------------------------------------

    /**
     * Admins ("gérant") bypass every permission check.
     */
    public function can($abilities, $arguments = []): bool
    {
        if (is_string($abilities) && $this->is_admin) {
            return true;
        }

        return parent::can($abilities, $arguments);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->is_admin) {
            return true;
        }

        // Granted directly on the user...
        if ($this->permissionEntries()->where('permission', $permission)->exists()) {
            return true;
        }

        // ...or inherited from one of the user's permission groups.
        return $this->groups()
            ->whereHas('permissions', fn ($q) => $q->where('permission', $permission))
            ->exists();
    }

    /**
     * All effective permission keys for the user: the ones granted directly plus
     * the ones inherited from their groups.
     *
     * @return list<string>
     */
    public function effectivePermissions(): array
    {
        $direct = $this->permissionEntries()->pluck('permission');
        $fromGroups = $this->groups()
            ->with('permissions:id,permission_group_id,permission')
            ->get()
            ->flatMap(fn (PermissionGroup $g) => $g->permissions->pluck('permission'));

        return $direct->merge($fromGroups)->unique()->values()->all();
    }

    /**
     * Whether the user has any business access at all: a gérant, or someone with
     * at least one effective permission (directly or via a group). Used to decide
     * what to do with an SSO user who landed in no synchronised group.
     */
    public function hasAnyBusinessAccess(): bool
    {
        return $this->is_admin || $this->effectivePermissions() !== [];
    }

    public function fullName(): string
    {
        return trim(($this->prenom ?? '').' '.$this->nom);
    }

    public function initials(): string
    {
        return strtoupper(mb_substr($this->prenom ?? $this->nom, 0, 1).mb_substr($this->nom, 0, 1));
    }
}
