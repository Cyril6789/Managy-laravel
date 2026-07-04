<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SsoConnection;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lets the gérant enable and configure Single Sign-On (Microsoft Entra / Google)
 * for their société.
 */
class SsoConnectionController extends Controller
{
    public function edit()
    {
        $this->authorize(Permissions::SSO_MANAGE);

        // One row per provider, created lazily so the form always has a model.
        $connections = collect(SsoConnection::PROVIDERS)->mapWithKeys(function ($label, $provider) {
            $connection = SsoConnection::firstOrNew(['provider' => $provider]);

            return [$provider => $connection];
        });

        return view('settings.sso', [
            'connections' => $connections,
            'providers' => SsoConnection::PROVIDERS,
            'callbackBase' => url('/auth/sso'),
            'unmappedPolicy' => Setting::get('sso_unmapped_policy', 'read_only'),
        ]);
    }

    /**
     * How to treat an SSO user who signs in without landing in any synchronised
     * group (no effective permission): let them in read-only, or refuse.
     */
    public function updatePolicy(Request $request): RedirectResponse
    {
        $this->authorize(Permissions::SSO_MANAGE);

        $data = $request->validate([
            'sso_unmapped_policy' => ['required', 'in:read_only,deny'],
        ]);

        Setting::put('sso_unmapped_policy', $data['sso_unmapped_policy']);

        return redirect()->route('settings.sso.edit')
            ->with('success', __('Politique d\'accès SSO enregistrée.'));
    }

    public function update(Request $request, string $provider): RedirectResponse
    {
        $this->authorize(Permissions::SSO_MANAGE);

        abort_unless(array_key_exists($provider, SsoConnection::PROVIDERS), 404);

        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:1000'],
            'tenant_id' => ['nullable', 'string', 'max:255'],
            'allowed_domains' => ['nullable', 'string', 'max:1000'],
            'auto_provision_users' => ['nullable', 'boolean'],
        ]);

        $connection = SsoConnection::firstOrNew(['provider' => $provider]);

        $connection->fill([
            'enabled' => $request->boolean('enabled'),
            'client_id' => $data['client_id'] ?? null,
            'tenant_id' => $data['tenant_id'] ?? null,
            'allowed_domains' => $data['allowed_domains'] ?? null,
            'auto_provision_users' => $request->boolean('auto_provision_users'),
        ]);

        // Keep the stored secret when the field is left blank (it is masked in
        // the form), overwrite it only when a new value is submitted.
        if (filled($data['client_secret'] ?? null)) {
            $connection->client_secret = $data['client_secret'];
        }

        $connection->save();

        return redirect()->route('settings.sso.edit')
            ->with('success', __(':provider : configuration enregistrée.', [
                'provider' => $connection->providerLabel(),
            ]));
    }
}
