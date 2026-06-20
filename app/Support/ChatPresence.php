<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Lightweight presence tracking for the staff-side client chat. While a staff
 * member is actively looking at an intervention's chat, new client messages
 * should not raise a notification (the user already sees them live).
 */
final class ChatPresence
{
    private const TTL = 25; // seconds — refreshed on each chat poll (every 10s)

    private static function key(int $interventionId, int $userId): string
    {
        return "chat-presence:{$interventionId}:{$userId}";
    }

    /** Mark the given user as currently viewing the intervention's chat. */
    public static function mark(int $interventionId, int $userId): void
    {
        Cache::put(self::key($interventionId, $userId), true, self::TTL);
    }

    public static function isViewing(int $interventionId, int $userId): bool
    {
        return (bool) Cache::get(self::key($interventionId, $userId));
    }
}
