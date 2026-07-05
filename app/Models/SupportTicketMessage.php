<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single message in a support ticket conversation. `society_id` always mirrors
 * the parent ticket so the tenant scope keeps the thread segregated — it must be
 * set explicitly on creation because the platform super-admin (who has no
 * société) is a legitimate author.
 */
class SupportTicketMessage extends Model
{
    use BelongsToSociety;

    protected $fillable = [
        'society_id', 'support_ticket_id', 'user_id', 'body', 'is_staff',
    ];

    protected function casts(): array
    {
        return ['is_staff' => 'boolean'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
