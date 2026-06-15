<?php

namespace App\Http\Controllers\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\SousTraitance;
use App\Services\AutomatismeRunner;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SousTraitanceController extends Controller
{
    public function __construct(private AutomatismeRunner $automatismes) {}

    public function store(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $intervention->sousTraitances()->create($this->rules($request));
        $this->log($intervention, 'a ajouté une sous-traitance');

        return back()->with('success', 'Sous-traitance ajoutée.');
    }

    public function update(Request $request, SousTraitance $sousTraitance)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $wasRetour = $sousTraitance->retournee;
        $sousTraitance->update($this->rules($request));

        if (! $wasRetour && $sousTraitance->retournee) {
            $sousTraitance->forceFill(['retour_le' => $sousTraitance->retour_le ?? now()])->save();
            $this->log($sousTraitance->intervention, 'a réceptionné le retour de sous-traitance');
            $this->automatismes->fire('sous_traitance_retour', $sousTraitance->intervention);
        }

        return back()->with('success', 'Sous-traitance mise à jour.');
    }

    public function destroy(SousTraitance $sousTraitance)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $sousTraitance->delete();

        return back()->with('success', 'Sous-traitance supprimée.');
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'nom' => ['nullable', 'string', 'max:255'],
            'devis' => ['nullable', 'string', 'max:255'],
            'numero_commande' => ['nullable', 'string', 'max:255'],
            'suivi_aller' => ['nullable', 'string', 'max:255'],
            'suivi_retour' => ['nullable', 'string', 'max:255'],
            'envoye_le' => ['nullable', 'date'],
            'retour_le' => ['nullable', 'date'],
            'retournee' => ['nullable', 'boolean'],
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
