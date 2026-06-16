<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\MaintenanceMovement;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permissions::MAINTENANCE_VIEW);

        // Customers having at least one maintenance movement, with their balance.
        $clients = Client::query()
            ->select('clients.*')
            ->selectSub(
                MaintenanceMovement::selectRaw('COALESCE(SUM(mouvement),0)')
                    ->whereColumn('client_id', 'clients.id'),
                'solde'
            )
            ->whereHas('maintenanceMovements')
            ->orderBy('nom')
            ->paginate(25);

        return view('maintenance.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $this->authorize(Permissions::MAINTENANCE_VIEW);

        $mouvements = $client->maintenanceMovements()->with(['intervention', 'user'])->latest()->get();

        return view('maintenance.show', [
            'client' => $client,
            'mouvements' => $mouvements,
            'solde' => $client->soldeMaintenance(),
        ]);
    }

    public function store(Request $request, Client $client)
    {
        $this->authorize(Permissions::MAINTENANCE_MANAGE);

        $data = $request->validate([
            'sens' => ['required', 'in:credit,debit'],
            'heures' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'intervention_id' => ['nullable', 'exists:interventions,id'],
        ]);

        $client->maintenanceMovements()->create([
            'mouvement' => $data['sens'] === 'credit' ? $data['heures'] : -$data['heures'],
            'description' => $data['description'] ?? null,
            'intervention_id' => $data['intervention_id'] ?? null,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Mouvement enregistré.');
    }

    public function destroy(MaintenanceMovement $movement)
    {
        $this->authorize(Permissions::MAINTENANCE_MANAGE);

        $client = $movement->client;
        $movement->delete();

        return redirect()->route('maintenance.show', $client)->with('success', 'Mouvement supprimé.');
    }
}
