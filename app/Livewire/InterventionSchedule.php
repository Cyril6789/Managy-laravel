<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\TechnicianAbsence;
use App\Models\User;
use App\Services\Notifier;
use App\Support\Permissions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Plans the appointment (RDV) of an intervention and picks the technician(s)
 * who will handle it. For the chosen day it shows, per technician, their real
 * agenda — each booking's time range and the town they travel to — so the
 * dispatcher can fill gaps, pool two visits in the same town, and avoid
 * technicians who are off (congés / maladie). Works both at creation
 * (mode "form", values submitted with the surrounding form) and from the
 * intervention sheet afterwards (mode "live", changes persisted immediately).
 */
class InterventionSchedule extends Component
{
    #[Locked]
    public string $mode = 'form';

    #[Locked]
    public ?int $interventionId = null;

    public ?int $clientId = null;

    /** Town of the current intervention's client (for same-town pooling hints). */
    public ?string $clientVille = null;

    public ?string $rdv_debut = null;

    public ?string $rdv_fin = null;

    /** @var array<int> */
    public array $selected = [];

    public function mount(
        string $mode = 'form',
        ?Intervention $intervention = null,
        ?int $clientId = null,
        ?string $rdvDebut = null,
        ?string $rdvFin = null,
        array $selected = [],
    ): void {
        $this->mode = $mode;

        if ($intervention && $intervention->exists) {
            $this->interventionId = $intervention->id;
            $this->clientId = $intervention->client_id;
            $this->rdv_debut = $intervention->rdv_debut?->format('Y-m-d\TH:i');
            $this->rdv_fin = $intervention->rdv_fin?->format('Y-m-d\TH:i');
            $this->selected = $intervention->techniciens()->pluck('users.id')->all();
        } else {
            $this->clientId = $clientId;
            $this->rdv_debut = $rdvDebut;
            $this->rdv_fin = $rdvFin;
            $this->selected = $selected;
        }

        $this->refreshClientVille();
    }

    /** Track the client chosen in the surrounding (creation) form. */
    #[On('client-selected')]
    public function onClientSelected(int $id): void
    {
        $this->clientId = $id;
        $this->refreshClientVille();
    }

    private function refreshClientVille(): void
    {
        $this->clientVille = $this->clientId
            ? Client::whereKey($this->clientId)->value('ville')
            : null;
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
     * Availability for the chosen day. For each active technician: their other
     * bookings that day (time range + town), any absence covering the day, and
     * whether they are free / busy / unavailable for the requested RDV window.
     */
    private function availability(): array
    {
        $day = $this->rdv_debut ? Carbon::parse($this->rdv_debut) : null;
        $rdvEnd = $this->rdv_fin ? Carbon::parse($this->rdv_fin) : ($day?->copy()->addHour());
        $targetVille = $this->normalizeVille($this->clientVille);

        $techniciens = User::where('is_active', true)->orderBy('nom')->get();

        $bookings = collect();
        $absences = collect();
        if ($day) {
            $bookings = Intervention::query()
                ->whereNotNull('rdv_debut')
                ->whereDate('rdv_debut', $day->toDateString())
                ->where('rdv_annule', false)
                ->when($this->interventionId, fn ($q) => $q->whereKeyNot($this->interventionId))
                ->with(['techniciens:id', 'client:id,nom,prenom,type,ville,adresse,code_postal'])
                ->get();

            $absences = TechnicianAbsence::onDay($day)->get()->groupBy('user_id');
        }

        return $techniciens->map(function (User $u) use ($bookings, $absences, $day, $rdvEnd, $targetVille) {
            $slots = $bookings->filter(fn (Intervention $i) => $i->techniciens->contains('id', $u->id))
                ->sortBy('rdv_debut')
                ->map(function (Intervention $i) use ($targetVille) {
                    $ville = $i->client?->ville;

                    return [
                        'debut' => $i->rdv_debut->format('H:i'),
                        'fin' => $i->rdv_fin?->format('H:i'),
                        'client' => $i->client?->nomComplet(),
                        'ville' => $ville,
                        'reference' => $i->reference,
                        'domicile' => $i->type_lieu === 'domicile',
                        'same_ville' => $targetVille && $this->normalizeVille($ville) === $targetVille,
                    ];
                })->values()->all();

            // Absences covering the day, and whether one hits the RDV window.
            $dayAbsences = ($absences->get($u->id) ?? collect());
            $absenceLabels = $dayAbsences->map(fn (TechnicianAbsence $a) => [
                'motif' => $a->motifLabel(),
                'plage' => $a->journee_entiere
                    ? 'journée'
                    : $a->debut->format('H:i').'–'.$a->fin->format('H:i'),
            ])->values()->all();

            $unavailable = $day
                ? $dayAbsences->contains(fn (TechnicianAbsence $a) => $a->covers($day, $rdvEnd))
                : false;

            $samVilleCount = collect($slots)->where('same_ville', true)->count();

            return [
                'id' => $u->id,
                'nom' => $u->fullName(),
                'initials' => $u->initials(),
                'busy' => count($slots),
                'slots' => $slots,
                'absences' => $absenceLabels,
                'unavailable' => $unavailable,
                'same_ville_count' => $samVilleCount,
            ];
        })
            // Free & available first, unavailable (absent) last.
            ->sortBy(fn ($t) => [$t['unavailable'] ? 1 : 0, $t['busy'] === 0 ? 0 : 1])
            ->values()
            ->all();
    }

    private function normalizeVille(?string $ville): ?string
    {
        $ville = trim((string) $ville);

        return $ville === '' ? null : mb_strtolower($ville);
    }

    public function render()
    {
        return view('livewire.intervention-schedule', [
            'technicians' => $this->availability(),
            'hasDay' => (bool) $this->rdv_debut,
            'clientVille' => $this->clientVille,
        ]);
    }
}
