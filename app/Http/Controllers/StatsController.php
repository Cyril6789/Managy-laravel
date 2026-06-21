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

        // ---- Breakdowns (interventions opened within the period) ----------------

        // Device type distribution.
        $parMateriel = Intervention::query()
            ->leftJoin('materiels', 'materiels.id', '=', 'interventions.materiel_id')
            ->whereBetween('interventions.opened_at', [$from, $to])
            ->selectRaw("COALESCE(materiels.nom, 'Non renseigné') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Intervention type = on-site vs workshop.
        $libelleLieu = ['atelier' => 'Atelier', 'domicile' => 'Domicile'];
        $parLieu = Intervention::query()
            ->whereBetween('opened_at', [$from, $to])
            ->selectRaw("COALESCE(NULLIF(type_lieu, ''), 'Non renseigné') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['label' => $libelleLieu[$r->label] ?? $r->label, 'total' => $r->total]);

        // Status distribution (keeps each status colour for the badge dot).
        $parStatut = Intervention::query()
            ->leftJoin('statuts', 'statuts.id', '=', 'interventions.statut_id')
            ->whereBetween('interventions.opened_at', [$from, $to])
            ->selectRaw("COALESCE(statuts.nom, 'Sans statut') as label, COALESCE(statuts.couleur, '#64748b') as couleur, COUNT(*) as total")
            ->groupBy('label', 'couleur')
            ->orderByDesc('total')
            ->get();

        // ---- Billed averages (over interventions closed within the period) ------

        $cloturees = Intervention::cloturees()->whereBetween('closed_at', [$from, $to]);
        $nbCloturees = $totaux['cloturees'];

        $heuresFacturees = (float) InterventionPrestation::query()
            ->join('interventions', 'interventions.id', '=', 'intervention_prestations.intervention_id')
            ->whereNotNull('interventions.closed_at')
            ->whereBetween('interventions.closed_at', [$from, $to])
            ->sum('duree');

        $delais = (clone $cloturees)->get(['opened_at', 'closed_at']);

        $moyennes = [
            'duree' => $nbCloturees ? $heuresFacturees / $nbCloturees : 0.0,
            'pieces' => $nbCloturees ? (float) (clone $cloturees)->sum('montant_pieces') / $nbCloturees : 0.0,
            'panier' => $nbCloturees ? (float) (clone $cloturees)->sum('montant_total') / $nbCloturees : 0.0,
            'delai' => $delais->isNotEmpty()
                ? $delais->avg(fn ($i) => $i->opened_at->diffInDays($i->closed_at))
                : 0.0,
        ];

        // Revenue per month (closed interventions), split services vs parts.
        $caParMois = collect();
        for ($m = $from->copy()->startOfMonth(); $m <= $to; $m->addMonth()) {
            $base = Intervention::cloturees()
                ->whereBetween('closed_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()]);
            $caParMois->push([
                'label' => $m->translatedFormat('M Y'),
                'prestations' => (float) (clone $base)->sum('montant_prestations'),
                'pieces' => (float) (clone $base)->sum('montant_pieces'),
            ]);
        }

        return view('stats.index', compact(
            'parMois', 'heuresParTech', 'totaux', 'from', 'to',
            'parMateriel', 'parLieu', 'parStatut', 'moyennes', 'caParMois'
        ));
    }
}
