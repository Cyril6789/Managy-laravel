<div>
    {{-- Upload zone (staff, manage permission only) --}}
    @if (! $public)
        @can(\App\Support\Permissions::INTERVENTIONS_MANAGE)
            <div class="mb-4"
                 x-data="photoUploader()"
                 x-on:livewire-upload-start="uploading = true; error = null"
                 x-on:livewire-upload-finish="uploading = false; progress = 0; clearTimeout(watchdog)"
                 x-on:livewire-upload-error="uploading = false; progress = 0; clearTimeout(watchdog); error = 'L’envoi a échoué. Vérifiez votre connexion et réessayez.'"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">
                <label class="flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center transition hover:border-brand-400 hover:bg-brand-50 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:border-brand-500 dark:hover:bg-brand-900/10"
                       :class="uploading && 'pointer-events-none opacity-60'">
                    <x-icon name="camera" class="h-7 w-7 text-gray-400" />
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Ajouter des photos</span>
                    <span class="text-xs text-gray-400">Galerie ou appareil photo &middot; compressées automatiquement</span>
                    {{-- The user picks photos here. They are resized client-side, then handed
                         to the hidden Livewire input below. accept="image/*" without `capture`
                         lets iOS / Android offer both the camera and the photo library. --}}
                    <input type="file" accept="image/*" multiple class="hidden" x-ref="picker" @change="pick($event)" :disabled="uploading">
                </label>

                {{-- Receives the compressed files and performs the actual Livewire upload. --}}
                <input type="file" wire:model="uploads" multiple x-ref="target" class="hidden" tabindex="-1" aria-hidden="true">

                <label class="mt-3 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="prive" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Photo privée <span class="text-gray-400">(non visible par le client)</span>
                </label>

                <div x-show="uploading" x-cloak class="mt-2 flex items-center gap-2 text-sm text-brand-600">
                    <x-icon name="clock" class="h-4 w-4 animate-spin" />
                    <span x-text="preparing ? 'Préparation des photos…' : ('Envoi en cours…' + (progress > 0 ? ' ' + progress + ' %' : ''))"></span>
                </div>
                <p x-show="error" x-cloak x-text="error" class="mt-1 text-sm text-red-600"></p>
                @error('uploads.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('uploads') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        @endcan
    @endif

    {{-- Gallery --}}
    @if ($photos->isEmpty())
        <p class="text-sm text-gray-400">
            {{ $public ? 'Aucune photo pour le moment.' : "Aucune photo pour cette intervention." }}
        </p>
    @else
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
            @foreach ($photos as $photo)
                @php
                    $url = $public
                        ? route('public.intervention.photo', [$token, $photo])
                        : route('interventions.photos.show', [$photo->intervention_id, $photo]);
                @endphp
                <div class="group relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
                    <a href="{{ $url }}" target="_blank" rel="noopener">
                        <img src="{{ $url }}" alt="{{ $photo->original_name }}" loading="lazy"
                             class="h-full w-full object-cover transition group-hover:opacity-90">
                    </a>

                    @if (! $public)
                        @if ($photo->prive)
                            <span class="absolute left-1 top-1 inline-flex items-center gap-1 rounded bg-gray-900/70 px-1.5 py-0.5 text-[10px] font-medium text-white">
                                <x-icon name="lock" class="h-3 w-3" /> Privée
                            </span>
                        @endif

                        @can(\App\Support\Permissions::INTERVENTIONS_MANAGE)
                            <div class="absolute inset-x-1 bottom-1 flex justify-between gap-1 opacity-0 transition focus-within:opacity-100 group-hover:opacity-100">
                                <button type="button" wire:click="togglePrivacy({{ $photo->id }})"
                                        class="rounded bg-white/90 px-1.5 py-0.5 text-[10px] font-medium text-gray-700 shadow hover:bg-white dark:bg-gray-900/80 dark:text-gray-200"
                                        title="{{ $photo->prive ? 'Rendre visible au client' : 'Masquer au client' }}">
                                    {{ $photo->prive ? 'Publier' : 'Masquer' }}
                                </button>
                                <button type="button" wire:click="delete({{ $photo->id }})"
                                        wire:confirm="Supprimer cette photo ?"
                                        class="rounded bg-white/90 px-1.5 py-0.5 text-[10px] font-medium text-red-600 shadow hover:bg-white dark:bg-gray-900/80">
                                    Suppr.
                                </button>
                            </div>
                        @endcan
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
