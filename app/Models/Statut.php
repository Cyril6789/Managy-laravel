<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Statut extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'couleur', 'ordre', 'verrouille', 'est_defaut', 'est_cloture'];

    protected function casts(): array
    {
        return [
            'verrouille' => 'boolean',
            'est_defaut' => 'boolean',
            'est_cloture' => 'boolean',
        ];
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }
}
