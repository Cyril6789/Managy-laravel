<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'prenom',
        'nom',
        'pseudo',
        'email',
        'telephone',
        'password',
        'is_admin',
        'is_active',
        'two_factor_enabled',
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

        return $this->permissionEntries()
            ->where('permission', $permission)
            ->exists();
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
