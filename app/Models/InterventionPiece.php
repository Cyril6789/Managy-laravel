<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionPiece extends Model
{
    use BelongsToSociety;

    protected $fillable = ['intervention_id', 'designation', 'prix', 'quantite'];

    protected function casts(): array
    {
        return [
            'prix' => 'decimal:2',
            'quantite' => 'decimal:2',
        ];
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    /** Line total = unit price × quantity. */
    public function total(): float
    {
        return (float) $this->prix * (float) $this->quantite;
    }
}
