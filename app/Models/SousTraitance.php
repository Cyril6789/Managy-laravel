<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SousTraitance extends Model
{
    protected $fillable = [
        'intervention_id', 'nom', 'devis', 'numero_commande', 'suivi_aller',
        'suivi_retour', 'envoye_le', 'retour_le', 'retournee',
    ];

    protected function casts(): array
    {
        return [
            'envoye_le' => 'date',
            'retour_le' => 'date',
            'retournee' => 'boolean',
        ];
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }
}
