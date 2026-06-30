<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientMessage extends Model
{
    use BelongsToSociety;
    protected $fillable = [
        'client_id', 'intervention_id', 'user_id', 'canal', 'destinataire',
        'sujet', 'corps', 'statut', 'programme_pour', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'programme_pour' => 'datetime',
            'sent_at' => 'datetime',
        ];
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
