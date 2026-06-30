<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use BelongsToSociety;

    protected $fillable = [
        'user_id', 'created_by', 'client_id', 'intervention_id', 'titre', 'description',
        'statut', 'priorite', 'heures_estimees', 'heures_passees', 'echeance', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'heures_estimees' => 'decimal:2',
            'heures_passees' => 'decimal:2',
            'echeance' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }
}
