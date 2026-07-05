<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class InterventionPhoto extends Model
{
    use Auditable, BelongsToSociety, SoftDeletes;

    protected $fillable = [
        'intervention_id', 'user_id', 'path', 'original_name', 'prive',
    ];

    protected static function booted(): void
    {
        // Keep the file while the record is only soft-deleted (so a deletion can
        // be undone); purge it from storage only on a permanent delete.
        static::forceDeleted(function (self $photo) {
            if ($photo->path) {
                Storage::disk('public')->delete($photo->path);
            }
        });
    }

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
