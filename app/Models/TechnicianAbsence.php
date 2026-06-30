<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A period during which a technician is unavailable (congés, maladie…).
 * Used to take the technician out of the pool of available technicians when
 * scheduling on-site interventions.
 */
class TechnicianAbsence extends Model
{
    use BelongsToSociety;

    protected $fillable = [
        'user_id', 'debut', 'fin', 'journee_entiere', 'motif', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'debut' => 'datetime',
            'fin' => 'datetime',
            'journee_entiere' => 'boolean',
        ];
    }

    public const MOTIFS = [
        'conges' => 'Congés',
        'maladie' => 'Maladie',
        'formation' => 'Formation',
        'autre' => 'Autre',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Absences overlapping the given [start, end] window. */
    public function scopeOverlapping(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->where('debut', '<', $end)->where('fin', '>', $start);
    }

    /** Absences touching the given calendar day. */
    public function scopeOnDay(Builder $query, Carbon $day): Builder
    {
        return $query->overlapping($day->copy()->startOfDay(), $day->copy()->endOfDay());
    }

    public function motifLabel(): string
    {
        return self::MOTIFS[$this->motif] ?? 'Absence';
    }

    /** Whether this absence overlaps the given [start, end] window. */
    public function covers(Carbon $start, ?Carbon $end = null): bool
    {
        $end = $end ?: $start->copy()->addMinute();

        return $this->debut < $end && $this->fin > $start;
    }
}
