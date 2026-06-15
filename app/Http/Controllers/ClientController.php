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
            ->roots()
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

    public function create()
    {
        $this->authorize(Permissions::CLIENTS_MANAGE);

        return view('clients.create', [
            'client' => new Client(['type' => 'professionnel']),
            'parents' => Client::roots()->active()->orderBy('nom')->get(),
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

        $client->load(['contacts', 'parent']);
        $interventions = $client->interventions()->with('statut')->latest('opened_at')->limit(25)->get();

        return view('clients.show', compact('client', 'interventions'));
    }

    public function edit(Client $client)
    {
        $this->authorize(Permissions::CLIENTS_MANAGE);

        return view('clients.edit', [
            'client' => $client,
            'parents' => Client::roots()->active()->where('id', '!=', $client->id)->orderBy('nom')->get(),
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
        return $request->validate([
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
            'parent_id' => ['nullable', 'exists:clients,id'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
