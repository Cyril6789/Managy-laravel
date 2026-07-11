<?php

namespace Tests\Feature;

use App\Models\Society;
use App\Models\User;
use App\Support\TenantUrl;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Covers the anti-brute-force guard on login and the anti-bot guard on the
 * public sign-up form.
 */
class AuthHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        RateLimiter::clear('admin@exemple.fr|127.0.0.1');
    }

    public function test_login_locks_out_after_too_many_failed_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'admin@exemple.fr',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
            $this->assertGuest();
        }

        $response = $this->post(route('login'), [
            'email' => 'admin@exemple.fr',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString(
            'Trop de tentatives',
            session('errors')->first('email'),
        );
    }

    public function test_a_few_failures_do_not_block_a_legitimate_login(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('login'), [
                'email' => 'admin@exemple.fr',
                'password' => 'wrong-password',
            ]);
        }

        $society = Society::whereHas('users', fn ($query) => $query->where('email', 'admin@exemple.fr'))->firstOrFail();

        $this->post(route('login'), [
            'email' => 'admin@exemple.fr',
            'password' => 'password',
        ])->assertRedirect(TenantUrl::forSociety($society, '/tableau-de-bord'));

        $this->assertAuthenticated();
        $this->assertSame(0, RateLimiter::attempts('admin@exemple.fr|127.0.0.1'));
    }

    public function test_registration_rejects_a_filled_honeypot(): void
    {
        $before = Society::count();

        $this->post(route('register'), $this->validSignup([
            'homepage' => 'http://spam.example',
        ]))->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame($before, Society::count());
        $this->assertDatabaseMissing('users', ['email' => 'newco@example.test']);
    }

    public function test_registration_rejects_a_submission_that_is_too_fast(): void
    {
        $before = Society::count();

        $this->withSession(['register_started_at' => now()->timestamp])
            ->post(route('register'), $this->validSignup())
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame($before, Society::count());
    }

    public function test_a_genuine_signup_passes_the_anti_bot_guard(): void
    {
        $response = $this->withSession(['register_started_at' => now()->subMinute()->timestamp])
            ->post(route('register'), $this->validSignup());

        $society = Society::where('slug', 'new-co')->firstOrFail();

        $response->assertRedirect(TenantUrl::forSociety($society, '/tableau-de-bord'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'newco@example.test']);
    }

    /** @param array<string, mixed> $overrides */
    private function validSignup(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'New Co',
            'company_slug' => 'new-co',
            'nom' => 'Doe',
            'email' => 'newco@example.test',
            'password' => 'Sup3r-Secret!',
            'password_confirmation' => 'Sup3r-Secret!',
        ], $overrides);
    }
}
