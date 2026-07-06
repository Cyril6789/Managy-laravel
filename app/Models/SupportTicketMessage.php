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

    /**
     * The message author, resolved WITHOUT the tenant scope — so a société
     * member reading their ticket sees the real name of the platform super-admin
     * who answered as "Support" (whose société is null and would otherwise fall
     * outside the reader's scope, showing up as "Utilisateur supprimé"). Only
     * used to display the author name; genuinely deleted users stay hidden by the
     * soft-delete scope, which is left in place.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope('society');
    }
}
