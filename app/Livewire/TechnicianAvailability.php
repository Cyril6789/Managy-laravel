<?php

namespace App\Livewire;

use App\Models\Intervention;
use App\Models\TechnicianAbsence;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Technician planning board: for a chosen day, shows every technician's real
 * agenda (intervention RDVs with time range + town) alongside their absences,
 * so dispatchers can pool visits and see who is off. Technicians can declare
 * their own absences (congés / maladie); staff managers can set anyone absent,
 * including a one-click "absent aujourd'hui".
 */
class TechnicianAvailability extends Component
{
    public string $date;

    // Absence form
    public ?int $absenceUserId = null;

    public string $absMotif = 'conges';

    public bool $absJournee = true;

    public ?string $absDebut = null;

    public ?string $absFin = null;

    public ?string $absNote = null;

    public bool $showForm = false;

    public function mount(?string $date = null): void
    {
        $this->date = $date && strtotime($date)
            ? Carbon::parse($date)->toDateString()
            : now()->toDateString();
        $this->absenceUserId = Auth::id();
    }

    public function canManageOthers(): bool
    {
        return Auth::user()?->can(Permissions::STAFF_MANAGE) ?? false;
    }

    private function day(): Carbon
    {
        return Carbon::parse($this->date);
    }

    public function prevDay(): void
    {
        $this->date = $this->day()->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $this->date = $this->day()->addDay()->toDateString();
    }

    public function goToday(): void
    {
        $this->date = now()->toDateString();
    }

    public function openForm(?int $userId = null): void
    {
        $this->resetValidation();
        $this->showForm = true;
        $this->absenceUserId = $this->canManageOthers() ? ($userId ?? Auth::id()) : Auth::id();
        $this->absMotif = 'conges';
        $this->absJournee = true;
        $this->absDebut = $this->date;
        $this->absFin = $this->date;
        $this->absNote = null;
    }

    public function updatedAbsJournee(): void
    {
        // Switch the date inputs between day (Y-m-d) and datetime granularity.
        if ($this->absJournee) {
            $this->absDebut = $this->absDebut ? Carbon::parse($this->absDebut)->toDateString() : $this->date;
            $this->absFin = $this->absFin ? Carbon::parse($this->absFin)->toDateString() : $this->date;
        } else {
            $this->absDebut = Carbon::parse($this->absDebut ?: $this->date)->setTime(9, 0)->format('Y-m-d\TH:i');
            $this->absFin = Carbon::parse($this->absFin ?: $this->date)->setTime(12, 0)->format('Y-m-d\TH:i');
        }
    }

    public function addAbsence(): void
    {
        $userId = $this->absenceUserId ?: Auth::id();
        $this->authorizeAbsence($userId);

        $rules = [
            'absMotif' => ['required', Rule::in(array_keys(TechnicianAbsence::MOTIFS))],
            'absNote' => ['nullable', 'string', 'max:255'],
            'absDebut' => ['required', 'date'],
            'absFin' => ['required', 'date', 'after_or_equal:absDebut'],
        ];
        $this->validate($rules);

        $debut = Carbon::parse($this->absDebut);
        $fin = Carbon::parse($this->absFin);
        if ($this->absJournee) {
            $debut = $debut->startOfDay();
            $fin = $fin->endOfDay();
        }

        TechnicianAbsence::create([
            'user_id' => $userId,
            'debut' => $debut,
            'fin' => $fin,
            'journee_entiere' => $this->absJournee,
            'motif' => $this->absMotif,
            'note' => $this->absNote,
            'created_by' => Auth::id(),
        ]);

        $this->showForm = false;
        $this->dispatch('absence-saved');
    }

    public function markAbsentToday(int $userId): void
    {
        $this->authorizeAbsence($userId);

        $day = $this->day();
        // Avoid duplicating an existing full-day absence for that day.
        $exists = TechnicianAbsence::where('user_id', $userId)
            ->where('journee_entiere', true)
            ->onDay($day)->exists();

        if (! $exists) {
            TechnicianAbsence::create([
                'user_id' => $userId,
                'debut' => $day->copy()->startOfDay(),
                'fin' => $day->copy()->endOfDay(),
                'journee_entiere' => true,
                'motif' => 'autre',
                'created_by' => Auth::id(),
            ]);
        }
    }

    public function deleteAbsence(int $id): void
    {
        $absence = TechnicianAbsence::findOrFail($id);
        $this->authorizeAbsence($absence->user_id);
        $absence->delete();
    }

    private function authorizeAbsence(int $userId): void
    {
        abort_unless($userId === Auth::id() || $this->canManageOthers(), 403);
    }

    public function render()
    {
        $day = $this->day();

        $technicians = User::where('is_active', true)->orderBy('nom')->get();

        $bookings = Intervention::query()
            ->whereNotNull('rdv_debut')
            ->whereDate('rdv_debut', $day->toDateString())
            ->where('rdv_annule', false)
            ->with(['techniciens:id', 'client:id,nom,prenom,type,ville'])
            ->get();

        $absencesByUser = TechnicianAbsence::onDay($day)
            ->with('creator:id,prenom,nom')
            ->get()->groupBy('user_id');

        $board = $technicians->map(function (User $u) use ($bookings, $absencesByUser, $day) {
            $slots = $bookings->filter(fn (Intervention $i) => $i->techniciens->contains('id', $u->id))
                ->sortBy('rdv_debut')
                ->map(fn (Intervention $i) => [
                    'id' => $i->id,
                    'debut' => $i->rdv_debut->format('H:i'),
                    'fin' => $i->rdv_fin?->format('H:i'),
                    'client' => $i->client?->nomComplet(),
                    'ville' => $i->client?->ville,
                    'domicile' => $i->type_lieu === 'domicile',
                    'reference' => $i->reference,
                    'url' => route('interventions.show', $i),
                ])->values();

            $absences = ($absencesByUser->get($u->id) ?? collect())
                ->sortBy('debut')
                ->map(fn (TechnicianAbsence $a) => [
                    'id' => $a->id,
                    'motif' => $a->motifLabel(),
                    'journee' => $a->journee_entiere,
                    'plage' => $a->journee_entiere
                        ? ($a->debut->isSameDay($a->fin) ? 'Journée entière' : $a->debut->format('d/m').' → '.$a->fin->format('d/m'))
                        : $a->debut->format('H:i').' – '.$a->fin->format('H:i'),
                    'note' => $a->note,
                ])->values();

            return [
                'id' => $u->id,
                'nom' => $u->fullName(),
                'initials' => $u->initials(),
                'slots' => $slots,
                'absences' => $absences,
                'absent' => $absences->isNotEmpty(),
                'is_self' => $u->id === Auth::id(),
            ];
        })->values();

        // Towns visited that day, grouped to surface pooling opportunities.
        $villesDuJour = $bookings
            ->filter(fn (Intervention $i) => $i->type_lieu === 'domicile' && $i->client?->ville)
            ->groupBy(fn (Intervention $i) => trim($i->client->ville))
            ->map->count()
            ->filter(fn ($n) => $n > 1)
            ->sortDesc();

        return view('livewire.technician-availability', [
            'day' => $day,
            'board' => $board,
            'technicians' => $technicians,
            'motifs' => TechnicianAbsence::MOTIFS,
            'villesDuJour' => $villesDuJour,
        ])->layout('layouts.app', ['title' => 'Disponibilités techniciens']);
    }
}
