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
}
