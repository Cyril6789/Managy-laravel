<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Records every create / update / delete / restore of a model into the
 * activity log, with maximum detail (who, what, when):
 *
 *  - update   → keeps the previous value of each changed field (old → new);
 *  - delete   → soft delete + a full snapshot of the record so it can be undone,
 *               cascading the soft delete to declared child relations.
 *
 * Always combined with {@see SoftDeletes}. The written {@see ActivityLog} carries
 * the current société (via BelongsToSociety), so the trail is tenant-segregated.
 */
trait Auditable
{
    /**
     * Child relations soft-deleted (and restored) together with this model.
     * Declared per-model; each relation must target an Auditable model.
     *
     * @var list<string>
     */
    // protected array $auditCascades = [];

    /** Set on a child during a cascade so it does not write its own log line. */
    public bool $auditQuietly = false;

    /** Descendants soft-deleted alongside this model, exposed to the parent. */
    public array $auditCascadedCache = [];

    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $model->recordAudit('created', ['attributes' => $model->auditableSnapshot()]);
        });

        static::updated(function (Model $model) {
            $changes = $model->auditableChanges();

            // Nothing meaningful changed (e.g. only timestamps, or the deleted_at
            // flip performed by restore()) → don't clutter the trail.
            if ($changes['new'] === []) {
                return;
            }

            $model->recordAudit('updated', $changes);
        });

        static::deleted(function (Model $model) {
            // React to the soft-delete pass only, never a hard/force delete.
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            $cascaded = $model->cascadeSoftDeletes();

            if ($model->auditQuietly) {
                $model->auditCascadedCache = $cascaded;

                return;
            }

            $model->recordAudit('deleted', [
                'attributes' => $model->auditableSnapshot(),
                'cascaded' => $cascaded,
            ]);
        });

        static::restored(function (Model $model) {
            $model->recordAudit('restored', ['attributes' => $model->auditableSnapshot()]);
        });
    }

    /**
     * Soft-delete the declared child relations and return the flat list of every
     * descendant that was trashed, as [{type, id}, …], for later restoration.
     */
    protected function cascadeSoftDeletes(): array
    {
        $cascaded = [];

        foreach ($this->auditCascades ?? [] as $relation) {
            foreach ($this->{$relation}()->get() as $child) {
                if (! in_array(SoftDeletes::class, class_uses_recursive($child), true)) {
                    continue;
                }

                $child->auditQuietly = true;
                $child->delete();

                $cascaded[] = ['type' => $child->getMorphClass(), 'id' => $child->getKey()];
                $cascaded = array_merge($cascaded, $child->auditCascadedCache);
            }
        }

        return $cascaded;
    }

    protected function recordAudit(string $action, array $changes): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $this->getMorphClass(),
            'subject_id' => $this->getKey(),
            'description' => $this->auditDescription($action),
            'changes' => $changes ?: null,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }

    /** Raw attribute snapshot, minus sensitive / noisy columns. */
    protected function auditableSnapshot(): array
    {
        return collect($this->getAttributes())->except($this->auditExcluded())->all();
    }

    /** @return array{old: array<string, mixed>, new: array<string, mixed>} */
    protected function auditableChanges(): array
    {
        $new = collect($this->getChanges())->except($this->auditExcluded())->all();
        $old = collect($this->getOriginal())->only(array_keys($new))->all();

        return ['old' => $old, 'new' => $new];
    }

    /** Columns never worth (or safe) recording. Override to extend. */
    protected function auditExcluded(): array
    {
        return array_merge([
            'password',
            'remember_token',
            'client_secret',
            'updated_at',
            'deleted_at',
        ], $this->getHidden());
    }

    protected function auditDescription(string $action): string
    {
        $verbs = [
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression',
            'restored' => 'Restauration',
        ];

        return ($verbs[$action] ?? $action).' — '.class_basename($this).' #'.$this->getKey();
    }
}
