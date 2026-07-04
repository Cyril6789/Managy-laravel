<?php

namespace App\Services\Sso;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads the signed-in user's security-group memberships from Microsoft Graph.
 *
 * Requires the access token to carry a scope allowing directory reads
 * (GroupMember.Read.All or Directory.Read.All). Returns both the object id and
 * the display name of every group so a mapping can match on either.
 */
class MicrosoftGraphGroups
{
    private const ENDPOINT = 'https://graph.microsoft.com/v1.0/me/transitiveMemberOf/microsoft.graph.group';

    /**
     * @return list<array{id: string, name: string}>
     */
    public function forToken(?string $accessToken): array
    {
        if (blank($accessToken)) {
            return [];
        }

        $groups = [];
        $url = self::ENDPOINT.'?$select=id,displayName&$top=999';

        try {
            // Graph paginates via @odata.nextLink; follow it until exhausted.
            while ($url !== null) {
                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->timeout(15)
                    ->get($url);

                if ($response->failed()) {
                    Log::warning('SSO: Microsoft Graph group lookup failed', [
                        'status' => $response->status(),
                    ]);

                    break;
                }

                foreach ($response->json('value', []) as $group) {
                    $groups[] = [
                        'id' => (string) ($group['id'] ?? ''),
                        'name' => (string) ($group['displayName'] ?? ''),
                    ];
                }

                $url = $response->json()['@odata.nextLink'] ?? null;
            }
        } catch (\Throwable $e) {
            Log::warning('SSO: Microsoft Graph group lookup errored', ['message' => $e->getMessage()]);
        }

        return $groups;
    }
}
