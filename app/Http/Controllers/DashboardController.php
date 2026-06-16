<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\Statut;
use App\Models\StickyNote;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        $stats = [
            'ouvertes' => Intervention::ouvertes()->count(),
            'urgentes' => Intervention::ouvertes()->where('urgente', true)->count(),
            'cloturees_mois' => Intervention::cloturees()->where('closed_at', '>=', now()->startOfMonth())->count(),
            'a_facturer' => Intervention::cloturees()->where('facturee', false)->count(),
        ];

        // My ongoing interventions
        $mesInterventions = Intervention::ouvertes()
            ->whereHas('techniciens', fn ($q) => $q->where('users.id', $user->id))
            ->with(['client', 'statut'])
            ->latest('opened_at')
            ->limit(8)
            ->get();

        // Today's appointments
        $rdvJour = Intervention::ouvertes()
            ->whereNotNull('rdv_debut')
            ->whereBetween('rdv_debut', [now()->startOfDay(), now()->endOfDay()])
            ->with(['client'])
            ->orderBy('rdv_debut')
            ->get();

        // Distribution by status (for a simple bar chart)
        $parStatut = Statut::withCount(['interventions' => fn ($q) => $q->whereNull('closed_at')])
            ->orderBy('ordre')
            ->get();

        $mesTaches = Task::where('user_id', $user->id)
            ->where('statut', '!=', 'terminee')
            ->orderByRaw('CASE WHEN echeance IS NULL THEN 1 ELSE 0 END, echeance')
            ->limit(6)
            ->get();

        $postits = StickyNote::where('user_id', $user->id)->orderBy('ordre')->get();

        return view('dashboard', compact('stats', 'mesInterventions', 'rdvJour', 'parStatut', 'mesTaches', 'postits'));
    }
}
