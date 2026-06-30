<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestation extends Model
{
    use BelongsToSociety;
    use HasFactory;

    protected $fillable = ['designation', 'duree_defaut', 'tarif'];

    protected function casts(): array
    {
        return [
            'duree_defaut' => 'decimal:2',
            'tarif' => 'decimal:2',
        ];
    }
}
