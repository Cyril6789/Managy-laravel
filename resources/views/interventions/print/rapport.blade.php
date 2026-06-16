@php $i = $intervention; @endphp
<x-print-sheet title="Rapport {{ $i->reference }}">
    <h2>Rapport</h2>
    <div class="num">N° {{ $i->reference }}</div>
    <p class="muted">{{ ($i->closed_at ?? $i->opened_at)?->format('d/m/Y') }}</p>

    <x-slot:body>
        <div class="box">
            <h3>Client</h3>
            <strong>{{ $i->client?->nomComplet() }}</strong>
            @if ($i->client?->adresseComplete()) — {{ $i->client->adresseComplete() }}@endif
        </div>

        <div class="box">
            <h3>Rapport technique</h3>
            <div class="pre">{{ $i->diagnostic ?: '—' }}</div>
        </div>

        @if ($i->materiel_ajoute)
            <div class="box">
                <h3>Matériel ajouté / remplacé</h3>
                <div class="pre">{{ $i->materiel_ajoute }}</div>
            </div>
        @endif

        @if ($i->message_client)
            <div class="box">
                <h3>Message au client</h3>
                <div class="pre">{{ $i->message_client }}</div>
            </div>
        @endif

        <div class="box">
            <h3>Prestations réalisées</h3>
            @if ($i->prestations->isNotEmpty())
                <table>
                    <thead><tr><th>Désignation</th><th class="right">Durée (h)</th></tr></thead>
                    <tbody>
                        @foreach ($i->prestations as $p)
                            <tr><td>{{ $p->designation }}</td><td class="right">{{ rtrim(rtrim(number_format($p->duree, 2), '0'), '.') }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th>Total</th><th class="right">{{ rtrim(rtrim(number_format($i->tempsTotal(), 2), '0'), '.') }} h</th></tr>
                    </tfoot>
                </table>
            @else
                <p class="muted">Aucune prestation enregistrée.</p>
            @endif
            @if ($i->tarif_estimatif)<p style="margin-top:8px"><strong>Tarif estimatif :</strong> {{ number_format($i->tarif_estimatif, 2, ',', ' ') }} €</p>@endif
        </div>

        <div class="sign">
            <div>Signature du client</div>
            <div>Signature du technicien</div>
        </div>
    </x-slot:body>
</x-print-sheet>
