<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Society;
use App\Models\SsoConnection;
use App\Models\User;
use App\Support\Tenancy;
use App\Support\TenantUrl;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Identifier-first login: a single e-mail routes to the correct tenant, a
 * société has a dedicated subdomain login page, and SSO can be imposed.
 */
class LoginFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function demoSociety(): Society
    {
        return User::where('pseudo', 'admin')->firstOrFail()->society;
    }

    private function makeMicrosoftConnection(string $domains = ''): void
    {
        app(Tenancy::class)->forSociety($this->demoSociety()->id, fn () => SsoConnection::create([
            'provider' => 'microsoft',
            'enabled' => true,
            'client_id' => 'cid',
            'client_secret' => 'sec',
            'tenant_id' => 'tid',
            'allowed_domains' => $domains,
        ]));
    }

    private function setPasswordEnabled(bool $enabled): void
    {
        app(Tenancy::class)->forSociety(
            $this->demoSociety()->id,
            fn () => Setting::put('login_password_enabled', $enabled ? '1' : '0'),
        );
    }

    public function test_identify_redirects_to_the_tenant_login_for_an_sso_domain(): void
    {
        $this->makeMicrosoftConnection('contoso.com');
        $society = $this->demoSociety();

        $this->post(route('login.identify'), ['email' => 'jdoe@contoso.com'])
            ->assertRedirect(TenantUrl::forSociety($society, '/login'));
    }

    public function test_identify_reveals_the_password_step_for_a_non_sso_email(): void
    {
        $this->post(route('login.identify'), ['email' => 'someone@nowhere.tld'])
            ->assertRedirect(route('login'));

        $this->assertSame('password', session('login.step'));
        $this->get(route('login'))->assertOk()->assertSee('Mot de passe');
    }

    public function test_legacy_slug_page_redirects_to_the_tenant_login(): void
    {
        $this->makeMicrosoftConnection();
        $society = $this->demoSociety();

        $this->get(route('login.society', $society->slug))
            ->assertRedirect(TenantUrl::forSociety($society, '/login'));

        $this->get(TenantUrl::forSociety($society, '/login'))
            ->assertOk()
            ->assertSee('Continuer avec Microsoft')
            ->assertSee('Se connecter');
    }

    public function test_unknown_slug_is_not_found(): void
    {
        $this->get('/login/slug-inexistant')->assertNotFound();
    }

    public function test_password_login_is_refused_for_a_non_admin_when_disabled(): void
    {
        $this->setPasswordEnabled(false);

        $this->post(route('login'), ['email' => 'julie@exemple.fr', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_gerant_keeps_password_access_even_when_disabled(): void
    {
        $this->setPasswordEnabled(false);
        $society = $this->demoSociety();

        $this->post(route('login'), ['email' => 'admin@exemple.fr', 'password' => 'password'])
            ->assertRedirect(TenantUrl::forSociety($society, '/tableau-de-bord'));

        $this->assertAuthenticated();
    }

    public function test_successful_login_sets_the_prefill_cookie(): void
    {
        $this->post(route('login'), ['email' => 'admin@exemple.fr', 'password' => 'password'])
            ->assertCookie('managy_login_hint');
    }

    public function test_gerant_can_configure_slug_and_password_option(): void
    {
        $admin = User::where('pseudo', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('settings.sso.login'), ['slug' => 'ma-super-societe', 'login_password_enabled' => '1'])
            ->assertRedirect(route('settings.sso.edit'));

        $this->assertSame('ma-super-societe', $this->demoSociety()->fresh()->slug);
    }

    public function test_slug_must_stay_unique(): void
    {
        Society::create(['name' => 'Autre', 'slug' => 'ma-super-societe', 'is_active' => true]);
        $admin = User::where('pseudo', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('settings.sso.login'), ['slug' => 'ma-super-societe'])
            ->assertSessionHasErrors('slug');
    }
}
