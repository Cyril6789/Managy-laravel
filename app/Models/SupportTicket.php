<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An "Assistance" ticket. Any user of a société can open one (a question, a bug
 * report or a suggestion) and follow its progress; the platform super-admin
 * processes it from the supervision area. Tenant segregation is automatic via
 * {@see BelongsToSociety}: users only ever see their own société's tickets,
 * while the super-admin (no société) sees them all.
 */
class SupportTicket extends Model
{
    use Auditable, BelongsToSociety, SoftDeletes;

    /** Child rows soft-deleted together with the ticket. */
    protected array $auditCascades = ['messages'];

    protected $fillable = [
        'society_id', 'user_id', 'sujet', 'description',
        'type', 'urgence', 'statut', 'last_reply_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_reply_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // ----- Reference labels & catalogues -------------------------------------

    public const TYPES = [
        'question' => 'Question',
        'bug' => 'Déclaration de bug',
        'suggestion' => 'Suggestion',
    ];

    public const URGENCES = [
        'basse' => 'Basse',
        'normale' => 'Normale',
        'haute' => 'Haute',
        'urgente' => 'Urgente',
    ];

    public const STATUTS = [
        'ouvert' => 'Ouvert',
        'en_cours' => 'En cours',
        'en_attente' => 'En attente de votre réponse',
        'resolu' => 'Résolu',
        'ferme' => 'Fermé',
    ];

    /** Human, stable reference shown in the UI and notifications. */
    public function getReferenceAttribute(): string
    {
        return 'A-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function urgenceLabel(): string
    {
        return self::URGENCES[$this->urgence] ?? $this->urgence;
    }

    public function statutLabel(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function isClosed(): bool
    {
        return in_array($this->statut, ['resolu', 'ferme'], true);
    }

    /** Soft badge colour (hex) for the current status — see <x-badge>. */
    public function statutColor(): string
    {
        return [
            'ouvert' => '#2563eb',
            'en_cours' => '#7c3aed',
            'en_attente' => '#d97706',
            'resolu' => '#16a34a',
            'ferme' => '#6b7280',
        ][$this->statut] ?? '#6b7280';
    }

    public function urgenceColor(): string
    {
        return [
            'basse' => '#6b7280',
            'normale' => '#2563eb',
            'haute' => '#d97706',
            'urgente' => '#dc2626',
        ][$this->urgence] ?? '#6b7280';
    }

    /** Icon key (see <x-icon>) illustrating the ticket type. */
    public function typeIcon(): string
    {
        return [
            'question' => 'lifebuoy',
            'bug' => 'bolt',
            'suggestion' => 'star',
        ][$this->type] ?? 'lifebuoy';
    }

    // ----- Scopes ------------------------------------------------------------

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('statut', ['resolu', 'ferme']);
    }

    // ----- Relationships -----------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class)->oldest();
    }
}
