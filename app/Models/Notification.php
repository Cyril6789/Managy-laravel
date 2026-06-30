<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use BelongsToSociety;

    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id', 'intervention_id', 'titre', 'message', 'url', 'icone', 'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
