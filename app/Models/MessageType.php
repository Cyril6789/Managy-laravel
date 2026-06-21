<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A reusable SMS / e-mail template ("SMS type" / "mail type"). Managed in
 * Paramètres and chosen on the intervention sheet to prefill the message
 * composer (the SMS body, or the e-mail subject + body).
 */
class MessageType extends Model
{
    protected $fillable = ['canal', 'titre', 'sujet', 'corps'];

    public function scopeCanal(Builder $query, string $canal): Builder
    {
        return $query->where('canal', $canal);
    }
}
