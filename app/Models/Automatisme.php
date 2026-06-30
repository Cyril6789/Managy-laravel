<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Automatisme extends Model
{
    use BelongsToSociety;

    protected $table = 'automatismes';

    protected $fillable = [
        'libelle', 'evenement', 'offset_minutes', 'type_lieu', 'statut_id', 'canal', 'sujet', 'modele', 'actif',
    ];

    protected function casts(): array
    {
        return ['actif' => 'boolean', 'offset_minutes' => 'integer'];
    }

    public function estPlanifie(): bool
    {
        return $this->evenement === 'rendez_vous';
    }

    /** Human-readable timing, e.g. "1 h avant le RDV" / "3 h après le RDV". */
    public function timingLabel(): string
    {
        $m = (int) $this->offset_minutes;
        if ($m === 0) {
            return 'à l\'heure du RDV';
        }
        $sens = $m < 0 ? 'avant' : 'après';
        $m = abs($m);
        $txt = match (true) {
            $m % 1440 === 0 => ($m / 1440).' j',
            $m % 60 === 0 => ($m / 60).' h',
            default => $m.' min',
        };

        return "{$txt} {$sens} le RDV";
    }

    public function statut(): BelongsTo
    {
        return $this->belongsTo(Statut::class);
    }
}
