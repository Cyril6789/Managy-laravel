<?php

namespace App\Livewire\Auth;

use App\Models\Society;
use Livewire\Component;

class RegistrationSlug extends Component
{
    public string $companyName = '';

    public string $slug = '';

    public bool $slugEdited = false;

    public bool $available = false;

    public ?string $suggestion = null;

    public function mount(): void
    {
        $this->companyName = (string) old('company_name', '');
        $this->slug = (string) old('company_slug', '');

        if ($this->slug !== '') {
            $this->slugEdited = true;
            $this->checkAvailability();
        } elseif ($this->companyName !== '') {
            $this->generateFromName();
        }
    }

    public function updatedCompanyName(): void
    {
        if (! $this->slugEdited) {
            $this->generateFromName();
        }
    }

    public function updatedSlug(string $value): void
    {
        $this->slugEdited = true;
        $this->slug = Society::normalizeSlug($value);
        $this->checkAvailability();
    }

    public function useSuggestion(): void
    {
        if ($this->suggestion) {
            $this->slug = $this->suggestion;
            $this->slugEdited = true;
            $this->checkAvailability();
        }
    }

    private function generateFromName(): void
    {
        $base = Society::normalizeSlug($this->companyName);
        $this->slug = $base === '' ? '' : Society::uniqueSlug($base);
        $this->checkAvailability();
    }

    private function checkAvailability(): void
    {
        $this->available = $this->slug !== '' && Society::slugIsAvailable($this->slug);
        $this->suggestion = $this->available || $this->slug === ''
            ? null
            : Society::uniqueSlug($this->slug);
    }

    public function render()
    {
        return view('livewire.auth.registration-slug');
    }
}
