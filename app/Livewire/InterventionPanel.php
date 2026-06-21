<?php

namespace App\Livewire;

use App\Models\Commande;
use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\InterventionPiece;
use App\Models\InterventionPrestation;
use App\Models\Prestation;
use App\Models\SousTraitance;
use App\Models\Statut;
use App\Services\AutomatismeRunner;
use App\Services\Notifier;
use App\Support\InterventionStatus;
use App\Support\Permissions;
use App\Support\Reception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class InterventionPanel extends Component
{
    public Intervention $intervention;

    public string $tab = 'intervention';

    public ?int $statutId = null;

    public array $presta = ['prestation_id' => '', 'designation' => '', 'duree' => ''];

    public array $piece = ['designation' => '', 'prix' => '', 'quantite' => '1'];

    public array $commande = ['fournisseur' => '', 'numero_commande' => '', 'suivi_colis' => ''];

    public array $sst = ['nom' => '', 'devis' => ''];

    /** Accès / mot de passe — éditable dans l'onglet « Intervention » (spécificités). */
    public ?string $mdp = null;

    /** Tarif estimatif — rangé dans la zone « spécificités », sous le mot de passe. */
    public ?string $tarif_estimatif = null;

    public function mount(Intervention $intervention): void
    {
        $this->intervention = $intervention;
        $this->statutId = $intervention->statut_id;
        $this->mdp = $intervention->mdp;
        $this->tarif_estimatif = $intervention->tarif_estimatif;
    }

    public function getCanManageProperty(): bool
    {
        return Auth::user()->can(Permissions::INTERVENTIONS_MANAGE);
    }

    // ----- Status ------------------------------------------------------------

    public function changeStatut(AutomatismeRunner $automatismes): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $this->validate(['statutId' => ['required', 'exists:statuts,id']]);

        $statut = Statut::find($this->statutId);
        $this->intervention->update(['statut_id' => $statut->id]);
        $this->log('a changé le statut en « '.$statut->nom.' »');
        Notifier::interventionChanged($this->intervention, 'Statut : '.$statut->nom);
        $automatismes->fire('changement_statut', $this->intervention);
    }

    // ----- Spécificités (accès / mot de passe, tarif estimatif) --------------

    /** Auto-save the access password when the field loses focus (wire:model.blur). */
    public function updatedMdp(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $this->validate(['mdp' => ['nullable', 'string', 'max:255']]);

        $this->intervention->update(['mdp' => $this->mdp]);
    }

    /** Auto-save the estimated price (sits under the password, in the specs box). */
    public function updatedTarifEstimatif(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $this->tarif_estimatif = $this->normaliserDecimale($this->tarif_estimatif);
        $this->validate(['tarif_estimatif' => ['nullable', 'numeric', 'min:0']]);

        $this->intervention->update(['tarif_estimatif' => $this->tarif_estimatif ?: null]);
    }

    // ----- Prestations -------------------------------------------------------

    public function selectPrestation(): void
    {
        if ($p = Prestation::find($this->presta['prestation_id'])) {
            $this->presta['designation'] = $p->designation;
            $this->presta['duree'] = rtrim(rtrim(number_format((float) $p->duree_defaut, 2), '0'), '.');
        }
    }

    public function addPrestation(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $this->presta['duree'] = $this->normaliserDecimale($this->presta['duree']);
        $this->validate([
            'presta.designation' => ['nullable', 'string', 'max:255'],
            'presta.prestation_id' => ['nullable', 'exists:prestations,id'],
            'presta.duree' => ['required', 'numeric', 'min:0'],
        ]);

        // The price always comes from the catalogue (Paramètres), not from the
        // technician. Free-text lines have no price.
        $prestation = $this->presta['prestation_id'] ? Prestation::find($this->presta['prestation_id']) : null;
        $designation = $this->presta['designation'] ?: $prestation?->designation;

        if (! $designation) {
            $this->addError('presta.designation', 'Désignation requise.');

            return;
        }

        $this->intervention->prestations()->create([
            'prestation_id' => $prestation?->id,
            'designation' => $designation,
            'duree' => $this->presta['duree'],
            'tarif' => $prestation?->tarif,
        ]);
        $this->presta = ['prestation_id' => '', 'designation' => '', 'duree' => ''];
        $this->log('a ajouté une prestation');
        $this->itemsChanged();
    }

    public function deletePrestation(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        InterventionPrestation::where('intervention_id', $this->intervention->id)->findOrFail($id)->delete();
        $this->itemsChanged();
    }

    // ----- Pièces (replaced parts, ad-hoc) -----------------------------------

    public function addPiece(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $this->piece['prix'] = $this->normaliserDecimale($this->piece['prix']);
        $this->piece['quantite'] = $this->normaliserDecimale($this->piece['quantite']);
        $this->validate([
            'piece.designation' => ['required', 'string', 'max:255'],
            'piece.prix' => ['required', 'numeric', 'min:0'],
            'piece.quantite' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->intervention->pieces()->create($this->piece);
        $this->piece = ['designation' => '', 'prix' => '', 'quantite' => '1'];
        $this->log('a ajouté une pièce');
        $this->itemsChanged();
    }

    public function deletePiece(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        InterventionPiece::where('intervention_id', $this->intervention->id)->findOrFail($id)->delete();
        $this->itemsChanged();
    }

    /**
     * Tell the sibling "clôture" component (finalisation / restitution) that the
     * billable items changed, so its total recomputes live — no page reload.
     */
    private function itemsChanged(): void
    {
        $this->dispatch('intervention-items-updated');
    }

    // ----- Commandes ---------------------------------------------------------

    public function addCommande(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $this->validate(['commande.fournisseur' => ['nullable', 'string', 'max:255']]);

        $this->intervention->commandes()->create($this->commande);
        $this->commande = ['fournisseur' => '', 'numero_commande' => '', 'suivi_colis' => ''];
        $this->log('a ajouté une commande fournisseur');
        $this->syncStatut();
    }

    public function receiveCommande(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $c = Commande::where('intervention_id', $this->intervention->id)->findOrFail($id);
        Reception::receiveCommande($c);
        $this->syncStatut();
    }

    public function deleteCommande(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        Commande::where('intervention_id', $this->intervention->id)->findOrFail($id)->delete();
        $this->syncStatut();
    }

    // ----- Sous-traitance ----------------------------------------------------

    public function addSousTraitance(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $this->validate(['sst.nom' => ['nullable', 'string', 'max:255']]);

        $this->intervention->sousTraitances()->create($this->sst);
        $this->sst = ['nom' => '', 'devis' => ''];
        $this->log('a ajouté une sous-traitance');
        $this->syncStatut();
    }

    public function returnSousTraitance(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $s = SousTraitance::where('intervention_id', $this->intervention->id)->findOrFail($id);
        Reception::returnSousTraitance($s);
        $this->syncStatut();
    }

    public function deleteSousTraitance(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        SousTraitance::where('intervention_id', $this->intervention->id)->findOrFail($id)->delete();
        $this->syncStatut();
    }

    /**
     * Accept French-style decimals (comma separator, optional thousands spaces)
     * so a technician can type "1,5" for a duration or "12,50" for a price.
     */
    private function normaliserDecimale($valeur)
    {
        if (! is_string($valeur)) {
            return $valeur;
        }

        return str_replace([' ', ','], ['', '.'], trim($valeur));
    }

    private function syncStatut(): void
    {
        InterventionStatus::syncFromDependencies($this->intervention->refresh());
        $this->statutId = $this->intervention->statut_id;
    }

    private function log(string $texte): void
    {
        InterventionLog::create([
            'intervention_id' => $this->intervention->id,
            'user_id' => Auth::id(),
            'texte' => $texte,
            'created_at' => now(),
        ]);
    }

    public function render()
    {
        $this->intervention->load(['prestations', 'pieces', 'commandes', 'sousTraitances', 'clientMessages', 'photos', 'statut', 'client', 'materiel', 'systemeExploitation', 'antivirus']);

        return view('livewire.intervention-panel', [
            'statuts' => Statut::orderBy('ordre')->get(),
            'catalogue' => Prestation::orderBy('designation')->get(),
        ]);
    }
}
