<?php

namespace App\Livewire;

use App\Models\Client;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Full management of a company's contacts (its employees) on the client page.
 */
class ContactManager extends Component
{
    public Client $company;

    public bool $showModal = false;

    public string $mode = 'create';

    public ?int $editingId = null;

    public array $form = [];

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
            $this->company->contacts()->create($this->form + ['type' => 'particulier']);
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $contact = $this->company->contacts()->findOrFail($id);

        if ($contact->interventions()->exists()) {
            $this->addError('delete', 'Ce contact a des interventions et ne peut pas être supprimé.');

            return;
        }
        $contact->delete();
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
