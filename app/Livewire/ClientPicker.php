<?php

namespace App\Livewire;

use App\Models\Client;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ClientPicker extends Component
{
    public ?int $selectedId = null;

    public string $selectedLabel = '';

    public string $query = '';

    public array $results = [];

    public bool $open = false;

    // Modal: create / edit a full client record without leaving the page.
    public bool $showModal = false;

    public string $mode = 'create';

    public array $form = [];

    public function mount(?int $selected = null, string $selectedLabel = ''): void
    {
        $this->selectedId = $selected;
        $this->selectedLabel = $selectedLabel;
        $this->resetForm();
    }

    protected function rules(): array
    {
        return [
            'form.type' => ['required', Rule::in(['professionnel', 'particulier'])],
            'form.civilite' => ['nullable', 'string', 'max:20'],
            'form.nom' => ['required', 'string', 'max:255'],
            'form.prenom' => ['nullable', 'string', 'max:255'],
            'form.email' => ['nullable', 'email', 'max:255'],
            'form.telephone_fixe' => ['nullable', 'string', 'max:30'],
            'form.telephone_mobile' => ['nullable', 'string', 'max:30'],
            'form.adresse' => ['nullable', 'string', 'max:255'],
            'form.code_postal' => ['nullable', 'string', 'max:10'],
            'form.ville' => ['nullable', 'string', 'max:255'],
            'form.siret' => ['nullable', 'string', 'max:30'],
            'form.notes' => ['nullable', 'string'],
        ];
    }

    protected function validationAttributes(): array
    {
        return ['form.nom' => 'nom', 'form.email' => 'e-mail'];
    }

    public function updatedQuery(): void
    {
        $this->open = true;
        $term = trim($this->query);

        $this->results = strlen($term) < 2 ? [] : Client::active()
            ->where(fn ($w) => $w->where('nom', 'like', "%{$term}%")
                ->orWhere('prenom', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('ville', 'like', "%{$term}%")
                ->orWhere('telephone_mobile', 'like', "%{$term}%"))
            ->orderBy('nom')->limit(15)->get()
            ->map(fn (Client $c) => ['id' => $c->id, 'label' => $c->nomComplet(), 'ville' => $c->ville])
            ->all();
    }

    public function choose(int $id, string $label): void
    {
        $this->selectedId = $id;
        $this->selectedLabel = $label;
        $this->open = false;
        $this->query = '';
        $this->results = [];
        $this->dispatch('client-selected', id: $id);
    }

    /** Fill the address fields from a Base Adresse Nationale suggestion. */
    public function fillAddress(array $address): void
    {
        $this->form['adresse'] = (string) ($address['adresse'] ?? '');
        $this->form['code_postal'] = (string) ($address['code_postal'] ?? '');
        $this->form['ville'] = (string) ($address['ville'] ?? '');
    }

    public function openCreate(): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $this->resetForm();
        $this->form['nom'] = trim($this->query); // prefill with what was typed
        $this->mode = 'create';
        $this->open = false;
        $this->showModal = true;
    }

    public function openEdit(): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        abort_unless((bool) $this->selectedId, 404);

        $client = Client::findOrFail($this->selectedId);
        $this->form = array_merge($this->emptyForm(), $client->only(array_keys($this->emptyForm())));
        $this->mode = 'edit';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $this->validate();

        if ($this->mode === 'edit' && $this->selectedId) {
            $client = Client::findOrFail($this->selectedId);
            $client->update($this->form);
        } else {
            $client = Client::create($this->form);
        }

        $this->showModal = false;
        $this->choose($client->id, $client->nomComplet());
    }

    private function emptyForm(): array
    {
        return [
            'type' => 'particulier', 'civilite' => '', 'nom' => '', 'prenom' => '',
            'email' => '', 'telephone_fixe' => '', 'telephone_mobile' => '',
            'adresse' => '', 'code_postal' => '', 'ville' => '', 'siret' => '', 'notes' => '',
        ];
    }

    private function resetForm(): void
    {
        $this->form = $this->emptyForm();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.client-picker');
    }
}
