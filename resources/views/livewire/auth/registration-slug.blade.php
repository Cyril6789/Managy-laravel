<div class="space-y-4">
    <x-field label="Nom de l'entreprise" name="company_name" required>
        <x-input name="company_name" wire:model.live.debounce.400ms="companyName" autofocus required />
    </x-field>

    <x-field label="Adresse de votre espace" name="company_slug" required>
        <div class="flex overflow-hidden rounded-lg border border-gray-300 bg-white focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
            <input
                type="text"
                name="company_slug"
                wire:model.live.debounce.400ms="slug"
                required
                pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                autocomplete="off"
                class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2 text-sm text-gray-900 focus:ring-0 dark:text-gray-100"
            >
            <span class="flex items-center border-l border-gray-200 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800">.{{ config('saas.domain') }}</span>
        </div>

        @if ($slug !== '')
            @if ($available)
                <p class="mt-1 text-xs font-medium text-green-600">Cette adresse est disponible.</p>
            @else
                <p class="mt-1 text-xs text-red-600">
                    Cette adresse n'est pas disponible.
                    @if ($suggestion)
                        <button type="button" wire:click="useSuggestion" class="font-medium underline">
                            Utiliser {{ $suggestion }}.{{ config('saas.domain') }}
                        </button>
                    @endif
                </p>
            @endif
        @else
            <p class="mt-1 text-xs text-gray-500">Le slug est proposé automatiquement à partir du nom de l'entreprise, puis reste modifiable.</p>
        @endif

        @error('company_slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </x-field>
</div>
