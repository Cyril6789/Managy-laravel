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
                    <input type="checkbox" wire:model.live="prive" class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800 focus:ring-brand-500">
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

    {{-- Gallery + carousel (Alpine, shared staff/public — see photoCarousel in app.js) --}}
    @if ($photos->isEmpty())
        <p class="text-sm text-gray-400">
            {{ $public ? 'Aucune photo pour le moment.' : "Aucune photo pour cette intervention." }}
        </p>
    @else
        <div x-data="photoCarousel()">
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                @foreach ($photos as $photo)
                    @php
                        $url = $public
                            ? route('public.intervention.photo', [$token, $photo])
                            : route('interventions.photos.show', [$photo->intervention_id, $photo]);
                    @endphp
                    <div wire:key="photo-{{ $photo->id }}" class="group relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
                        {{-- The carousel reads its slides from these data-attributes at open time. --}}
                        <button type="button" data-carousel-item data-url="{{ $url }}" data-name="{{ $photo->original_name }}"
                                data-id="{{ $photo->id }}" data-prive="{{ $photo->prive ? '1' : '0' }}"
                                @click="show({{ $loop->index }})"
                                class="block h-full w-full cursor-zoom-in" title="Agrandir">
                            <img src="{{ $url }}" alt="{{ $photo->original_name }}" loading="lazy"
                                 class="h-full w-full object-cover transition group-hover:opacity-90">
                        </button>

                        @if (! $public)
                            @if ($photo->prive)
                                <span class="pointer-events-none absolute left-1 top-1 inline-flex items-center gap-1 rounded bg-gray-900/70 px-1.5 py-0.5 text-[10px] font-medium text-white">
                                    <x-icon name="lock" class="h-3 w-3" /> Privée
                                </span>
                            @endif

                            {{-- Actions toujours visibles (indispensable au tactile : pas de survol
                                 sur mobile). Suppression et bascule privée/publique. --}}
                            @can(\App\Support\Permissions::INTERVENTIONS_MANAGE)
                                <button type="button" wire:click="delete({{ $photo->id }})"
                                        wire:confirm="Supprimer cette photo ?"
                                        class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-sm font-bold leading-none text-red-600 shadow hover:bg-white dark:bg-gray-900/80"
                                        title="Supprimer la photo">&times;</button>
                                <button type="button" wire:click="togglePrivacy({{ $photo->id }})"
                                        class="absolute inset-x-1 bottom-1 rounded bg-white/90 px-1.5 py-0.5 text-[10px] font-medium text-gray-700 shadow hover:bg-white dark:bg-gray-900/80 dark:text-gray-200"
                                        title="{{ $photo->prive ? 'Rendre visible au client' : 'Masquer au client' }}">
                                    {{ $photo->prive ? 'Publier' : 'Masquer' }}
                                </button>
                            @endcan
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Fullscreen carousel overlay (teleported to body to escape card overflow/z-index) --}}
            <template x-teleport="body">
                <div x-show="open" x-cloak style="display:none"
                     @keydown.window="onKey($event)"
                     x-transition.opacity
                     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 backdrop-blur-sm"
                     @click="close()">
                    {{-- Close --}}
                    <button type="button" @click="close()"
                            class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-2xl leading-none text-white hover:bg-white/20"
                            title="Fermer (Échap)">&times;</button>

                    {{-- Prev --}}
                    <button type="button" x-show="slides.length > 1" @click.stop="prev()"
                            class="absolute left-2 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-3xl leading-none text-white hover:bg-white/20 sm:left-4"
                            title="Précédente (←)">&lsaquo;</button>

                    {{-- Image (swipe + tap-to-keep-open) --}}
                    <img :src="current.url" :alt="current.name" draggable="false"
                         @click.stop @touchstart.passive="touchStart($event)" @touchend.passive="touchEnd($event)"
                         class="max-h-[90vh] max-w-[92vw] select-none rounded-lg object-contain shadow-2xl">

                    {{-- Next --}}
                    <button type="button" x-show="slides.length > 1" @click.stop="next()"
                            class="absolute right-2 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-3xl leading-none text-white hover:bg-white/20 sm:right-4"
                            title="Suivante (→)">&rsaquo;</button>

                    {{-- Counter + caption + actions de gestion (staff) --}}
                    <div class="absolute inset-x-0 bottom-4 flex flex-col items-center gap-2 px-4 text-center text-sm text-white/90" @click.stop>
                        <span x-show="current.name" x-text="current.name" class="max-w-[90vw] truncate"></span>
                        <span x-show="slides.length > 1" class="rounded-full bg-white/10 px-3 py-1 text-xs"
                              x-text="(index + 1) + ' / ' + slides.length"></span>
                        @if (! $public)
                            @can(\App\Support\Permissions::INTERVENTIONS_MANAGE)
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="$wire.togglePrivacy(current.id); current.prive = !current.prive"
                                            class="rounded-full bg-white/10 px-3 py-1 text-xs font-medium hover:bg-white/20"
                                            x-text="current.prive ? 'Publier' : 'Masquer au client'"></button>
                                    <button type="button"
                                            @click="if (confirm('Supprimer cette photo ?')) { $wire.delete(current.id); close(); }"
                                            class="rounded-full bg-red-500/80 px-3 py-1 text-xs font-medium text-white hover:bg-red-500">
                                        Supprimer
                                    </button>
                                </div>
                            @endcan
                        @endif
                    </div>
                </div>
            </template>
        </div>
    @endif
</div>
