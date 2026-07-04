<?php

namespace Tests\Feature;

use App\Models\PermissionGroup;
use App\Models\SsoConnection;
use App\Models\SsoGroupMapping;
use App\Models\User;
use App\Services\Sso\SsoGroupSynchronizer;
use App\Support\Permissions;
use App\Support\Tenancy;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoAndPermissionGroupsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('pseudo', 'admin')->firstOrFail();
    }

    private function societyId(): int
    {
        return $this->admin()->society_id;
    }

    /** Run a callback scoped to the demo société (stamps society_id, applies scope). */
    private function forSociety(callable $fn): mixed
    {
        return app(Tenancy::class)->forSociety($this->societyId(), $fn);
    }

    public function test_a_non_admin_inherits_permissions_from_their_group(): void
    {
        [$user, $group] = $this->forSociety(function () {
            $user = User::factory()->create(['society_id' => $this->societyId(), 'is_admin' => false]);
            $group = PermissionGroup::create(['name' => 'Tech_Niv1', 'description' => 'Niveau 1']);
            $group->syncPermissions([Permissions::CLIENTS_VIEW, Permissions::INTERVENTIONS_VIEW]);
            $group->users()->attach($user->id, ['source' => 'manual']);

            return [$user, $group];
        });

        // Direct check and gate both honour the inherited permissions.
        $this->assertTrue($user->hasPermission(Permissions::CLIENTS_VIEW));
        $this->assertTrue($user->can(Permissions::INTERVENTIONS_VIEW));
        $this->assertFalse($user->hasPermission(Permissions::SETTINGS_MANAGE));
        $this->assertEqualsCanonicalizing(
            [Permissions::CLIENTS_VIEW, Permissions::INTERVENTIONS_VIEW],
            $user->effectivePermissions(),
        );
    }

    public function test_sso_group_synchronizer_places_user_in_mapped_group(): void
    {
        [$user, $group] = $this->forSociety(function () {
            $user = User::factory()->create(['society_id' => $this->societyId(), 'is_admin' => false]);
            $group = PermissionGroup::create(['name' => 'Tech_Niv1']);
            $group->syncPermissions([Permissions::CLIENTS_VIEW]);
            SsoGroupMapping::create([
                'permission_group_id' => $group->id,
                'external_group' => 'sg_managy_tech_niv1',
            ]);

            return [$user, $group];
        });

        $sync = app(SsoGroupSynchronizer::class);

        // The directory group name matches the mapping → user joins Tech_Niv1.
        $this->forSociety(fn () => $sync->sync($user, [
            ['id' => '00000000-guid', 'name' => 'sg_managy_tech_niv1'],
        ]));

        $this->assertTrue($user->fresh()->groups()->where('permission_groups.id', $group->id)->exists());
        $this->assertTrue($user->fresh()->hasPermission(Permissions::CLIENTS_VIEW));

        // Membership was recorded as SSO-managed.
        $this->assertSame('sso', $user->fresh()->groups()->first()->pivot->source);

        // No longer in the directory group → membership is revoked on re-sync.
        $this->forSociety(fn () => $sync->sync($user, []));
        $this->assertFalse($user->fresh()->groups()->where('permission_groups.id', $group->id)->exists());
    }

    public function test_manual_membership_survives_an_sso_resync(): void
    {
        [$user, $group] = $this->forSociety(function () {
            $user = User::factory()->create(['society_id' => $this->societyId(), 'is_admin' => false]);
            $group = PermissionGroup::create(['name' => 'Manuel']);
            $group->users()->attach($user->id, ['source' => 'manual']);

            return [$user, $group];
        });

        // An SSO sync that matches nothing must not drop the manual membership.
        $this->forSociety(fn () => app(SsoGroupSynchronizer::class)->sync($user, [
            ['id' => 'x', 'name' => 'unrelated_group'],
        ]));

        $this->assertTrue($user->fresh()->groups()->where('permission_groups.id', $group->id)->exists());
    }

    public function test_admin_can_open_sso_and_group_pages(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('settings.sso.edit'))->assertOk()->assertSee('Connexion SSO');
        $this->get(route('permission-groups.index'))->assertOk();
        $this->get(route('permission-groups.create'))->assertOk();
    }

    public function test_admin_can_create_a_permission_group(): void
    {
        $target = $this->forSociety(fn () => User::factory()->create([
            'society_id' => $this->societyId(), 'is_admin' => false,
        ]));

        $this->actingAs($this->admin())
            ->post(route('permission-groups.store'), [
                'name' => 'Support',
                'description' => 'Équipe support',
                'permissions' => [Permissions::CLIENTS_VIEW, 'not.a.real.permission'],
                'members' => [$target->id],
                'sso_groups' => ['sg_support', '', 'sg_support'],
            ])
            ->assertRedirect(route('permission-groups.index'));

        $group = $this->forSociety(fn () => PermissionGroup::where('name', 'Support')->firstOrFail());

        // Only catalogued permissions are kept.
        $this->assertEqualsCanonicalizing([Permissions::CLIENTS_VIEW], $group->permissionKeys());
        // Member assigned, blank/duplicate SSO groups de-duplicated.
        $this->assertTrue($group->users()->where('users.id', $target->id)->exists());
        $this->assertSame(1, $group->ssoMappings()->count());
        $this->assertTrue($target->fresh()->hasPermission(Permissions::CLIENTS_VIEW));
    }

    public function test_sso_configuration_can_be_saved_and_secret_is_preserved(): void
    {
        $this->actingAs($this->admin())
            ->put(route('settings.sso.update', 'microsoft'), [
                'enabled' => '1',
                'client_id' => 'client-123',
                'client_secret' => 'super-secret',
                'tenant_id' => 'tenant-abc',
                'allowed_domains' => 'contoso.com',
                'auto_provision_users' => '1',
            ])
            ->assertRedirect(route('settings.sso.edit'));

        $connection = $this->forSociety(fn () => SsoConnection::where('provider', 'microsoft')->firstOrFail());
        $this->assertTrue($connection->enabled);
        $this->assertSame('super-secret', $connection->client_secret);
        $this->assertTrue($connection->acceptsEmail('user@contoso.com'));
        $this->assertFalse($connection->acceptsEmail('user@other.com'));

        // Submitting again without a secret keeps the stored one.
        $this->actingAs($this->admin())
            ->put(route('settings.sso.update', 'microsoft'), [
                'enabled' => '1',
                'client_id' => 'client-456',
                'client_secret' => '',
                'tenant_id' => 'tenant-abc',
                'allowed_domains' => 'contoso.com',
            ]);

        $connection = $this->forSociety(fn () => SsoConnection::where('provider', 'microsoft')->firstOrFail());
        $this->assertSame('client-456', $connection->client_id);
        $this->assertSame('super-secret', $connection->client_secret);
    }

    public function test_sso_redirect_resolves_the_connection_from_the_email_domain(): void
    {
        $this->forSociety(fn () => SsoConnection::create([
            'provider' => 'microsoft',
            'enabled' => true,
            'client_id' => 'client-123',
            'client_secret' => 'secret',
            'tenant_id' => 'tenant-abc',
            'allowed_domains' => 'contoso.com',
        ]));

        // Matching domain → redirect off to Microsoft's authorize endpoint.
        $response = $this->post(route('sso.redirect', 'microsoft'), ['email' => 'jdoe@contoso.com']);
        $response->assertRedirect();
        $this->assertStringContainsString('login.microsoftonline.com', $response->headers->get('Location'));
        $this->assertSame($this->societyId(), session('sso.society_id'));

        // Unknown domain → back to the login page with an error.
        $this->post(route('sso.redirect', 'microsoft'), ['email' => 'jdoe@unknown.com'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }
}
