<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionPrestation extends Model
{
    protected $fillable = ['intervention_id', 'prestation_id', 'designation', 'duree', 'tarif'];

    protected function casts(): array
    {
        return [
            'duree' => 'decimal:2',
            'tarif' => 'decimal:2',
        ];
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class);
    }

    /** Line total = hourly rate × hours. Free-text lines (no tarif) stay unpriced. */
    public function montant(): float
    {
        return (float) $this->tarif * (float) $this->duree;
    }
}
