@php $i = $intervention; @endphp
<x-print-sheet title="Fiche de dépôt {{ $i->reference }}">
    <h2>Dépôt</h2>
    <div class="num">N° {{ $i->reference }}</div>
    <p class="muted">{{ $i->opened_at?->format('d/m/Y H:i') }}</p>

    <x-slot:body>
        <div class="grid">
            <div style="flex:2">
                <div class="box">
                    <h3>Client</h3>
                    <strong>{{ $i->client?->nomComplet() }}</strong><br>
                    @if ($i->client?->telephone_mobile){{ $i->client->telephone_mobile }}<br>@endif
                    @if ($i->client?->email){{ $i->client->email }}@endif
                </div>
                <div class="box">
                    <h3>Matériel déposé</h3>
                    <div class="pre">{{ $i->materiel_depose ?: '—' }}</div>
                    @if ($i->materiel)<p class="muted">Type : {{ $i->materiel->nom }}</p>@endif
                </div>
                <div class="box">
                    <h3>Panne signalée</h3>
                    <div class="pre">{{ $i->panne ?: '—' }}</div>
                </div>
            </div>
            <div style="flex:1">
                <div class="box qr">
                    <h3>Suivi en ligne</h3>
                    {!! $qr !!}
                    <p class="muted" style="font-size:11px">Scannez pour suivre l'avancement</p>
                </div>
            </div>
        </div>

        <div class="sign">
            <div>Signature du client</div>
            <div>Signature du technicien</div>
        </div>
    </x-slot:body>
</x-print-sheet>
