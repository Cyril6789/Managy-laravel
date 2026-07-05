<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StickyNote extends Model
{
    use Auditable, BelongsToSociety, SoftDeletes;

    protected $fillable = ['user_id', 'contenu', 'couleur', 'ordre'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
