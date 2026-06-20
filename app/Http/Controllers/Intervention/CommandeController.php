<?php

namespace App\Http\Controllers\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Services\AutomatismeRunner;
use App\Services\Notifier;
use App\Support\InterventionStatus;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{
    public function __construct(private AutomatismeRunner $automatismes) {}

    public function store(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $intervention->commandes()->create($this->rules($request));
        $this->log($intervention, 'a ajouté une commande fournisseur');
        InterventionStatus::syncFromDependencies($intervention->refresh());

        return back()->with('success', 'Commande ajoutée.');
    }

    public function update(Request $request, Commande $commande)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $wasRecue = $commande->recue;
        $commande->update($this->rules($request));

        if (! $wasRecue && $commande->recue) {
            $commande->forceFill(['recue_le' => $commande->recue_le ?? now()])->save();
            $this->log($commande->intervention, 'a réceptionné la commande '.($commande->numero_commande ?? ''));
            $this->automatismes->fire('commande_recue', $commande->intervention);
            Notifier::interventionChanged($commande->intervention, 'Commande reçue'.($commande->numero_commande ? ' ('.$commande->numero_commande.')' : ''));
        }

        InterventionStatus::syncFromDependencies($commande->intervention->refresh());

        return back()->with('success', 'Commande mise à jour.');
    }

    public function destroy(Commande $commande)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $commande->delete();

        return back()->with('success', 'Commande supprimée.');
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'fournisseur' => ['nullable', 'string', 'max:255'],
            'bon_commande' => ['nullable', 'string', 'max:255'],
            'numero_commande' => ['nullable', 'string', 'max:255'],
            'suivi_colis' => ['nullable', 'string', 'max:255'],
            'commande_le' => ['nullable', 'date'],
            'recue_le' => ['nullable', 'date'],
            'recue' => ['nullable', 'boolean'],
        ]);
    }

    private function log(Intervention $intervention, string $texte): void
    {
        InterventionLog::create([
            'intervention_id' => $intervention->id, 'user_id' => Auth::id(),
            'texte' => $texte, 'created_at' => now(),
        ]);
    }
}
