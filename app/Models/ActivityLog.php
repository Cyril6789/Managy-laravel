<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use BelongsToSociety;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id', 'description',
        'changes', 'ip_address', 'created_at', 'undone_at', 'undone_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'undone_at' => 'datetime',
            'changes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** A soft-deleted subject can be brought back until it has been undone once. */
    public function isRestorable(): bool
    {
        return $this->action === 'deleted' && $this->undone_at === null;
    }

    /** French label for the subject's model type (e.g. "Client"). */
    public function subjectLabel(): ?string
    {
        if (! $this->subject_type) {
            return null;
        }

        return match (class_basename($this->subject_type)) {
            'Client' => 'Client',
            'Intervention' => 'Intervention',
            'Commande' => 'Commande fournisseur',
            'SousTraitance' => 'Sous-traitance',
            'InterventionPrestation' => 'Prestation d\'intervention',
            'InterventionPiece' => 'Pièce d\'intervention',
            'InterventionPhoto' => 'Photo d\'intervention',
            'InterventionMessage', 'ClientMessage', 'PublicMessage' => 'Message',
            'Task' => 'Tâche',
            'Event' => 'Rendez-vous',
            'Materiel' => 'Matériel',
            'StickyNote' => 'Post-it',
            'Automatisme' => 'Automatisme',
            'PermissionGroup' => 'Groupe de permissions',
            'TechnicianAbsence' => 'Absence technicien',
            'MaintenanceMovement' => 'Mouvement de maintenance',
            'Satisfaction' => 'Enquête de satisfaction',
            'SsoConnection' => 'Connexion SSO',
            'User' => 'Utilisateur',
            'Statut' => 'Statut',
            'Prestation' => 'Prestation',
            'CommentaireType', 'MaterielAjouteType', 'MessageType', 'RapportType' => 'Liste de référence',
            'Antivirus' => 'Antivirus',
            'SystemeExploitation' => 'Système d\'exploitation',
            default => class_basename($this->subject_type),
        };
    }

    /**
     * Best-effort link to the subject on the app, for create/update entries.
     * Returns null when the model has no dedicated page (link is then omitted).
     */
    public function subjectUrl(): ?string
    {
        if (! $this->subject_id || ! $this->subject_type) {
            return null;
        }

        return match (class_basename($this->subject_type)) {
            'Client' => route('clients.show', $this->subject_id),
            'Intervention' => route('interventions.show', $this->subject_id),
            'User' => route('staff.edit', $this->subject_id),
            'Automatisme' => route('automatismes.edit', $this->subject_id),
            'Statut', 'Prestation', 'Materiel', 'CommentaireType',
            'MaterielAjouteType', 'MessageType', 'RapportType',
            'Antivirus', 'SystemeExploitation' => route('settings.index'),
            'PermissionGroup' => route('permission-groups.index'),
            default => null,
        };
    }
}
