<?php

namespace App\Services;

use App\Models\Intervention;
use App\Models\Notification;
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

    public static function toUser(int $userId, string $titre, ?string $message = null, ?string $url = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'titre' => $titre,
            'message' => $message,
            'url' => $url,
        ]);
    }
}
