<?php

namespace App\Support;

/**
 * Resolves the "current société" for the running request. Every tenant model
 * reads this through the BelongsToSociety global scope, so a logged-in user
 * only ever sees the data of their own société — automatically, without ever
 * choosing one.
 *
 * When the resolved id is null (guest, or the platform super-admin), the scope
 * is *not* applied: the super-admin supervision area can therefore query across
 * every société.
 */
class Tenancy
{
    private ?int $societyId = null;

    private bool $explicit = false;

    /** The société id that should scope queries right now, or null for "all". */
    public function id(): ?int
    {
        if ($this->explicit) {
            return $this->societyId;
        }

        // Only read an already-resolved user. Calling auth()->user() here would
        // recurse while the session guard is itself resolving the User model
        // (whose global scope asks us for the current société).
        if (! auth()->hasUser()) {
            return null;
        }

        $user = auth()->user();

        return $user && ! $user->is_super_admin ? $user->society_id : null;
    }

    /** Force a specific société (provisioning, jobs, tests). */
    public function set(?int $societyId): void
    {
        $this->societyId = $societyId;
        $this->explicit = true;
    }

    /** Fall back to resolving the société from the authenticated user. */
    public function forget(): void
    {
        $this->societyId = null;
        $this->explicit = false;
    }

    /** Run a callback scoped to a given société, then restore the context. */
    public function forSociety(int $societyId, callable $callback): mixed
    {
        $previousId = $this->societyId;
        $previousExplicit = $this->explicit;

        $this->set($societyId);

        try {
            return $callback();
        } finally {
            $this->societyId = $previousId;
            $this->explicit = $previousExplicit;
        }
    }
}
