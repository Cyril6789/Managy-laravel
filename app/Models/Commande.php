<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commande extends Model
{
    use BelongsToSociety;
    protected $fillable = [
        'intervention_id', 'fournisseur', 'bon_commande', 'numero_commande',
        'suivi_colis', 'commande_le', 'recue_le', 'recue',
    ];

    protected function casts(): array
    {
        return [
            'commande_le' => 'date',
            'recue_le' => 'date',
            'recue' => 'boolean',
        ];
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }
}
