<?php

namespace App\Livewire;

use App\Models\Client;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Lets the user pick / add / edit a contact (employee) of the selected company
 * during intervention creation. The contact is who receives the SMS / e-mail.
 * Hidden when the selected client is an individual (not a company).
 */
class ContactPicker extends Component
{
    public ?int $clientId = null;

    public bool $isCompany = false;

    public ?int $contactId = null;

    public array $contacts = [];

    public bool $showModal = false;

    public string $mode = 'create';

    public ?int $editingId = null;

    public array $form = [];

    public function mount(?int $clientId = null, ?int $contactId = null): void
    {
        $this->contactId = $contactId;
        $this->resetForm();
        if ($clientId) {
            $this->loadClient($clientId);
        }
    }

    #[On('client-selected')]
    public function onClientSelected(int $id): void
    {
        $this->contactId = null;
        $this->loadClient($id);
    }

    private function loadClient(int $id): void
    {
        $client = Client::find($id);
        $this->clientId = $id;
        $this->isCompany = $client && $client->type === 'professionnel' && $client->parent_id === null;
        $this->contacts = $this->isCompany
            ? $client->contacts()->orderBy('nom')->get()
                ->map(fn (Client $c) => ['id' => $c->id, 'label' => $c->nomComplet()])->all()
            : [];
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

    public function openCreate(): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $this->resetForm();
        $this->mode = 'create';
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        abort_unless((bool) $this->contactId, 404);
        $contact = Client::findOrFail($this->contactId);
        $this->form = array_merge($this->emptyForm(), $contact->only(array_keys($this->emptyForm())));
        $this->mode = 'edit';
        $this->editingId = $contact->id;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $this->validate();

        if ($this->mode === 'edit' && $this->editingId) {
            $contact = Client::findOrFail($this->editingId);
            $contact->update($this->form);
        } else {
            $contact = Client::create($this->form + [
                'type' => 'particulier',
                'parent_id' => $this->clientId,
            ]);
        }

        $this->showModal = false;
        $this->loadClient($this->clientId);
        $this->contactId = $contact->id;
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
        return view('livewire.contact-picker');
    }
}
