<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\InterventionPrestation;
use App\Models\User;
use App\Support\Permissions;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize(Permissions::STATS_VIEW);

        $from = $request->filled('from') ? Carbon::parse($request->date('from')) : now()->subMonths(5)->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->date('to'))->endOfDay() : now()->endOfDay();

        // Interventions opened per month (last 6 months by default).
        $parMois = collect();
        for ($m = $from->copy()->startOfMonth(); $m <= $to; $m->addMonth()) {
            $parMois->push([
                'label' => $m->translatedFormat('M Y'),
                'ouvertes' => Intervention::whereBetween('opened_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->count(),
                'cloturees' => Intervention::whereBetween('closed_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->count(),
            ]);
        }

        // Hours per technician over the period — only CLOSED (billable) interventions.
        $heuresParTech = User::query()
            ->select('users.id', 'users.prenom', 'users.nom')
            ->selectSub(
                InterventionPrestation::selectRaw('COALESCE(SUM(duree),0)')
                    ->join('interventions', 'interventions.id', '=', 'intervention_prestations.intervention_id')
                    ->join('intervention_user', 'intervention_user.intervention_id', '=', 'interventions.id')
                    ->whereColumn('intervention_user.user_id', 'users.id')
                    ->whereNotNull('interventions.closed_at')
                    ->whereBetween('interventions.closed_at', [$from, $to]),
                'heures'
            )
            ->where('is_active', true)
            ->orderByDesc('heures')
            ->get();

        $totaux = [
            'total' => Intervention::whereBetween('opened_at', [$from, $to])->count(),
            'cloturees' => Intervention::whereBetween('closed_at', [$from, $to])->count(),
            'heures' => (float) InterventionPrestation::whereHas('intervention', fn ($q) => $q->whereBetween('opened_at', [$from, $to]))->sum('duree'),
            'ca_estime' => (float) Intervention::whereBetween('opened_at', [$from, $to])->sum('tarif_estimatif'),
        ];

        return view('stats.index', compact('parMois', 'heuresParTech', 'totaux', 'from', 'to'));
    }
}
