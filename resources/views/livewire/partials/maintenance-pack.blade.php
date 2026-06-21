{{-- Maintenance-pack settlement control, shared by the workshop & home closing
     paths. The explanatory message is rendered server-side (Blade) so it is
     always visible regardless of Alpine; the editable input — bound to the
     `restitution` Alpine component for the live total — is shown only when the
     deduction is actually possible (a pack, a positive balance and logged
     service hours).

     Expects: $hasPack (bool), $totalHeures (float), $solde (float), $hint (string). --}}
@php
    $packDispo = max(0.0, min((float) $totalHeures, (float) $solde));
    $packDispoLabel = rtrim(rtrim(number_format($packDispo, 2, ',', ' '), '0'), ',');
@endphp
<div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
    <span class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Régler des prestations avec le pack maintenance (facultatif)</span>

    @if (! $hasPack)
        <p class="text-xs text-gray-400">Ce client n'a pas de pack maintenance.</p>
    @elseif ((float) $totalHeures <= 0)
        <p class="text-xs text-gray-400">Ajoutez des prestations (avec des heures) dans l'onglet « Prestations &amp; pièces » pour pouvoir les régler depuis le pack.</p>
    @elseif ((float) $solde <= 0)
        <p class="text-xs text-gray-400">Le solde du pack maintenance est épuisé (0 h disponible).</p>
    @else
        <div class="flex items-center gap-2">
            <input type="text" inputmode="decimal" x-model="packHeures" @input="clampPack()" @blur="clampPack()"
                   class="block w-28 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
            <span class="text-sm text-gray-500">h / {{ $packDispoLabel }} h disponible(s)</span>
        </div>
        <p class="mt-1 text-xs text-gray-400" x-show="packCovered > 0" x-cloak x-text="'− ' + fmt(packCovered) + ' déduits des prestations (réglés par le pack).'"></p>
        <p class="mt-1 text-xs text-gray-400" x-show="!(packCovered > 0)">{{ $hint }}</p>
    @endif
</div>
