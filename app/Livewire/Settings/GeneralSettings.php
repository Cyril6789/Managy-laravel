<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Models\Society;
use App\Models\Statut;
use App\Support\Permissions;
use App\Support\TenantUrl;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Livewire-driven settings panels (company, SMS, SMTP, automation, billing).
 */
class GeneralSettings extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $section;

    public array $data = [];

    public $logo = null;

    public bool $removeLogo = false;

    public string $mailPassword = '';

    public string $slug = '';

    #[Locked]
    public string $originalSlug = '';

    public bool $slugAvailable = true;

    public ?string $slugSuggestion = null;

    private const SECTIONS = [
        'entreprise' => [
            'company_name', 'company_email', 'company_phone', 'company_website',
            'company_address', 'company_postal_code', 'company_city', 'company_siret', 'company_vat',
        ],
        'sms' => ['sms_provider', 'sms_sender', 'sms_signature', 'sms_api_key'],
        'smtp' => [
            'mail_host', 'mail_port', 'mail_username', 'mail_encryption',
            'mail_from_address', 'mail_from_name',
        ],
        'automation' => [
            'maintenance_alert_threshold', 'statut_attente_id', 'statut_pret_id', 'statut_finalise_id',
        ],
        'billing' => [
            'deplacement_mode', 'deplacement_forfait', 'deplacement_prix_km', 'deplacement_villes_gratuites',
        ],
    ];

    public function mount(string $section): void
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);
        Gate::authorize(Permissions::SETTINGS_MANAGE);

        $this->section = $section;
        $all = Setting::all();
        foreach (self::SECTIONS[$section] as $key) {
            $this->data[$key] = $all[$key] ?? null;
        }

        if ($section === 'entreprise' && auth()->user()?->society) {
            $society = auth()->user()->society;
            $this->slug = $society->slug;
            $this->originalSlug = $society->slug;
        }

        $this->data['sms_provider'] ??= 'log';
        $this->data['mail_port'] ??= '587';
        $this->data['mail_encryption'] ??= 'tls';
        $this->data['deplacement_mode'] ??= 'aucun';
    }

    protected function rules(): array
    {
        return match ($this->section) {
            'entreprise' => array_fill_keys(
                array_map(fn ($k) => "data.$k", self::SECTIONS['entreprise']),
                ['nullable', 'string', 'max:255']
            ) + [
                'data.company_email' => ['nullable', 'email', 'max:255'],
                'logo' => ['nullable', 'image', 'max:2048'],
                'slug' => auth()->user()?->is_admin ? [
                    'required',
                    'string',
                    'max:63',
                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    Rule::notIn(config('saas.reserved_subdomains', [])),
                    Rule::unique('societies', 'slug')->ignore(auth()->user()->society_id),
                ] : [],
            ],
            'sms' => [
                'data.sms_provider' => ['required', 'in:log,smsmode,smsfactor'],
                'data.sms_sender' => ['nullable', 'string', 'max:11'],
                'data.sms_signature' => ['nullable', 'string', 'max:255'],
                'data.sms_api_key' => ['nullable', 'string', 'max:255'],
            ],
            'smtp' => [
                'data.mail_host' => ['nullable', 'string', 'max:255'],
                'data.mail_port' => ['nullable', 'integer', 'between:1,65535'],
                'data.mail_username' => ['nullable', 'string', 'max:255'],
                'data.mail_encryption' => ['nullable', 'in:tls,ssl,none'],
                'data.mail_from_address' => ['nullable', 'email', 'max:255'],
                'data.mail_from_name' => ['nullable', 'string', 'max:255'],
                'mailPassword' => ['nullable', 'string', 'max:255'],
            ],
            'automation' => [
                'data.maintenance_alert_threshold' => ['nullable', 'numeric', 'min:0'],
                'data.statut_attente_id' => ['nullable', 'exists:statuts,id'],
                'data.statut_pret_id' => ['nullable', 'exists:statuts,id'],
                'data.statut_finalise_id' => ['nullable', 'exists:statuts,id'],
            ],
            'billing' => [
                'data.deplacement_mode' => ['required', 'in:aucun,forfait,km'],
                'data.deplacement_forfait' => ['nullable', 'numeric', 'min:0'],
                'data.deplacement_prix_km' => ['nullable', 'numeric', 'min:0'],
                'data.deplacement_villes_gratuites' => ['nullable', 'string'],
            ],
            default => [],
        };
    }

    public function updatedSlug(string $value): void
    {
        if ($this->section !== 'entreprise' || ! auth()->user()?->is_admin) {
            return;
        }

        $this->slug = Society::normalizeSlug($value);
        $societyId = auth()->user()->society_id;
        $this->slugAvailable = Society::slugIsAvailable($this->slug, $societyId);
        $this->slugSuggestion = $this->slugAvailable || $this->slug === ''
            ? null
            : Society::uniqueSlug($this->slug, $societyId);

        $this->resetValidation('slug');
    }

    public function useSlugSuggestion(): void
    {
        if ($this->slugSuggestion) {
            $this->slug = $this->slugSuggestion;
            $this->updatedSlug($this->slug);
        }
    }

    public function save(): mixed
    {
        Gate::authorize(Permissions::SETTINGS_MANAGE);
        $this->validate();

        foreach (self::SECTIONS[$this->section] as $key) {
            Setting::put($key, $this->data[$key] ?? null);
        }

        $redirectAfterSlugChange = false;
        $society = null;

        if ($this->section === 'entreprise') {
            $this->handleLogo();

            if (auth()->user()?->is_admin) {
                $society = auth()->user()->society;
                $newSlug = Society::normalizeSlug($this->slug);
                $redirectAfterSlugChange = $newSlug !== $society->slug;

                $society->update([
                    'name' => $this->data['company_name'] ?: $society->name,
                    'slug' => $newSlug,
                ]);
            }
        }

        if ($this->section === 'smtp' && $this->mailPassword !== '') {
            Setting::put('mail_password', $this->mailPassword);
            $this->mailPassword = '';
        }

        if ($redirectAfterSlugChange && $society) {
            session()->flash('success', 'L’adresse de votre espace a été modifiée.');

            return redirect()->away(TenantUrl::forSociety($society->fresh(), '/parametres'));
        }

        $this->originalSlug = $this->slug;
        $this->dispatch('settings-saved');

        return null;
    }

    private function handleLogo(): void
    {
        if ($this->removeLogo) {
            $this->deleteCurrentLogo();
            Setting::put('company_logo', null);
            $this->removeLogo = false;
        }

        if ($this->logo) {
            $this->deleteCurrentLogo();
            Setting::put('company_logo', $this->logo->store('logos', 'public'));
            $this->logo = null;
        }
    }

    private function deleteCurrentLogo(): void
    {
        if ($current = Setting::get('company_logo')) {
            Storage::disk('public')->delete($current);
        }
    }

    public function render()
    {
        return view('livewire.settings.general-settings', [
            'statuts' => $this->section === 'automation'
                ? Statut::orderBy('ordre')->get()
                : collect(),
            'companyLogo' => $this->section === 'entreprise' ? Setting::get('company_logo') : null,
        ]);
    }
}
