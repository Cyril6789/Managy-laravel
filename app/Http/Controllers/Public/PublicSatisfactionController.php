<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Satisfaction;
use Illuminate\Http\Request;

class PublicSatisfactionController extends Controller
{
    public function show(string $token)
    {
        $satisfaction = Satisfaction::where('token', $token)->with(['client', 'intervention'])->firstOrFail();

        return view('public.satisfaction', compact('satisfaction'));
    }

    public function store(Request $request, string $token)
    {
        $satisfaction = Satisfaction::where('token', $token)->firstOrFail();

        if ($satisfaction->submitted_at) {
            return view('public.satisfaction', ['satisfaction' => $satisfaction]);
        }

        $data = $request->validate([
            'note' => ['required', 'integer', 'between:1,5'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ]);

        $satisfaction->update($data + ['submitted_at' => now()]);

        return view('public.satisfaction', ['satisfaction' => $satisfaction->fresh()]);
    }
}
