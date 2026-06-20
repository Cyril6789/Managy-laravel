<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AddressSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_address_search_proxies_and_maps_ban_results(): void
    {
        Http::fake([
            'api-adresse.data.gouv.fr/*' => Http::response([
                'features' => [
                    ['properties' => [
                        'label' => '8 Boulevard du Port 80000 Amiens',
                        'housenumber' => '8', 'street' => 'Boulevard du Port',
                        'postcode' => '80000', 'city' => 'Amiens', 'type' => 'housenumber',
                    ]],
                ],
            ]),
        ]);

        $admin = User::where('pseudo', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->getJson('/adresse/recherche?q=8 boulevard du port')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.adresse', '8 Boulevard du Port')
            ->assertJsonPath('0.code_postal', '80000')
            ->assertJsonPath('0.ville', 'Amiens');
    }

    public function test_short_query_returns_empty(): void
    {
        $admin = User::where('pseudo', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->getJson('/adresse/recherche?q=ab')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_requires_authentication(): void
    {
        // Guests are redirected to login (app-wide behaviour), never served.
        $this->get('/adresse/recherche?q=paris')->assertRedirect('/login');
    }
}
