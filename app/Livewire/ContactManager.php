<?php

namespace App\Livewire;

use App\Models\Client;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Manage a company's contacts. A contact is a regular "particulier" client
 * linked to the company through the company_contact pivot: it can be an existing
 * particulier attached here, or a new one created on the fly. Detaching only
 * removes the link — the particulier (and its own interventions) is kept.
 */
class ContactManager extends Component
{
    public Client $company;

    public bool $showModal = false;

    public string $mode = 'create';

    public ?int $editingId = null;

    public array $form = [];

    // Live search to attach an existing particulier as a contact.
    public string $query = '';

    public array $results = [];

    public bool $open = false;

    public function mount(Client $company): void
    {
        $this->company = $company;
        $this->resetForm();
    }

    protected function rules(): array
    {
        return [
            'form.civilite' => ['nullable', 'string', 'max:20'],
            'form.nom' => ['required', 'string', 'max:255'],
            'form.prenom' => ['nullable', 'string', 'max:255'],
            'form.email' => ['nullable', 'email', 'max:255'],
            'form.telephone_mobile' => ['nullable', 'string', 'max:30'],
            'form.telephone_fixe' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function updatedQuery(): void
    {
        $this->open = true;
        $term = trim($this->query);
        $attached = $this->company->contacts()->pluck('clients.id')->all();

        $this->results = strlen($term) < 2 ? [] : Client::active()
            ->particuliers()
            ->whereNotIn('id', $attached)
            ->where(fn ($w) => $w->where('nom', 'like', "%{$term}%")
                ->orWhere('prenom', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('telephone_mobile', 'like', "%{$term}%"))
            ->orderBy('nom')->limit(10)->get()
            ->map(fn (Client $c) => ['id' => $c->id, 'label' => $c->nomComplet(), 'ville' => $c->ville])
            ->all();
    }

    public function attachExisting(int $id): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $contact = Client::particuliers()->findOrFail($id);
        $this->company->contacts()->syncWithoutDetaching([$contact->id]);
        $this->reset('query', 'results', 'open');
    }

    public function openCreate(): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $this->resetForm();
        $this->mode = 'create';
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $contact = $this->company->contacts()->findOrFail($id);
        $this->form = array_merge($this->emptyForm(), $contact->only(array_keys($this->emptyForm())));
        $this->mode = 'edit';
        $this->editingId = $id;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $this->validate();

        if ($this->mode === 'edit' && $this->editingId) {
            $this->company->contacts()->findOrFail($this->editingId)->update($this->form);
        } else {
            $contact = Client::create($this->form + ['type' => 'particulier']);
            $this->company->contacts()->syncWithoutDetaching([$contact->id]);
        }

        $this->showModal = false;
    }

    /** Removes the link with the company; the particulier itself is kept. */
    public function detach(int $id): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $this->company->contacts()->detach($id);
    }

    private function emptyForm(): array
    {
        return ['civilite' => '', 'nom' => '', 'prenom' => '', 'email' => '', 'telephone_mobile' => '', 'telephone_fixe' => ''];
    }

    private function resetForm(): void
    {
        $this->form = $this->emptyForm();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.contact-manager', [
            'contacts' => $this->company->contacts()->orderBy('nom')->get(),
        ]);
    }
}
