<?php

namespace App\Services;

use App\Models\Intervention;
use App\Models\Notification;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\ChatPresence;
use Illuminate\Support\Facades\Auth;

/**
 * Creates in-app notifications. Replaces the legacy "changements_interventions"
 * mechanism: when a user acts on an intervention, the other assigned technicians
 * are notified.
 */
class Notifier
{
    public static function interventionChanged(Intervention $intervention, string $message): void
    {
        $actorId = Auth::id();

        // Notify the assigned technicians AND the person who opened the intervention.
        $recipients = $intervention->techniciens()->pluck('users.id')
            ->push($intervention->opened_by)
            ->filter()
            ->unique()
            ->reject(fn ($id) => $actorId && (int) $id === (int) $actorId)
            ->values();

        foreach ($recipients as $userId) {
            Notification::create([
                'user_id' => $userId,
                'intervention_id' => $intervention->id,
                'titre' => 'Intervention '.($intervention->reference ?? '#'.$intervention->id),
                'message' => $message,
                'url' => route('interventions.show', $intervention),
                'icone' => 'wrench',
            ]);
        }
    }

    /**
     * Like interventionChanged, but skips staff members who are currently
     * watching the intervention's live chat (they already see the message).
     */
    public static function clientChatMessage(Intervention $intervention, string $message): void
    {
        $recipients = $intervention->techniciens()->pluck('users.id')
            ->push($intervention->opened_by)
            ->filter()
            ->unique()
            ->reject(fn ($id) => ChatPresence::isViewing($intervention->id, (int) $id))
            ->values();

        foreach ($recipients as $userId) {
            Notification::create([
                'user_id' => $userId,
                'intervention_id' => $intervention->id,
                'titre' => 'Intervention '.($intervention->reference ?? '#'.$intervention->id),
                'message' => $message,
                'url' => route('interventions.show', $intervention),
                'icone' => 'bell',
            ]);
        }
    }

    public static function toUser(int $userId, string $titre, ?string $message = null, ?string $url = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'titre' => $titre,
            'message' => $message,
            'url' => $url,
        ]);
    }

    /**
     * Notify the people who follow a support ticket that something moved: the
     * user who opened it, plus every gérant of that société (always kept in
     * copy). The actor is never notified about their own action.
     *
     * The notification is stamped with the ticket's société explicitly, because
     * the actor may be the platform super-admin (whose tenancy is null) and the
     * notification must still land inside the recipients' tenant scope.
     */
    public static function ticketProgress(SupportTicket $ticket, string $message): void
    {
        $actorId = Auth::id();

        $gerants = User::query()->withoutGlobalScope('society')
            ->where('society_id', $ticket->society_id)
            ->where('is_admin', true)
            ->pluck('id');

        $recipients = collect([$ticket->user_id])
            ->merge($gerants)
            ->filter()
            ->unique()
            ->reject(fn ($id) => $actorId && (int) $id === (int) $actorId)
            ->values();

        foreach ($recipients as $userId) {
            // society_id is not mass-assignable on Notification; set it directly
            // so the record belongs to the recipient's tenant (not the actor's).
            $notification = new Notification([
                'user_id' => $userId,
                'titre' => 'Assistance '.$ticket->reference,
                'message' => $message,
                'url' => route('support.show', $ticket),
                'icone' => 'lifebuoy',
            ]);
            $notification->society_id = $ticket->society_id;
            $notification->save();
        }
    }
}
