<?php

namespace App\Livewire;

use App\Models\CommentaireType;
use App\Models\Intervention;
use App\Models\RapportType;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class InterventionReport extends Component
{
    public Intervention $intervention;

    public ?string $diagnostic = null;

    public ?string $message_client = null;

    public ?string $message_interne = null;

    public ?string $mdp = null;

    public ?string $tarif_estimatif = null;

    public bool $saved = false;

    public function mount(Intervention $intervention): void
    {
        $this->intervention = $intervention;
        foreach (['diagnostic', 'message_client', 'message_interne', 'mdp', 'tarif_estimatif'] as $f) {
            $this->{$f} = $intervention->{$f};
        }
    }

    protected function rules(): array
    {
        return [
            'diagnostic' => ['nullable', 'string'],
            'message_client' => ['nullable', 'string'],
            'message_interne' => ['nullable', 'string'],
            'mdp' => ['nullable', 'string', 'max:255'],
            'tarif_estimatif' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /** Auto-save when a field loses focus (wire:model.blur). */
    public function updated(): void
    {
        $this->save();
    }

    public function save(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);
        $data = $this->validate();

        $this->intervention->update($data);
        $this->saved = true;
        $this->dispatch('report-saved');
    }

    public function applyModele(string $field, string $value, string $mode = 'replace'): void
    {
        if (! in_array($field, ['diagnostic', 'message_client'], true) || $value === '') {
            return;
        }

        $this->{$field} = ($mode === 'append' && trim((string) $this->{$field}) !== '')
            ? $this->{$field}."\n".$value
            : $value;

        $this->save();
    }

    public function render()
    {
        return view('livewire.intervention-report', [
            'rapportTypes' => RapportType::orderBy('titre')->get(),
            'commentaireTypes' => CommentaireType::orderBy('titre')->get(),
        ]);
    }
}
