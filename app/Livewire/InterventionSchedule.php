<?php

namespace App\Livewire;

use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\User;
use App\Services\Notifier;
use App\Support\Permissions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Plans the appointment (RDV) of an intervention and picks the technician(s)
 * who will handle it, showing everyone's availability for the chosen day so the
 * dispatcher can pick a free technician. Works both at creation (mode "form",
 * the values are submitted with the surrounding form) and from the intervention
 * sheet afterwards (mode "live", changes are persisted immediately).
 */
class InterventionSchedule extends Component
{
    #[Locked]
    public string $mode = 'form';

    #[Locked]
    public ?int $interventionId = null;

    public ?string $rdv_debut = null;

    public ?string $rdv_fin = null;

    /** @var array<int> */
    public array $selected = [];

    public function mount(
        string $mode = 'form',
        ?Intervention $intervention = null,
        ?string $rdvDebut = null,
        ?string $rdvFin = null,
        array $selected = [],
    ): void {
        $this->mode = $mode;

        if ($intervention && $intervention->exists) {
            $this->interventionId = $intervention->id;
            $this->rdv_debut = $intervention->rdv_debut?->format('Y-m-d\TH:i');
            $this->rdv_fin = $intervention->rdv_fin?->format('Y-m-d\TH:i');
            $this->selected = $intervention->techniciens()->pluck('users.id')->all();
        } else {
            $this->rdv_debut = $rdvDebut;
            $this->rdv_fin = $rdvFin;
            $this->selected = $selected;
        }
    }

    public function toggleTechnician(int $id): void
    {
        if (in_array($id, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }

        if ($this->mode === 'live') {
            $this->persist();
        }
    }

    /** Persist RDV + technicians directly (intervention sheet). */
    public function save(): void
    {
        $this->persist();
        $this->dispatch('schedule-saved');
    }

    private function persist(): void
    {
        abort_unless($this->mode === 'live' && $this->interventionId, 403);
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);

        $this->validate([
            'rdv_debut' => ['nullable', 'date'],
            'rdv_fin' => ['nullable', 'date', 'after_or_equal:rdv_debut'],
            'selected' => ['array'],
            'selected.*' => ['integer', 'exists:users,id'],
        ]);

        $intervention = Intervention::findOrFail($this->interventionId);

        $intervention->update([
            'rdv_debut' => $this->rdv_debut ?: null,
            'rdv_fin' => $this->rdv_fin ?: null,
            'rdv_annule' => false,
        ]);

        // Sync technicians, notifying newly assigned ones.
        $before = $intervention->techniciens()->pluck('users.id')->all();
        $pivot = collect($this->selected)->mapWithKeys(fn ($id) => [$id => ['assigned_at' => now()]])->all();
        $intervention->techniciens()->sync($pivot);

        foreach (array_diff($this->selected, $before) as $newId) {
            Notifier::toUser($newId, 'Intervention '.($intervention->reference ?? '#'.$intervention->id),
                'Vous avez été affecté à cette intervention.', route('interventions.show', $intervention));
        }

        InterventionLog::create([
            'intervention_id' => $intervention->id,
            'user_id' => Auth::id(),
            'texte' => 'a mis à jour le rendez-vous / l\'affectation',
            'created_at' => now(),
        ]);
        Notifier::interventionChanged($intervention, 'Rendez-vous / affectation mis à jour');
    }

    /**
     * Availability for the chosen day: for each active technician, the other
     * interventions they are already booked on (same day).
     */
    private function availability(): array
    {
        $day = $this->rdv_debut ? Carbon::parse($this->rdv_debut) : null;

        $techniciens = User::where('is_active', true)->orderBy('nom')->get();

        $bookings = collect();
        if ($day) {
            $bookings = Intervention::query()
                ->whereNotNull('rdv_debut')
                ->whereDate('rdv_debut', $day->toDateString())
                ->where('rdv_annule', false)
                ->when($this->interventionId, fn ($q) => $q->whereKeyNot($this->interventionId))
                ->with(['techniciens:id', 'client:id,nom,prenom,type'])
                ->get();
        }

        return $techniciens->map(function (User $u) use ($bookings) {
            $slots = $bookings->filter(fn (Intervention $i) => $i->techniciens->contains('id', $u->id))
                ->map(fn (Intervention $i) => [
                    'heure' => $i->rdv_debut->format('H:i'),
                    'client' => $i->client?->nomComplet(),
                ])->values()->all();

            return [
                'id' => $u->id,
                'nom' => $u->fullName(),
                'initials' => $u->initials(),
                'busy' => count($slots),
                'slots' => $slots,
            ];
        })->all();
    }

    public function render()
    {
        return view('livewire.intervention-schedule', [
            'technicians' => $this->availability(),
            'hasDay' => (bool) $this->rdv_debut,
        ]);
    }
}
