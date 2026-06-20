<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'civilite', 'nom', 'prenom', 'email',
        'telephone_fixe', 'telephone_mobile', 'adresse', 'adresse_complement',
        'code_postal', 'ville', 'siret', 'parent_id', 'notes', 'archived_at',
        'deplacement_gratuit', 'remise_prestations', 'remise_pieces',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'deplacement_gratuit' => 'boolean',
            'remise_prestations' => 'decimal:2',
            'remise_pieces' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // A company ("professionnel") has no first name.
        static::saving(function (Client $client) {
            if ($client->type === 'professionnel') {
                $client->prenom = null;
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'parent_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Client::class, 'parent_id');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    public function maintenanceMovements(): HasMany
    {
        return $this->hasMany(MaintenanceMovement::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ClientMessage::class)->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function nomComplet(): string
    {
        return trim($this->nom.' '.($this->prenom ?? ''));
    }

    public function adresseComplete(): string
    {
        return trim(collect([
            $this->adresse,
            $this->adresse_complement,
            trim(($this->code_postal ?? '').' '.($this->ville ?? '')),
        ])->filter()->implode(', '));
    }

    /** Current maintenance-pack balance in hours. */
    public function soldeMaintenance(): float
    {
        return (float) $this->maintenanceMovements()->sum('mouvement');
    }
}
