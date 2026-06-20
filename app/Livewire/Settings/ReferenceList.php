<?php

namespace App\Livewire\Settings;

use App\Models\Antivirus;
use App\Models\CommentaireType;
use App\Models\Materiel;
use App\Models\Prestation;
use App\Models\RapportType;
use App\Models\Statut;
use App\Models\SystemeExploitation;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Generic, config-driven Livewire CRUD for the "reference" lists managed in
 * Paramètres (matériels, OS, antivirus, prestations, statuts, modèles…).
 * Everything is edited inline without a full page reload.
 */
class ReferenceList extends Component
{
    #[Locked]
    public string $type;

    #[Locked]
    public string $title = '';

    /** Inline edit buffers, keyed by row id then field. */
    public array $rows = [];

    /** New entry buffer. */
    public array $draft = [];

    /**
     * type => [model, order, fields[]]. Each field: key, kind, placeholder,
     * class, default.
     */
    private const TYPES = [
        'materiels' => [Materiel::class, 'nom', [
            ['key' => 'nom', 'kind' => 'text', 'placeholder' => 'Ex. Ordinateur portable', 'class' => 'flex-1 min-w-40'],
        ]],
        'systemes' => [SystemeExploitation::class, 'nom', [
            ['key' => 'nom', 'kind' => 'text', 'placeholder' => 'Ex. Windows 11', 'class' => 'flex-1 min-w-40'],
        ]],
        'antivirus' => [Antivirus::class, 'nom', [
            ['key' => 'nom', 'kind' => 'text', 'placeholder' => 'Ex. Bitdefender', 'class' => 'flex-1 min-w-40'],
        ]],
        'prestations' => [Prestation::class, 'designation', [
            ['key' => 'designation', 'kind' => 'text', 'placeholder' => 'Désignation', 'class' => 'flex-1 min-w-40'],
            ['key' => 'duree_defaut', 'kind' => 'number', 'placeholder' => 'h', 'class' => 'w-20', 'step' => '0.25'],
            ['key' => 'tarif', 'kind' => 'number', 'placeholder' => '€', 'class' => 'w-24', 'step' => '0.01'],
        ]],
        'statuts' => [Statut::class, 'ordre', [
            ['key' => 'couleur', 'kind' => 'color', 'default' => '#64748b'],
            ['key' => 'nom', 'kind' => 'text', 'placeholder' => 'Nouveau statut', 'class' => 'flex-1 min-w-40'],
            ['key' => 'verrouille', 'kind' => 'check', 'label' => 'Verrouille'],
            ['key' => 'est_cloture', 'kind' => 'check', 'label' => 'Clôture'],
        ]],
        'rapport-types' => [RapportType::class, 'titre', [
            ['key' => 'titre', 'kind' => 'text', 'placeholder' => 'Titre', 'class' => 'flex-1'],
            ['key' => 'texte', 'kind' => 'textarea', 'placeholder' => 'Contenu du modèle…'],
        ]],
        'commentaire-types' => [CommentaireType::class, 'titre', [
            ['key' => 'titre', 'kind' => 'text', 'placeholder' => 'Titre', 'class' => 'flex-1'],
            ['key' => 'texte', 'kind' => 'textarea', 'placeholder' => 'Contenu du modèle…'],
        ]],
    ];

    public function mount(string $type, string $title = ''): void
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $this->type = $type;
        $this->title = $title;
        $this->resetDraft();
        $this->loadRows();
    }

    private function config(): array
    {
        return self::TYPES[$this->type];
    }

    private function fields(): array
    {
        return $this->config()[2];
    }

    private function mainKey(): string
    {
        return $this->fields()[0]['key'];
    }

    private function loadRows(): void
    {
        $this->rows = [];
        foreach ($this->items() as $item) {
            foreach ($this->fields() as $f) {
                $this->rows[$item->id][$f['key']] = $item->{$f['key']};
            }
        }
    }

    private function resetDraft(): void
    {
        $this->draft = [];
        foreach ($this->fields() as $f) {
            $this->draft[$f['key']] = $f['kind'] === 'check' ? false : ($f['default'] ?? '');
        }
    }

    private function items()
    {
        [$model, $order] = $this->config();

        return $model::orderBy($order)->get();
    }

    private function cast(array $field, $value)
    {
        if ($field['kind'] === 'check') {
            return (bool) $value;
        }
        if ($field['kind'] === 'number') {
            return $value === '' || $value === null ? null : $value;
        }

        return $value;
    }

    public function create(): void
    {
        Gate::authorize(Permissions::SETTINGS_MANAGE);
        [$model] = $this->config();

        $main = trim((string) ($this->draft[$this->mainKey()] ?? ''));
        if ($main === '') {
            $this->addError('draft.'.$this->mainKey(), 'Champ requis.');

            return;
        }

        $data = [];
        foreach ($this->fields() as $f) {
            $data[$f['key']] = $this->cast($f, $this->draft[$f['key']] ?? null);
        }
        $model::create($data);

        $this->resetDraft();
        $this->loadRows();
    }

    public function save(int $id): void
    {
        Gate::authorize(Permissions::SETTINGS_MANAGE);
        [$model] = $this->config();

        $main = trim((string) ($this->rows[$id][$this->mainKey()] ?? ''));
        if ($main === '') {
            $this->addError('rows.'.$id.'.'.$this->mainKey(), 'Champ requis.');

            return;
        }

        $data = [];
        foreach ($this->fields() as $f) {
            $data[$f['key']] = $this->cast($f, $this->rows[$id][$f['key']] ?? null);
        }
        $model::findOrFail($id)->update($data);

        $this->dispatch('reference-saved', id: $id);
    }

    public function delete(int $id): void
    {
        Gate::authorize(Permissions::SETTINGS_MANAGE);
        [$model] = $this->config();
        $model::findOrFail($id)->delete();
        $this->loadRows();
    }

    public function render()
    {
        return view('livewire.settings.reference-list', [
            'items' => $this->items(),
            'fields' => $this->fields(),
        ]);
    }
}
