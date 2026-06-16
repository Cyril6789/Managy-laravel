<?php

namespace App\Http\Controllers;

use App\Models\Satisfaction;
use App\Support\Permissions;

class SatisfactionController extends Controller
{
    public function index()
    {
        $this->authorize(Permissions::SATISFACTION_VIEW);

        $reponses = Satisfaction::with(['client', 'intervention'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->paginate(20);

        $moyenne = Satisfaction::whereNotNull('note')->avg('note');
        $total = Satisfaction::whereNotNull('submitted_at')->count();
        $repartition = Satisfaction::whereNotNull('note')
            ->selectRaw('note, COUNT(*) as total')
            ->groupBy('note')
            ->pluck('total', 'note');

        return view('satisfaction.index', compact('reponses', 'moyenne', 'total', 'repartition'));
    }
}
