<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicMessage extends Model
{
    use BelongsToSociety;
    public const UPDATED_AT = null;

    protected $fillable = ['intervention_id', 'author', 'user_id', 'message', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
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
