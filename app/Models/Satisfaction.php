<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Satisfaction extends Model
{
    protected $fillable = [
        'intervention_id', 'client_id', 'token', 'note', 'commentaire', 'sent_at', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Satisfaction $s) => $s->token ??= Str::random(48));
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
