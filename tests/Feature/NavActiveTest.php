<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavActiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** Extract the <a> tag wrapping a given nav label. */
    private function linkFor(string $html, string $label): string
    {
        $pos = strpos($html, '>'.$label.'<');
        $start = strrpos(substr($html, 0, $pos), '<a ');
        return substr($html, $start, $pos - $start);
    }

    public function test_reception_links_highlight_independently(): void
    {
        $admin = User::where('pseudo', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $html = $this->get('/commandes-en-cours')->assertOk()->getContent();
        $this->assertStringContainsString('bg-brand-50', $this->linkFor($html, 'Commandes en cours'));
        $this->assertStringNotContainsString('bg-brand-50', $this->linkFor($html, 'Sous-traitance en cours'));

        $html = $this->get('/sous-traitances-en-cours')->assertOk()->getContent();
        $this->assertStringContainsString('bg-brand-50', $this->linkFor($html, 'Sous-traitance en cours'));
        $this->assertStringNotContainsString('bg-brand-50', $this->linkFor($html, 'Commandes en cours'));
    }
}
