<?php

namespace App\Livewire;

use App\Models\Client;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Attach / detach the companies (professionnels) a particulier is a contact of.
 * Shown only when editing an existing particulier. The search is a live Ajax
 * list restricted to companies — never individuals.
 */
class ClientCompanies extends Component
{
    public Client $contact;

    public string $query = '';

    public array $results = [];

    public bool $open = false;

    public function mount(Client $contact): void
    {
        $this->contact = $contact;
    }

    public function updatedQuery(): void
    {
        Gate::authorize(Permissions::CLIENTS_VIEW);
        $this->open = true;
        $term = trim($this->query);
        $attached = $this->contact->companies()->pluck('clients.id')->all();

        $this->results = strlen($term) < 2 ? [] : Client::active()
            ->professionnels()
            ->whereKeyNot($this->contact->id)
            ->whereNotIn('id', $attached)
            ->where(fn ($w) => $w->where('nom', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('ville', 'like', "%{$term}%")
                ->orWhere('siret', 'like', "%{$term}%"))
            ->orderBy('nom')->limit(15)->get()
            ->map(fn (Client $c) => ['id' => $c->id, 'label' => $c->nomComplet(), 'ville' => $c->ville])
            ->all();
    }

    public function attach(int $companyId): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $company = Client::professionnels()->findOrFail($companyId);
        $this->contact->companies()->syncWithoutDetaching([$company->id]);
        $this->reset('query', 'results', 'open');
    }

    public function detach(int $companyId): void
    {
        Gate::authorize(Permissions::CLIENTS_MANAGE);
        $this->contact->companies()->detach($companyId);
    }

    public function render()
    {
        return view('livewire.client-companies', [
            'companies' => $this->contact->companies()->orderBy('nom')->get(),
        ]);
    }
}
