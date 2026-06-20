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
            @if ($i->pieces->isNotEmpty())
                <h3 style="margin-top:10px">Pièces remplacées</h3>
                <table>
                    <thead><tr><th>Désignation</th><th class="right">Qté</th><th class="right">P.U.</th><th class="right">Total</th></tr></thead>
                    <tbody>
                        @foreach ($i->pieces as $p)
                            <tr><td>{{ $p->designation }}</td><td class="right">{{ rtrim(rtrim(number_format($p->quantite, 2), '0'), '.') }}</td><td class="right">{{ number_format($p->prix, 2, ',', ' ') }} €</td><td class="right">{{ number_format($p->total(), 2, ',', ' ') }} €</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if ($i->montant_total !== null)
                <table style="margin-top:10px">
                    <tbody>
                        @if ($i->montant_prestations !== null)<tr><td>Prestations</td><td class="right">{{ number_format($i->montant_prestations, 2, ',', ' ') }} €</td></tr>@endif
                        @if ($i->montant_pieces)<tr><td>Pièces</td><td class="right">{{ number_format($i->montant_pieces, 2, ',', ' ') }} €</td></tr>@endif
                        @if ($i->remise_montant)<tr><td>Ristourne</td><td class="right">− {{ number_format($i->remise_montant, 2, ',', ' ') }} €</td></tr>@endif
                        @if ($i->montant_deplacement)<tr><td>Déplacement</td><td class="right">{{ number_format($i->montant_deplacement, 2, ',', ' ') }} €</td></tr>@endif
                        <tr><th>Total</th><th class="right">{{ number_format($i->montant_total, 2, ',', ' ') }} €</th></tr>
                        @if ($i->payee)<tr><td>Réglé{{ $i->paiement_mode ? ' ('.$i->paiement_mode.')' : '' }}</td><td class="right">{{ number_format($i->montant_paye ?? $i->montant_total, 2, ',', ' ') }} €</td></tr>@endif
                    </tbody>
                </table>
            @elseif ($i->tarif_estimatif)
                <p style="margin-top:8px"><strong>Tarif estimatif :</strong> {{ number_format($i->tarif_estimatif, 2, ',', ' ') }} €</p>
            @endif
        </div>

        @if (($soldeMaintenance ?? null) !== null)
            <div class="box">
                <h3>Pack maintenance</h3>
                <strong>Solde restant : {{ number_format($soldeMaintenance, 2, ',', ' ') }} h</strong>
            </div>
        @endif

        @php
            $sigData = null;
            if ($i->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($i->signature_path)) {
                $sigData = 'data:image/png;base64,'.base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($i->signature_path));
            }
        @endphp
        <div class="sign">
            <div>
                Signature du client
                @if ($i->signataire_nom)
                    ({{ $i->signataire_nom }})
                @endif
                @if ($sigData)
                    <br><img src="{{ $sigData }}" alt="signature" style="max-height:70px; margin-top:4px">
                @endif
                @if ($i->signed_at)
                    <br><span style="font-size:11px; color:#777">Signé le {{ $i->signed_at->format('d/m/Y H:i') }}</span>
                @endif
            </div>
            <div>Signature du technicien</div>
        </div>
    </x-slot:body>
</x-print-sheet>
