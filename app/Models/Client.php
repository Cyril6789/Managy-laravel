<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use BelongsToSociety;
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
        static::saving(function (Client $client) {
            // A company ("professionnel") has no first name.
            if ($client->type === 'professionnel') {
                $client->prenom = null;
            }
            // A "particulier" never carries a SIRET (companies only).
            if ($client->type === 'particulier') {
                $client->siret = null;
            }
        });
    }

    /**
     * The companies (professionnels) this particulier is a contact of.
     * A particulier can be a contact for several companies.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'company_contact', 'contact_id', 'company_id')
            ->withTimestamps();
    }

    /**
     * The particulier contacts attached to this company (professionnel).
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'company_contact', 'company_id', 'contact_id')
            ->withTimestamps();
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

    public function scopeProfessionnels(Builder $query): Builder
    {
        return $query->where('type', 'professionnel');
    }

    public function scopeParticuliers(Builder $query): Builder
    {
        return $query->where('type', 'particulier');
    }

    public function estProfessionnel(): bool
    {
        return $this->type === 'professionnel';
    }

    public function estParticulier(): bool
    {
        return $this->type === 'particulier';
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
