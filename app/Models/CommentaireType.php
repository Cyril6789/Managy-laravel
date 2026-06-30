<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;

class CommentaireType extends Model
{
    use BelongsToSociety;

    protected $fillable = ['titre', 'texte'];
}
