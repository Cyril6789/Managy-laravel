<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionMessage extends Model
{
    protected $fillable = ['intervention_id', 'user_id', 'message'];

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
