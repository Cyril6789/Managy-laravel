<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CommentaireType extends Model
{
    use Auditable, BelongsToSociety, SoftDeletes;

    protected $fillable = ['titre', 'texte'];
}
