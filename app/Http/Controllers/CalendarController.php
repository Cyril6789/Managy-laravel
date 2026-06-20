<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Event;
use App\Models\Intervention;
use App\Models\User;
use App\Support\Permissions;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permissions::CALENDAR_VIEW);

        $cursor = $request->filled('mois')
            ? Carbon::createFromFormat('Y-m', $request->string('mois')->toString())->startOfMonth()
            : now()->startOfMonth();

        $start = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $items = $this->itemsBetween($start, $end);

        // Group items per day (Y-m-d).
        $byDay = $items->groupBy(fn ($i) => $i['date']->format('Y-m-d'));

        $days = collect();
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $days->push([
                'date' => $d->copy(),
                'inMonth' => $d->month === $cursor->month,
                'isToday' => $d->isToday(),
                'items' => $byDay->get($d->format('Y-m-d'), collect())->sortBy('date')->values(),
            ]);
        }

        return view('calendar.index', [
            'cursor' => $cursor,
            'weeks' => $days->chunk(7),
            'clients' => Client::active()->orderBy('nom')->get(),
            'techniciens' => User::where('is_active', true)->orderBy('nom')->get(),
        ]);
    }

    public function events(Request $request)
    {
        $this->authorize(Permissions::CALENDAR_VIEW);

        $start = Carbon::parse($request->input('start', now()->startOfMonth()));
        $end = Carbon::parse($request->input('end', now()->endOfMonth()));

        return response()->json($this->itemsBetween($start, $end)->values());
    }

    public function store(Request $request)
    {
        $this->authorize(Permissions::CALENDAR_MANAGE);

        $data = $this->rules($request);
        $data['user_id'] = $data['user_id'] ?? $request->user()->id;
        Event::create($data);

        return back()->with('success', 'Rendez-vous ajouté.');
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize(Permissions::CALENDAR_MANAGE);

        $event->update($this->rules($request));

        return back()->with('success', 'Rendez-vous mis à jour.');
    }

    public function destroy(Event $event)
    {
        $this->authorize(Permissions::CALENDAR_MANAGE);

        $event->delete();

        return back()->with('success', 'Rendez-vous supprimé.');
    }

    private function itemsBetween(Carbon $start, Carbon $end)
    {
        $interventions = Intervention::ouvertes()
            ->whereNotNull('rdv_debut')
            ->whereBetween('rdv_debut', [$start, $end])
            ->where('rdv_annule', false)
            ->with('client')
            ->get()
            ->map(fn (Intervention $i) => [
                'type' => 'intervention',
                'id' => $i->id,
                'date' => $i->rdv_debut,
                'titre' => ($i->reference ? $i->reference.' · ' : '').($i->client?->nomComplet() ?? ''),
                'couleur' => $i->urgente ? '#ef4444' : '#2563eb',
                'url' => route('interventions.show', $i),
            ]);

        $events = Event::whereBetween('debut', [$start, $end])->with('client')->get()
            ->map(fn (Event $e) => [
                'type' => 'event',
                'id' => $e->id,
                'date' => $e->debut,
                'titre' => $e->titre,
                'couleur' => $e->couleur,
                'url' => null,
            ]);

        return $interventions->concat($events);
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'debut' => ['required', 'date'],
            'fin' => ['nullable', 'date', 'after_or_equal:debut'],
            'journee_entiere' => ['nullable', 'boolean'],
            'couleur' => ['nullable', 'string', 'max:9'],
        ]);
    }
}
