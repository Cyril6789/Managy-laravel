<?php

namespace App\Models\Concerns;

use App\Models\Society;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applied to every tenant model. It:
 *   - filters all queries by the current société (data isolation), and
 *   - stamps new records with the current société id automatically,
 * so application code never has to think about society_id.
 */
trait BelongsToSociety
{
    public static function bootBelongsToSociety(): void
    {
        static::addGlobalScope('society', function (Builder $builder) {
            $societyId = app(Tenancy::class)->id();

            if ($societyId !== null) {
                $builder->where($builder->getModel()->getTable().'.society_id', $societyId);
            }
        });

        static::creating(function ($model) {
            if ($model->society_id === null) {
                $model->society_id = app(Tenancy::class)->id();
            }
        });
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    /** Escape hatch for the super-admin area / maintenance jobs. */
    public function scopeWithoutSocietyScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('society');
    }
}
