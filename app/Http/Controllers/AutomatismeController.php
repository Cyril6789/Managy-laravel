<?php

namespace App\Http\Controllers;

use App\Models\Automatisme;
use App\Models\Statut;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AutomatismeController extends Controller
{
    private const EVENTS = [
        'intervention_creee' => 'À la création d\'une intervention',
        'changement_statut' => 'Au changement de statut',
        'changement_rdv' => 'Au changement de rendez-vous',
        'commande_recue' => 'À la réception d\'une commande',
        'sous_traitance_retour' => 'Au retour de sous-traitance',
        'restitution' => 'À la restitution / clôture',
    ];

    public function index()
    {
        $this->authorize(Permissions::AUTOMATISMES_MANAGE);

        return view('automatismes.index', [
            'automatismes' => Automatisme::with('statut')->orderBy('evenement')->get(),
            'events' => self::EVENTS,
        ]);
    }

    public function create()
    {
        $this->authorize(Permissions::AUTOMATISMES_MANAGE);

        return view('automatismes.create', $this->formData(new Automatisme(['canal' => 'sms', 'actif' => true])));
    }

    public function store(Request $request)
    {
        $this->authorize(Permissions::AUTOMATISMES_MANAGE);

        Automatisme::create($this->rules($request));

        return redirect()->route('automatismes.index')->with('success', 'Automatisme créé.');
    }

    public function edit(Automatisme $automatisme)
    {
        $this->authorize(Permissions::AUTOMATISMES_MANAGE);

        return view('automatismes.edit', $this->formData($automatisme));
    }

    public function update(Request $request, Automatisme $automatisme)
    {
        $this->authorize(Permissions::AUTOMATISMES_MANAGE);

        $automatisme->update($this->rules($request));

        return redirect()->route('automatismes.index')->with('success', 'Automatisme mis à jour.');
    }

    public function destroy(Automatisme $automatisme)
    {
        $this->authorize(Permissions::AUTOMATISMES_MANAGE);

        $automatisme->delete();

        return back()->with('success', 'Automatisme supprimé.');
    }

    private function formData(Automatisme $automatisme): array
    {
        return [
            'automatisme' => $automatisme,
            'events' => self::EVENTS,
            'statuts' => Statut::orderBy('ordre')->get(),
        ];
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'evenement' => ['required', Rule::in(array_keys(self::EVENTS))],
            'statut_id' => ['nullable', 'exists:statuts,id'],
            'canal' => ['required', 'in:sms,email'],
            'sujet' => ['nullable', 'string', 'max:255'],
            'modele' => ['required', 'string'],
            'actif' => ['nullable', 'boolean'],
        ]);
    }
}
