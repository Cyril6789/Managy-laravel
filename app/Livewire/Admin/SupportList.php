<?php

namespace App\Livewire\Admin;

use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Cross-société supervision list of every assistance ticket, for the platform
 * super-admin. Their tenancy is null, so the tickets are not scoped; each row
 * surfaces the requester and the société it belongs to.
 *
 * Fully filterable, searchable and sortable per column, and defaulting to the
 * tickets that still need attention (non clôturés).
 */
class SupportList extends Component
{
    use WithPagination;

    /** Status filter. The pseudo value "non_clotures" hides résolus / fermés. */
    #[Url]
    public string $statut = 'non_clotures';

    #[Url]
    public string $type = '';

    #[Url]
    public string $urgence = '';

    #[Url]
    public string $societyId = '';

    #[Url]
    public string $q = '';

    /** Sortable column key (see SORTS) and direction. */
    #[Url]
    public string $sort = 'activite';

    #[Url]
    public string $dir = 'desc';

    /** Column key => how to order it. */
    private const SORTS = ['reference', 'sujet', 'societe', 'demandeur', 'type', 'urgence', 'statut', 'activite', 'cree'];

    public function updating($name): void
    {
        if (in_array($name, ['statut', 'type', 'urgence', 'societyId', 'q'], true)) {
            $this->resetPage();
        }
    }

    /** Toggle sort direction when re-clicking a column, otherwise sort ascending. */
    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTS, true)) {
            return;
        }

        if ($this->sort === $field) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->dir = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['type', 'urgence', 'societyId', 'q']);
        $this->statut = 'non_clotures';
        $this->resetPage();
    }

    private function applySort(Builder $query): Builder
    {
        $dir = $this->dir === 'asc' ? 'asc' : 'desc';

        return match ($this->sort) {
            'reference' => $query->orderBy('id', $dir),
            'sujet' => $query->orderBy('sujet', $dir),
            'type' => $query->orderBy('type', $dir),
            // Logical (not alphabetical) ordering for urgency & status.
            'urgence' => $query->orderByRaw(
                "CASE urgence WHEN 'basse' THEN 0 WHEN 'normale' THEN 1 WHEN 'haute' THEN 2 WHEN 'urgente' THEN 3 ELSE 4 END $dir"
            ),
            'statut' => $query->orderByRaw(
                "CASE statut WHEN 'ouvert' THEN 0 WHEN 'en_cours' THEN 1 WHEN 'en_attente' THEN 2 WHEN 'resolu' THEN 3 WHEN 'ferme' THEN 4 ELSE 5 END $dir"
            ),
            // Related columns ordered through a correlated sub-query (no join needed).
            'societe' => $query->orderBy(
                Society::select('name')->whereColumn('societies.id', 'support_tickets.society_id'), $dir
            ),
            'demandeur' => $query->orderBy(
                User::select('nom')->whereColumn('users.id', 'support_tickets.user_id'), $dir
            ),
            'cree' => $query->orderBy('created_at', $dir),
            default => $query->orderByDesc('last_reply_at')->orderByDesc('created_at'),
        };
    }

    public function render()
    {
        $query = SupportTicket::query()->withoutGlobalScope('society')
            ->with(['user', 'society'])
            ->withCount('messages')
            ->when($this->statut === 'non_clotures', fn ($q) => $q->open())
            ->when(! in_array($this->statut, ['', 'non_clotures'], true), fn ($q) => $q->where('statut', $this->statut))
            ->when($this->type !== '', fn ($q) => $q->where('type', $this->type))
            ->when($this->urgence !== '', fn ($q) => $q->where('urgence', $this->urgence))
            ->when($this->societyId !== '', fn ($q) => $q->where('society_id', $this->societyId))
            ->when($this->q !== '', fn ($q) => $q->where(function ($w) {
                $term = '%'.$this->q.'%';
                $w->where('sujet', 'like', $term)
                    ->orWhereHas('society', fn ($s) => $s->where('name', 'like', $term))
                    ->orWhereHas('user', fn ($u) => $u->where('nom', 'like', $term)->orWhere('prenom', 'like', $term));

                // "A-00012", "#12" or "12" → match the ticket id behind the reference.
                if ($id = (int) preg_replace('/\D/', '', $this->q)) {
                    $w->orWhere('id', $id);
                }
            }));

        $tickets = $this->applySort($query)->paginate(30);

        // Status counters for the quick-filter chips (unaffected by the chips themselves).
        $counts = SupportTicket::query()->withoutGlobalScope('society')
            ->selectRaw('statut, count(*) as c')->groupBy('statut')->pluck('c', 'statut');

        $societies = Society::query()
            ->whereIn('id', SupportTicket::query()->withoutGlobalScope('society')->select('society_id'))
            ->orderBy('name')->get();

        return view('livewire.admin.support-list', [
            'tickets' => $tickets,
            'counts' => $counts,
            'openCount' => (int) $counts->except(['resolu', 'ferme'])->sum(),
            'societies' => $societies,
        ]);
    }
}
