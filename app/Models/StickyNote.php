<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StickyNote extends Model
{
    protected $fillable = ['user_id', 'contenu', 'couleur', 'ordre'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
