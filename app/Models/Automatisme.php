<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Automatisme extends Model
{
    protected $table = 'automatismes';

    protected $fillable = [
        'libelle', 'evenement', 'statut_id', 'canal', 'sujet', 'modele', 'actif',
    ];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function statut(): BelongsTo
    {
        return $this->belongsTo(Statut::class);
    }
}
