<?php

namespace App\Http\Controllers;

use App\Models\Antivirus;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\Materiel;
use App\Models\Setting;
use App\Models\Statut;
use App\Models\SystemeExploitation;
use App\Models\User;
use App\Services\AutomatismeRunner;
use App\Services\Notifier;
use App\Support\Permissions;
use App\Support\Qr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InterventionController extends Controller
{
    public function __construct(private AutomatismeRunner $automatismes) {}

    public function index(Request $request)
    {
        $this->authorize(Permissions::INTERVENTIONS_VIEW);

        $voirTout = $request->user()->can(Permissions::INTERVENTIONS_VIEW_ALL);

        $interventions = Intervention::query()
            ->with(['client', 'statut', 'techniciens'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('reference', 'like', $term)
                    ->orWhere('panne', 'like', $term)
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)));
            })
            ->when($request->input('statut'), fn ($q, $s) => $q->where('statut_id', $s))
            ->when($request->input('technicien'), fn ($q, $t) => $q->whereHas('techniciens', fn ($w) => $w->where('users.id', $t)))
            ->when($request->input('etat') === 'cloturees', fn ($q) => $q->cloturees())
            ->when($request->input('etat', 'ouvertes') === 'ouvertes', fn ($q) => $q->ouvertes())
            // Without the "view all" right, only show interventions the user is assigned to.
            ->when(! $voirTout, fn ($q) => $q->whereHas('techniciens', fn ($w) => $w->where('users.id', $request->user()->id)))
            ->latest('opened_at')
            ->paginate(20)
            ->withQueryString();

        return view('interventions.index', [
            'interventions' => $interventions,
            'statuts' => Statut::orderBy('ordre')->get(),
            'techniciens' => User::where('is_active', true)->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize(Permissions::INTERVENTIONS_CREATE);

        return view('interventions.create', $this->formData(new Intervention([
            'type_lieu' => 'atelier',
            'statut_id' => Statut::where('est_defaut', true)->value('id'),
        ])));
    }

    public function store(Request $request)
    {
        $this->authorize(Permissions::INTERVENTIONS_CREATE);

        $data = $this->validateData($request);
        $data['opened_by'] = Auth::id();

        $intervention = Intervention::create($data);
        $intervention->techniciens()->attach(Auth::id(), ['assigned_at' => now()]);

        $this->log($intervention, "a créé l'intervention");
        $this->automatismes->fire('intervention_creee', $intervention);

        return redirect()->route('interventions.show', $intervention)->with('success', 'Intervention créée.');
    }

    /**
     * Context for the create form once a client is selected: maintenance-pack
     * balance + distinct history values to prefill the text areas (JSON).
     */
    public function clientContext(Client $client)
    {
        $this->authorize(Permissions::INTERVENTIONS_VIEW);

        $hasPack = $client->maintenanceMovements()->exists();
        $balance = $client->soldeMaintenance();
        $threshold = (float) Setting::get('maintenance_alert_threshold', 2);

        $distinct = fn (string $col) => Intervention::where('client_id', $client->id)
            ->whereNotNull($col)->where($col, '!=', '')
            ->orderByDesc('opened_at')->limit(20)->pluck($col)->unique()->take(10)->values();

        return response()->json([
            'maintenance' => [
                'has' => $hasPack,
                'balance' => $balance,
                'threshold' => $threshold,
                'low' => $hasPack && $balance < $threshold,
            ],
            'materiels' => $distinct('materiel_depose'),
            'pannes' => $distinct('panne'),
            'notes' => $distinct('message_interne'),
        ]);
    }

    public function show(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_VIEW);

        // The interactive panel and report cards are Livewire components that
        // load their own data; here we only need the header / sidebar data.
        $intervention->load(['client', 'materiel', 'statut', 'ouvreur', 'techniciens', 'logs.user']);

        $client = $intervention->client;
        $hasPack = $client && $client->maintenanceMovements()->exists();

        return view('interventions.show', [
            'intervention' => $intervention,
            'techniciens' => User::where('is_active', true)->orderBy('nom')->get(),
            'maintenance' => [
                'has' => $hasPack,
                'balance' => $hasPack ? $client->soldeMaintenance() : 0,
                'threshold' => (float) Setting::get('maintenance_alert_threshold', 2),
            ],
        ]);
    }

    public function edit(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        return view('interventions.edit', $this->formData($intervention));
    }

    public function update(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $intervention->update($this->validateData($request));
        $this->log($intervention, 'a modifié les détails');
        Notifier::interventionChanged($intervention, 'Détails modifiés');

        return redirect()->route('interventions.show', $intervention)->with('success', 'Intervention mise à jour.');
    }

    public function destroy(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $intervention->delete();

        return redirect()->route('interventions.index')->with('success', 'Intervention supprimée.');
    }

    // ----- Lifecycle actions -------------------------------------------------

    public function updateStatut(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $request->validate(['statut_id' => ['required', 'exists:statuts,id']]);
        $statut = Statut::find($request->statut_id);

        $intervention->update(['statut_id' => $statut->id]);
        $this->log($intervention, 'a changé le statut en « '.$statut->nom.' »');
        Notifier::interventionChanged($intervention, 'Statut : '.$statut->nom);
        $this->automatismes->fire('changement_statut', $intervention);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function updateRdv(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $data = $request->validate([
            'rdv_debut' => ['nullable', 'date'],
            'rdv_fin' => ['nullable', 'date', 'after_or_equal:rdv_debut'],
            'type_lieu' => ['required', Rule::in(['atelier', 'domicile'])],
        ]);

        $intervention->update($data + ['rdv_annule' => false]);
        $this->log($intervention, 'a planifié un rendez-vous');
        Notifier::interventionChanged($intervention, 'Rendez-vous mis à jour');
        $this->automatismes->fire('changement_rdv', $intervention);

        return back()->with('success', 'Rendez-vous enregistré.');
    }

    public function togglePriseEnCharge(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_VIEW);

        $userId = Auth::id();
        if ($intervention->techniciens()->where('users.id', $userId)->exists()) {
            $intervention->techniciens()->detach($userId);
            $this->log($intervention, 'ne prend plus en charge l\'intervention');
        } else {
            $intervention->techniciens()->attach($userId, ['assigned_at' => now()]);
            $this->log($intervention, 'a pris en charge l\'intervention');
        }

        return back();
    }

    public function restituer(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $data = $request->validate([
            'signataire_nom' => ['nullable', 'string', 'max:255'],
            'signature' => ['nullable', 'string'], // PNG data URL
        ]);

        // The report itself is saved separately (saveRapport); here we only close.
        $statutCloture = Statut::where('est_cloture', true)->orderBy('ordre')->first();

        $signaturePath = $this->storeSignature($data['signature'] ?? null, $intervention);

        $intervention->update([
            'closed_at' => now(),
            'restituted_at' => now(),
            'restituted_by' => Auth::id(),
            'statut_id' => $statutCloture?->id ?? $intervention->statut_id,
            'signataire_nom' => $data['signataire_nom'] ?? null,
            'signature_path' => $signaturePath ?? $intervention->signature_path,
            'signed_at' => $signaturePath ? now() : $intervention->signed_at,
        ]);

        $this->log($intervention, 'a restitué et clôturé l\'intervention'.($signaturePath ? ' (signée)' : ''));

        // Auto-debit the maintenance pack (if the client has one) with the hours spent.
        $this->debitMaintenancePack($intervention);

        // E-mail a signed copy of the final report to the customer / contact.
        $this->emailSignedReport($intervention);

        Notifier::interventionChanged($intervention, 'Intervention clôturée');
        $this->automatismes->fire('restitution', $intervention);

        return back()->with('success', 'Intervention clôturée.');
    }

    private function storeSignature(?string $dataUrl, Intervention $intervention): ?string
    {
        if (! $dataUrl || ! str_starts_with($dataUrl, 'data:image')) {
            return null;
        }

        $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $binary = base64_decode($base64, true);
        if ($binary === false) {
            return null;
        }

        $path = 'signatures/intervention-'.$intervention->id.'-'.now()->timestamp.'.png';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function emailSignedReport(Intervention $intervention): void
    {
        $recipient = $intervention->recipientClient();
        $to = $recipient?->email;
        if (! $to) {
            return;
        }

        $intervention->loadMissing(['client', 'materiel', 'prestations', 'statut']);
        $html = view('interventions.print.rapport', [
            'intervention' => $intervention,
            'qr' => Qr::svg(route('public.intervention', $intervention->public_token), 120),
            'soldeMaintenance' => $intervention->client?->maintenanceMovements()->exists()
                ? $intervention->client->soldeMaintenance() : null,
            'forEmail' => true,
        ])->render();

        try {
            Mail::html($html, function ($m) use ($to, $intervention) {
                $m->to($to)->subject('Votre rapport d\'intervention '.$intervention->reference)
                    ->from(Setting::get('mail_from_address') ?: Setting::get('company_email', config('mail.from.address')), Setting::get('company_name'));
            });
        } catch (\Throwable $e) {
            Log::error('Signed report email failed: '.$e->getMessage());
        }
    }

    public function decloturer(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_DECLOTURE);

        $intervention->update(['closed_at' => null, 'restituted_at' => null, 'restituted_by' => null]);
        $this->log($intervention, 'a déclôturé l\'intervention');

        return back()->with('success', 'Intervention déclôturée.');
    }

    public function toggleFacturation(Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_FACTURATION);

        $intervention->update(['facturee' => ! $intervention->facturee]);
        $this->log($intervention, $intervention->facturee ? 'a marqué comme facturée' : 'a retiré la facturation');

        return back();
    }

    /**
     * Save the technical report progressively WITHOUT closing the intervention.
     */
    public function saveRapport(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_MANAGE);

        $intervention->update($request->validate([
            'diagnostic' => ['nullable', 'string'],
            'message_client' => ['nullable', 'string'],
            'materiel_ajoute' => ['nullable', 'string'],
            'message_interne' => ['nullable', 'string'],
            'mdp' => ['nullable', 'string', 'max:255'],
            'tarif_estimatif' => ['nullable', 'numeric', 'min:0'],
        ]));

        $this->log($intervention, 'a enregistré le rapport');

        return back()->with('success', 'Rapport enregistré.');
    }

    /**
     * Assign / unassign a technician to the intervention (admins & granted users).
     */
    public function assign(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_ASSIGN);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'action' => ['required', 'in:add,remove'],
        ]);

        $tech = User::find($data['user_id']);

        if ($data['action'] === 'add') {
            $intervention->techniciens()->syncWithoutDetaching([$tech->id => ['assigned_at' => now()]]);
            $this->log($intervention, 'a affecté '.$tech->fullName());
            Notifier::toUser($tech->id, 'Intervention '.($intervention->reference ?? '#'.$intervention->id),
                'Vous avez été affecté à cette intervention.', route('interventions.show', $intervention));
        } else {
            $intervention->techniciens()->detach($tech->id);
            $this->log($intervention, 'a retiré '.$tech->fullName());
        }

        return back();
    }

    /**
     * Printable A4 sheets: "depot" (deposit slip + QR) or "rapport" (final report).
     */
    public function print(Intervention $intervention, string $type)
    {
        $this->authorize(Permissions::INTERVENTIONS_VIEW);

        $intervention->load(['client', 'materiel', 'prestations', 'statut']);

        return view("interventions.print.{$type}", [
            'intervention' => $intervention,
            'qr' => Qr::svg(route('public.intervention', $intervention->public_token), 150),
            'soldeMaintenance' => $intervention->client?->maintenanceMovements()->exists()
                ? $intervention->client->soldeMaintenance() : null,
        ]);
    }

    // ----- Helpers -----------------------------------------------------------

    private function formData(Intervention $intervention): array
    {
        return [
            'intervention' => $intervention,
            'clients' => Client::active()->orderBy('nom')->get(),
            'materiels' => Materiel::orderBy('nom')->get(),
            'systemes' => SystemeExploitation::orderBy('nom')->get(),
            'antivirus' => Antivirus::orderBy('nom')->get(),
            'statuts' => Statut::orderBy('ordre')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'contact_id' => ['nullable', 'exists:clients,id'],
            'materiel_id' => ['nullable', 'exists:materiels,id'],
            'systeme_exploitation_id' => ['nullable', 'exists:systeme_exploitations,id'],
            'antivirus_id' => ['nullable', 'exists:antivirus,id'],
            'statut_id' => ['nullable', 'exists:statuts,id'],
            'type_lieu' => ['required', Rule::in(['atelier', 'domicile'])],
            'rdv_debut' => ['nullable', 'date'],
            'rdv_fin' => ['nullable', 'date', 'after_or_equal:rdv_debut'],
            'priorite' => ['nullable', 'integer', 'between:0,3'],
            'urgente' => ['nullable', 'boolean'],
            'garantie' => ['nullable', 'boolean'],
            'materiel_depose' => ['nullable', 'string'],
            'panne' => ['nullable', 'string'],
            'diagnostic' => ['nullable', 'string'],
            'message_interne' => ['nullable', 'string'],
            'mdp' => ['nullable', 'string', 'max:255'],
            'tarif_estimatif' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    /**
     * If the client owns a maintenance pack, consume the intervention's logged
     * hours from it (once). Skipped when the client has no pack.
     */
    private function debitMaintenancePack(Intervention $intervention): void
    {
        $client = $intervention->client;
        $heures = $intervention->prestations()->sum('duree');

        if (! $client || $heures <= 0 || ! $client->maintenanceMovements()->exists()) {
            return;
        }

        // Avoid double-debit if this intervention was already debited.
        if ($client->maintenanceMovements()->where('intervention_id', $intervention->id)->where('mouvement', '<', 0)->exists()) {
            return;
        }

        $client->maintenanceMovements()->create([
            'mouvement' => -$heures,
            'description' => 'Intervention '.($intervention->reference ?? '#'.$intervention->id),
            'intervention_id' => $intervention->id,
            'user_id' => Auth::id(),
        ]);
    }

    private function log(Intervention $intervention, string $texte): void
    {
        InterventionLog::create([
            'intervention_id' => $intervention->id,
            'user_id' => Auth::id(),
            'texte' => $texte,
            'created_at' => now(),
        ]);
    }
}
