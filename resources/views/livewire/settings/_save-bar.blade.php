{{-- Shared save bar for the Livewire settings panels (inline, no reload). --}}
<div class="mt-5 flex items-center justify-end gap-3">
    <span x-show="ok" x-cloak x-transition class="text-sm text-green-600">✓ Enregistré</span>
    <span wire:loading wire:target="save" class="text-sm text-amber-600">Enregistrement…</span>
    <x-button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">Enregistrer</x-button>
</div>
