<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Intervention;
use App\Support\Permissions;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $clients = collect();
        $interventions = collect();

        if (mb_strlen($q) >= 2) {
            $term = '%'.$q.'%';

            if ($request->user()->can(Permissions::CLIENTS_VIEW)) {
                $clients = Client::where(fn ($w) => $w->where('nom', 'like', $term)
                    ->orWhere('prenom', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('ville', 'like', $term))
                    ->limit(15)->get();
            }

            if ($request->user()->can(Permissions::INTERVENTIONS_VIEW)) {
                $interventions = Intervention::with('client')
                    ->where(fn ($w) => $w->where('reference', 'like', $term)
                        ->orWhere('panne', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)))
                    ->latest('opened_at')->limit(15)->get();
            }
        }

        return view('search', compact('q', 'clients', 'interventions'));
    }
}
