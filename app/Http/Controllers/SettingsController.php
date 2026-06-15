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

        $data = $request->validate(array_fill_keys($fields, ['nullable', 'string', 'max:255']));
        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        return back()->with('success', 'Coordonnées de l\'entreprise enregistrées.');
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
