<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permissions::CLIENTS_VIEW);

        $clients = Client::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('nom', 'like', $term)
                    ->orWhere('prenom', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('ville', 'like', $term)
                    ->orWhere('telephone_mobile', 'like', $term)
                    ->orWhere('telephone_fixe', 'like', $term));
            })
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->when(! $request->boolean('archived'), fn ($q) => $q->active())
            ->withCount('interventions')
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    /** Live search (JSON) for the searchable client picker. */
    public function search(Request $request)
    {
        $this->authorize(Permissions::CLIENTS_VIEW);

        $term = '%'.$request->string('q').'%';

        return Client::active()
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->where(fn ($w) => $w->where('nom', 'like', $term)
                ->orWhere('prenom', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('ville', 'like', $term)
                ->orWhere('telephone_mobile', 'like', $term)
                ->orWhere('telephone_fixe', 'like', $term))
            ->orderBy('nom')
            ->limit(15)
            ->get()
            ->map(fn (Client $c) => ['id' => $c->id, 'label' => $c->nomComplet(), 'ville' => $c->ville]);
    }

    /** Inline creation (JSON) from the client picker. */
    public function quickStore(Request $request)
    {
        $this->authorize(Permissions::CLIENTS_MANAGE);

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'in:professionnel,particulier'],
        ]);

        $client = Client::create([
            'nom' => $data['nom'],
            'type' => $data['type'] ?? 'particulier',
        ]);

        return response()->json(['id' => $client->id, 'label' => $client->nomComplet(), 'ville' => $client->ville]);
    }

    public function create()
    {
        $this->authorize(Permissions::CLIENTS_MANAGE);

        return view('clients.create', [
            'client' => new Client(['type' => 'professionnel']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize(Permissions::CLIENTS_MANAGE);

        $client = Client::create($this->validated($request));

        return redirect()->route('clients.show', $client)->with('success', 'Client créé.');
    }

    public function show(Client $client)
    {
        $this->authorize(Permissions::CLIENTS_VIEW);

        $client->load(['contacts', 'companies']);
        $interventions = $client->interventions()->with('statut')->latest('opened_at')->limit(25)->get();
        $messages = $client->messages()->with('intervention')->limit(50)->get();

        return view('clients.show', [
            'client' => $client,
            'interventions' => $interventions,
            'messages' => $messages,
            'soldeMaintenance' => $client->soldeMaintenance(),
            'aPackMaintenance' => $client->maintenanceMovements()->exists(),
        ]);
    }

    public function edit(Client $client)
    {
        $this->authorize(Permissions::CLIENTS_MANAGE);

        return view('clients.edit', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize(Permissions::CLIENTS_MANAGE);

        $client->update($this->validated($request, $client));

        return redirect()->route('clients.show', $client)->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $this->authorize(Permissions::CLIENTS_MANAGE);

        if ($client->interventions()->exists()) {
            return back()->with('error', 'Impossible de supprimer un client ayant des interventions. Archivez-le plutôt.');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client supprimé.');
    }

    public function archive(Client $client)
    {
        $this->authorize(Permissions::CLIENTS_MANAGE);

        $client->update(['archived_at' => $client->archived_at ? null : now()]);

        return back()->with('success', $client->archived_at ? 'Client archivé.' : 'Client réactivé.');
    }

    private function validated(Request $request, ?Client $client = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['professionnel', 'particulier'])],
            'civilite' => ['nullable', 'string', 'max:20'],
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telephone_fixe' => ['nullable', 'string', 'max:30'],
            'telephone_mobile' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'adresse_complement' => ['nullable', 'string', 'max:255'],
            'code_postal' => ['nullable', 'string', 'max:10'],
            'ville' => ['nullable', 'string', 'max:255'],
            'siret' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        // A particulier never carries a SIRET (only companies do).
        if (($data['type'] ?? null) === 'particulier') {
            $data['siret'] = null;
        }

        // Free-travel flag and per-customer discounts require a dedicated right.
        if ($request->user()->can(Permissions::CLIENTS_REMISES)) {
            $data += $request->validate([
                'deplacement_gratuit' => ['nullable', 'boolean'],
                'remise_prestations' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'remise_pieces' => ['nullable', 'numeric', 'min:0', 'max:100'],
            ]);
            $data['deplacement_gratuit'] = $request->boolean('deplacement_gratuit');
        }

        return $data;
    }
}
