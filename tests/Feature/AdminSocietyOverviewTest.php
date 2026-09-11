<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\Society;
use App\Models\User;
use App\Support\Tenancy;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSocietyOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_correct_totals_logo_and_invoice_switch(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');
        $society = Society::where('slug', 'demo')->firstOrFail();
        $society->update(['logo' => 'logos/company.png']);
        Storage::disk('public')->put($society->logo, 'fake-png');

        app(Tenancy::class)->forSociety($society->id, function () {
            $client = Client::create(['type' => 'particulier', 'nom' => 'Client archivé']);
            $intervention = Intervention::create(['client_id' => $client->id]);
            $client->delete();
            $intervention->delete();
        });

        $this->actingAs(User::where('is_super_admin', true)->firstOrFail())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.society.logo', $society))
            ->assertViewHas('societies', function ($rows) use ($society) {
                $row = $rows->first(fn ($item) => $item['society']->is($society));

                return $row
                    && $row['clients'] === Client::withoutGlobalScope('society')->withTrashed()->where('society_id', $society->id)->count()
                    && $row['interventions'] === Intervention::withoutGlobalScope('society')->withTrashed()->where('society_id', $society->id)->count();
            });

        $this->get(route('admin.society.logo', $society))->assertOk();
        $this->post(route('admin.society.invoice.toggle', $society))->assertRedirect();
        $this->assertTrue($society->fresh()->invoice_enabled);
    }
}
