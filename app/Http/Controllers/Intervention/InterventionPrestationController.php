<?php

namespace App\Http\Controllers\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\InterventionPrestation;
use App\Models\Prestation;
use App\Support\Permissions;
use Illuminate\Http\Request;

class InterventionPrestationController extends Controller
{
    public function store(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $data = $request->validate([
            'prestation_id' => ['nullable', 'exists:prestations,id'],
            'designation' => ['nullable', 'string', 'max:255'],
            'duree' => ['required', 'numeric', 'min:0'],
            'tarif' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (empty($data['designation']) && ! empty($data['prestation_id'])) {
            $data['designation'] = Prestation::find($data['prestation_id'])?->designation;
        }
        abort_if(empty($data['designation']), 422);

        $intervention->prestations()->create($data);

        return back()->with('success', 'Prestation ajoutée.');
    }

    public function destroy(InterventionPrestation $prestation)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $prestation->delete();

        return back()->with('success', 'Prestation supprimée.');
    }
}
