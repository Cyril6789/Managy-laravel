<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Same-origin proxy for the French Base Adresse Nationale (BAN) address search
 * — https://api-adresse.data.gouv.fr. Going through the server (instead of
 * calling the API straight from the browser) avoids CORS, CSP, mixed-content
 * and ad-blocker issues, and keeps the lookup working behind corporate proxies.
 */
class AddressController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        try {
            $response = Http::timeout(6)->acceptJson()->get(
                'https://api-adresse.data.gouv.fr/search/',
                ['q' => $q, 'limit' => 6, 'autocomplete' => 1]
            );

            if (! $response->ok()) {
                return response()->json([]);
            }

            $features = $response->json('features', []);
        } catch (\Throwable) {
            return response()->json([]);
        }

        $results = collect($features)->map(function (array $feature) {
            $p = $feature['properties'] ?? [];
            $street = trim(($p['housenumber'] ?? '').' '.($p['street'] ?? ''));

            return [
                'label' => $p['label'] ?? '',
                'adresse' => $street !== '' ? $street : (($p['type'] ?? '') === 'municipality' ? '' : ($p['name'] ?? '')),
                'code_postal' => $p['postcode'] ?? '',
                'ville' => $p['city'] ?? '',
            ];
        })->values()->all();

        return response()->json($results);
    }
}
