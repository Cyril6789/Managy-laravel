<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;

class MaterielAjouteType extends Model
{
    use BelongsToSociety;
    protected $fillable = ['nom', 'texte'];
}
