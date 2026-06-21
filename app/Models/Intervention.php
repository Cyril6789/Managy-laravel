<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'client_id', 'contact_id', 'materiel_id', 'systeme_exploitation_id',
        'antivirus_id', 'statut_id', 'opened_by', 'restituted_by', 'type_lieu',
        'rdv_debut', 'rdv_fin', 'rdv_annule', 'priorite', 'urgente', 'garantie',
        'materiel_depose', 'panne', 'diagnostic', 'materiel_ajoute', 'message_client',
        'message_interne', 'mdp', 'tarif_estimatif', 'note', 'facturee', 'payee',
        'montant_prestations', 'montant_pieces', 'montant_deplacement', 'deplacement_km',
        'montant_total', 'remise_type', 'remise_valeur', 'remise_montant', 'montant_paye', 'paiement_mode',
        'maintenance_heures', 'montant_maintenance',
        'public_token', 'signature_path', 'signataire_nom', 'signed_at',
        'opened_at', 'closed_at', 'restituted_at', 'finalisee_at',
    ];

    protected function casts(): array
    {
        return [
            'rdv_debut' => 'datetime',
            'rdv_fin' => 'datetime',
            'rdv_annule' => 'boolean',
            'urgente' => 'boolean',
            'garantie' => 'boolean',
            'facturee' => 'boolean',
            'payee' => 'boolean',
            'tarif_estimatif' => 'decimal:2',
            'montant_prestations' => 'decimal:2',
            'montant_pieces' => 'decimal:2',
            'montant_deplacement' => 'decimal:2',
            'deplacement_km' => 'decimal:2',
            'montant_total' => 'decimal:2',
            'remise_valeur' => 'decimal:2',
            'remise_montant' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'maintenance_heures' => 'decimal:2',
            'montant_maintenance' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'restituted_at' => 'datetime',
            'finalisee_at' => 'datetime',
            'signed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Intervention $i) {
            $i->public_token ??= Str::random(48);
            $i->opened_at ??= now();
        });

        static::created(function (Intervention $i) {
            $i->reference ??= $i->opened_at->format('Y').'-'.str_pad((string) $i->id, 4, '0', STR_PAD_LEFT);
            $i->saveQuietly();
        });
    }

    // ----- Relationships -----------------------------------------------------

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'contact_id');
    }

    public function materiel(): BelongsTo
    {
        return $this->belongsTo(Materiel::class);
    }

    public function systemeExploitation(): BelongsTo
    {
        return $this->belongsTo(SystemeExploitation::class);
    }

    public function antivirus(): BelongsTo
    {
        return $this->belongsTo(Antivirus::class);
    }

    public function statut(): BelongsTo
    {
        return $this->belongsTo(Statut::class);
    }

    public function ouvreur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function techniciens(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('assigned_at');
    }

    public function prestations(): HasMany
    {
        return $this->hasMany(InterventionPrestation::class);
    }

    public function pieces(): HasMany
    {
        return $this->hasMany(InterventionPiece::class);
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    public function sousTraitances(): HasMany
    {
        return $this->hasMany(SousTraitance::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InterventionMessage::class)->oldest();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InterventionLog::class)->latest();
    }

    public function clientMessages(): HasMany
    {
        return $this->hasMany(ClientMessage::class)->latest();
    }

    public function publicMessages(): HasMany
    {
        return $this->hasMany(PublicMessage::class)->oldest();
    }

    public function satisfaction(): BelongsTo
    {
        return $this->belongsTo(Satisfaction::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(InterventionPhoto::class)->latest();
    }

    // ----- Scopes / helpers --------------------------------------------------

    public function scopeOuvertes(Builder $query): Builder
    {
        return $query->whereNull('closed_at');
    }

    public function scopeCloturees(Builder $query): Builder
    {
        return $query->whereNotNull('closed_at');
    }

    public function estCloturee(): bool
    {
        return $this->closed_at !== null;
    }

    public function estVerrouillee(): bool
    {
        return (bool) ($this->statut?->verrouille);
    }

    public function estFinalisee(): bool
    {
        return $this->finalisee_at !== null;
    }

    public function estDomicile(): bool
    {
        return $this->type_lieu === 'domicile';
    }

    public function tempsTotal(): float
    {
        return (float) $this->prestations->sum('duree');
    }

    /** Gross sum of the priced services (hourly catalogue rate × hours per line). */
    public function montantPrestations(): float
    {
        return (float) $this->prestations->sum(fn (InterventionPrestation $p) => $p->montant());
    }

    /** Gross sum of the replaced parts (unit price × quantity). */
    public function montantPieces(): float
    {
        return (float) $this->pieces->sum(fn (InterventionPiece $p) => $p->total());
    }

    /** Who actually gets the SMS / e-mail: the selected contact, else the client. */
    public function recipientClient(): ?Client
    {
        return $this->contact ?: $this->client;
    }
}
