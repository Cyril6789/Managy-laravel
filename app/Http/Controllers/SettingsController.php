<?php

namespace App\Http\Controllers;

use App\Models\Antivirus;
use App\Models\CommentaireType;
use App\Models\Materiel;
use App\Models\MaterielAjouteType;
use App\Models\Prestation;
use App\Models\RapportType;
use App\Models\Setting;
use App\Models\Statut;
use App\Models\SystemeExploitation;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /** Reference list registry: slug => [model, label, fields]. */
    private const REFERENCES = [
        'materiels' => [Materiel::class, ['nom']],
        'systemes' => [SystemeExploitation::class, ['nom']],
        'antivirus' => [Antivirus::class, ['nom']],
        'prestations' => [Prestation::class, ['designation', 'duree_defaut', 'tarif']],
        'statuts' => [Statut::class, ['nom', 'couleur', 'verrouille', 'est_cloture']],
        'rapport-types' => [RapportType::class, ['titre', 'texte']],
        'commentaire-types' => [CommentaireType::class, ['titre', 'texte']],
        'materiel-ajoute-types' => [MaterielAjouteType::class, ['nom', 'texte']],
    ];

    public function index()
    {
        $this->authorize(Permissions::SETTINGS_MANAGE);

        return view('settings.index', [
            'settings' => Setting::all(),
            'materiels' => Materiel::orderBy('nom')->get(),
            'systemes' => SystemeExploitation::orderBy('nom')->get(),
            'antivirus' => Antivirus::orderBy('nom')->get(),
            'prestations' => Prestation::orderBy('designation')->get(),
            'statuts' => Statut::orderBy('ordre')->get(),
            'rapportTypes' => RapportType::orderBy('titre')->get(),
            'commentaireTypes' => CommentaireType::orderBy('titre')->get(),
            'materielAjouteTypes' => MaterielAjouteType::orderBy('nom')->get(),
        ]);
    }

    public function updateCompany(Request $request)
    {
        $this->authorize(Permissions::SETTINGS_MANAGE);

        $fields = ['company_name', 'company_email', 'company_phone', 'company_address',
            'company_postal_code', 'company_city', 'company_siret', 'company_vat', 'company_website'];

        $data = $request->validate(array_fill_keys($fields, ['nullable', 'string', 'max:255']) + [
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        foreach ($fields as $key) {
            Setting::put($key, $data[$key] ?? null);
        }

        if ($request->boolean('remove_logo')) {
            $this->deleteLogo();
            Setting::put('company_logo', null);
        }

        if ($request->hasFile('logo')) {
            $this->deleteLogo();
            Setting::put('company_logo', $request->file('logo')->store('logos', 'public'));
        }

        return back()->with('success', 'Coordonnées de l\'entreprise enregistrées.');
    }

    public function updateAutomation(Request $request)
    {
        $this->authorize(Permissions::SETTINGS_MANAGE);

        $data = $request->validate([
            'maintenance_alert_threshold' => ['nullable', 'numeric', 'min:0'],
            'statut_attente_id' => ['nullable', 'exists:statuts,id'],
            'statut_pret_id' => ['nullable', 'exists:statuts,id'],
        ]);
        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        return back()->with('success', 'Automatisations enregistrées.');
    }

    private function deleteLogo(): void
    {
        if ($current = Setting::get('company_logo')) {
            Storage::disk('public')->delete($current);
        }
    }

    public function updateSms(Request $request)
    {
        $this->authorize(Permissions::SETTINGS_MANAGE);

        $data = $request->validate([
            'sms_provider' => ['required', 'in:log,smsmode,smsfactor'],
            'sms_sender' => ['nullable', 'string', 'max:11'],
            'sms_signature' => ['nullable', 'string', 'max:255'],
            'sms_api_key' => ['nullable', 'string', 'max:255'],
        ]);
        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        return back()->with('success', 'Paramètres SMS enregistrés.');
    }

    public function storeReference(Request $request, string $type)
    {
        $this->authorize(Permissions::SETTINGS_MANAGE);

        [$model, $fields] = $this->reference($type);
        $model::create($this->referenceData($request, $fields));

        return back()->with('success', 'Entrée ajoutée.');
    }

    public function updateReference(Request $request, string $type, int $id)
    {
        $this->authorize(Permissions::SETTINGS_MANAGE);

        [$model, $fields] = $this->reference($type);
        $model::findOrFail($id)->update($this->referenceData($request, $fields));

        return back()->with('success', 'Entrée mise à jour.');
    }

    public function destroyReference(string $type, int $id)
    {
        $this->authorize(Permissions::SETTINGS_MANAGE);

        [$model] = $this->reference($type);
        $model::findOrFail($id)->delete();

        return back()->with('success', 'Entrée supprimée.');
    }

    private function reference(string $type): array
    {
        abort_unless(isset(self::REFERENCES[$type]), 404);

        return self::REFERENCES[$type];
    }

    private function referenceData(Request $request, array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            if (in_array($field, ['verrouille', 'est_cloture'])) {
                $data[$field] = $request->boolean($field);
            } else {
                $data[$field] = $request->input($field);
            }
        }

        // Required main label per model.
        $label = $fields[0];
        abort_if(blank($data[$label] ?? null), 422);

        return $data;
    }
}
