<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionPhoto extends Model
{
    protected $fillable = [
        'intervention_id', 'user_id', 'path', 'original_name', 'prive',
    ];

    protected function casts(): array
    {
        return [
            'prive' => 'boolean',
        ];
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
