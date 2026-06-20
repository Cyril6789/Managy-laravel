@php $i = $intervention; $s = $sousTraitance; @endphp
<x-print-sheet title="Bon de sous-traitance {{ $i->reference }}">
    <h2>Sous-traitance</h2>
    <div class="num">Interv. N° {{ $i->reference }}</div>
    <p class="muted">{{ now()->format('d/m/Y') }}</p>

    <x-slot:body>
        <div class="grid">
            <div style="flex:1">
                <div class="box">
                    <h3>Références</h3>
                    <p><strong>N° intervention :</strong> {{ $i->reference }}</p>
                    <p><strong>N° sous-traitance :</strong> {{ $s->numero_commande ?: '#'.$s->id }}</p>
                    @if ($s->devis)<p class="muted">Devis : {{ $s->devis }}</p>@endif
                </div>
                <div class="box">
                    <h3>Sous-traitant</h3>
                    <strong>{{ $s->nom ?: '—' }}</strong>
                </div>
            </div>
            <div style="flex:1">
                <div class="box">
                    <h3>Matériel confié</h3>
                    @if ($i->materiel)<p class="muted">Type : {{ $i->materiel->nom }}</p>@endif
                    <div class="pre">{{ $i->materiel_depose ?: '—' }}</div>
                </div>
                @if ($i->mdp)
                    <div class="box">
                        <h3>Mot de passe / accès</h3>
                        <div class="pre" style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 15px; font-weight: 600;">{{ $i->mdp }}</div>
                        <p class="muted" style="font-size: 11px;">Communiqué pour faciliter l'intervention du sous-traitant.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="box">
            <h3>Panne signalée</h3>
            <div class="pre">{{ $i->panne ?: '—' }}</div>
        </div>

        <div class="sign">
            <div>Remis le / par</div>
            <div>Réception sous-traitant</div>
        </div>

        <p class="muted" style="font-size: 11px; margin-top: 16px;">Document interne — ne comporte aucune donnée client.</p>
    </x-slot:body>
</x-print-sheet>
