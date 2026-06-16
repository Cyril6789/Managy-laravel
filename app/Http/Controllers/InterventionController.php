<?php

namespace App\Http\Controllers;

use App\Models\Antivirus;
use App\Models\Client;
use App\Models\CommentaireType;
use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\Materiel;
use App\Models\Prestation;
use App\Models\RapportType;
use App\Models\Statut;
use App\Models\SystemeExploitation;
use App\Models\User;
use App\Services\AutomatismeRunner;
use App\Services\Notifier;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InterventionController extends Controller
{
    public function __construct(private AutomatismeRunner $automatismes) {}

    public function index(Request $request)
    {
        $this->authorize(Permissions::INTERVENTIONS_VIEW);

        $voirTout = $request->user()->can(Permissions::INTERVENTIONS_VIEW_ALL);

        $interventions = Intervention::query()
            ->with(['client', 'statut', 'techniciens'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('reference', 'like', $term)
                    ->orWhere('panne', 'like', $term)
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)));
            })
            ->when($request->input('statut'), fn ($q, $s) => $q->where('statut_id', $s))
            ->when($request->input('technicien'), fn ($q, $t) => $q->whereHas('techniciens', fn ($w) => $w->where('users.id', $t)))
            ->when($request->input('etat') === 'cloturees', fn ($q) => $q->cloturees())
            ->when($request->input('etat', 'ouvertes') === 'ouvertes', fn ($q) => $q->ouvertes())
            // Without the "view all" right, only show interventions the user is assigned to.
            ->when(! $voirTout, fn ($q) => $q->whereHas('techniciens', fn ($w) => $w->where('users.id', $request->user()->id)))
            ->latest('opened_at')
            ->paginate(20)
            ->withQueryString();

        return view('interventions.index', [
            'interventions' => $interventions,
            'statuts' => Statut::orderBy('ordre')->get(),
            'techniciens' => User::where('is_active', true)->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize(Permissions::INTERVENTIONS_CREATE);

        return view('interventions.create', $this->formData(new Intervention([
            'type_lieu' => 'atelier',
            'statut_id' => Statut::where('est_defaut', true)->value('id'),
        ])));
    }

    public function store(Request $request)
    {
        $this->authorize(Permissions::INTERVENTIONS_CREATE);

        $data = $this->validateData($request);
        $data['opened_by'] = Auth::id();

        $intervention = Intervention::create($data);
        $intervention->techniciens()->attach(Auth::id(), ['assigned_at' => now()]);

        $this->log($intervention, "a créé l'intervention");
        $this->automatismes->fire('intervention_creee', $intervention);

        return redirect()->route('interventions.show', $intervention)->with('success', 'Intervention créée.');
    }

    public function show(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_VIEW);

        $intervention->load([
            'client', 'contact', 'materiel', 'systemeExploitation', 'antivirus', 'statut',
            'ouvreur', 'techniciens', 'prestations.prestation', 'commandes', 'sousTraitances',
            'messages.user', 'logs.user', 'clientMessages',
        ]);

        return view('interventions.show', [
            'intervention' => $intervention,
            'statuts' => Statut::orderBy('ordre')->get(),
            'prestationsCatalogue' => Prestation::orderBy('designation')->get(),
            'techniciens' => User::where('is_active', true)->orderBy('nom')->get(),
            'rapportTypes' => RapportType::orderBy('titre')->get(),
            'commentaireTypes' => CommentaireType::orderBy('titre')->get(),
        ]);
    }

    public function edit(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        return view('interventions.edit', $this->formData($intervention));
    }

    public function update(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $intervention->update($this->validateData($request));
        $this->log($intervention, 'a modifié les détails');
        Notifier::interventionChanged($intervention, 'Détails modifiés');

        return redirect()->route('interventions.show', $intervention)->with('success', 'Intervention mise à jour.');
    }

    public function destroy(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $intervention->delete();

        return redirect()->route('interventions.index')->with('success', 'Intervention supprimée.');
    }

    // ----- Lifecycle actions -------------------------------------------------

    public function updateStatut(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $request->validate(['statut_id' => ['required', 'exists:statuts,id']]);
        $statut = Statut::find($request->statut_id);

        $intervention->update(['statut_id' => $statut->id]);
        $this->log($intervention, 'a changé le statut en « '.$statut->nom.' »');
        Notifier::interventionChanged($intervention, 'Statut : '.$statut->nom);
        $this->automatismes->fire('changement_statut', $intervention);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function updateRdv(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $data = $request->validate([
            'rdv_debut' => ['nullable', 'date'],
            'rdv_fin' => ['nullable', 'date', 'after_or_equal:rdv_debut'],
            'type_lieu' => ['required', Rule::in(['atelier', 'domicile'])],
        ]);

        $intervention->update($data + ['rdv_annule' => false]);
        $this->log($intervention, 'a planifié un rendez-vous');
        Notifier::interventionChanged($intervention, 'Rendez-vous mis à jour');
        $this->automatismes->fire('changement_rdv', $intervention);

        return back()->with('success', 'Rendez-vous enregistré.');
    }

    public function togglePriseEnCharge(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_VIEW);

        $userId = Auth::id();
        if ($intervention->techniciens()->where('users.id', $userId)->exists()) {
            $intervention->techniciens()->detach($userId);
            $this->log($intervention, 'ne prend plus en charge l\'intervention');
        } else {
            $intervention->techniciens()->attach($userId, ['assigned_at' => now()]);
            $this->log($intervention, 'a pris en charge l\'intervention');
        }

        return back();
    }

    public function restituer(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $data = $request->validate([
            'diagnostic' => ['nullable', 'string'],
            'message_client' => ['nullable', 'string'],
            'materiel_ajoute' => ['nullable', 'string'],
        ]);

        $statutCloture = Statut::where('est_cloture', true)->orderBy('ordre')->first();

        $intervention->update($data + [
            'closed_at' => now(),
            'restituted_at' => now(),
            'restituted_by' => Auth::id(),
            'statut_id' => $statutCloture?->id ?? $intervention->statut_id,
        ]);

        $this->log($intervention, 'a restitué et clôturé l\'intervention');
        Notifier::interventionChanged($intervention, 'Intervention clôturée');
        $this->automatismes->fire('restitution', $intervention);

        return back()->with('success', 'Intervention clôturée.');
    }

    public function decloturer(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_DECLOTURE);

        $intervention->update(['closed_at' => null, 'restituted_at' => null, 'restituted_by' => null]);
        $this->log($intervention, 'a déclôturé l\'intervention');

        return back()->with('success', 'Intervention déclôturée.');
    }

    public function toggleFacturation(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_FACTURATION);

        $intervention->update(['facturee' => ! $intervention->facturee]);
        $this->log($intervention, $intervention->facturee ? 'a marqué comme facturée' : 'a retiré la facturation');

        return back();
    }

    // ----- Helpers -----------------------------------------------------------

    private function formData(Intervention $intervention): array
    {
        return [
            'intervention' => $intervention,
            'clients' => Client::active()->orderBy('nom')->get(),
            'materiels' => Materiel::orderBy('nom')->get(),
            'systemes' => SystemeExploitation::orderBy('nom')->get(),
            'antivirus' => Antivirus::orderBy('nom')->get(),
            'statuts' => Statut::orderBy('ordre')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'contact_id' => ['nullable', 'exists:clients,id'],
            'materiel_id' => ['nullable', 'exists:materiels,id'],
            'systeme_exploitation_id' => ['nullable', 'exists:systeme_exploitations,id'],
            'antivirus_id' => ['nullable', 'exists:antivirus,id'],
            'statut_id' => ['nullable', 'exists:statuts,id'],
            'type_lieu' => ['required', Rule::in(['atelier', 'domicile'])],
            'rdv_debut' => ['nullable', 'date'],
            'rdv_fin' => ['nullable', 'date', 'after_or_equal:rdv_debut'],
            'priorite' => ['nullable', 'integer', 'between:0,3'],
            'urgente' => ['nullable', 'boolean'],
            'garantie' => ['nullable', 'boolean'],
            'materiel_depose' => ['nullable', 'string'],
            'panne' => ['nullable', 'string'],
            'diagnostic' => ['nullable', 'string'],
            'message_interne' => ['nullable', 'string'],
            'mdp' => ['nullable', 'string', 'max:255'],
            'tarif_estimatif' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function log(Intervention $intervention, string $texte): void
    {
        InterventionLog::create([
            'intervention_id' => $intervention->id,
            'user_id' => Auth::id(),
            'texte' => $texte,
            'created_at' => now(),
        ]);
    }
}
