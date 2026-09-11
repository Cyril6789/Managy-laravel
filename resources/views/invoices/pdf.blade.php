<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 34px 42px; }
        body { color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { width: 100%; margin-bottom: 35px; }
        .header td { vertical-align: top; }
        .logo { max-width: 150px; max-height: 65px; margin-bottom: 8px; }
        .company-name { color: #315ee7; font-size: 19px; font-weight: bold; }
        .title { color: #315ee7; font-size: 27px; font-weight: bold; text-align: right; }
        .meta { margin-top: 8px; text-align: right; }
        .addresses { width: 100%; margin-bottom: 28px; }
        .addresses td { padding: 13px; vertical-align: top; width: 50%; }
        .issuer { background: #f4f6fa; }
        .customer { border: 1px solid #dce2ee; }
        .label { color: #6b7280; font-size: 8px; font-weight: bold; letter-spacing: .7px; margin-bottom: 7px; text-transform: uppercase; }
        table.lines { border-collapse: collapse; width: 100%; }
        .lines th { background: #315ee7; color: white; padding: 8px 7px; text-align: left; }
        .lines td { border-bottom: 1px solid #e5e7eb; padding: 8px 7px; }
        .lines .num { text-align: right; white-space: nowrap; }
        .summary { margin-top: 18px; width: 100%; }
        .summary > tbody > tr > td { vertical-align: top; }
        .conditions { color: #4b5563; font-size: 8px; line-height: 1.45; padding-right: 24px; width: 55%; }
        .conditions-title { color: #172033; font-size: 8px; font-weight: bold; margin: 0 0 4px; text-transform: uppercase; }
        .conditions-section + .conditions-section { margin-top: 10px; }
        .totals-cell { width: 45%; }
        .totals { border-collapse: collapse; width: 100%; }
        .totals td { padding: 7px 8px; }
        .totals .grand { background: #eef2ff; color: #2347b6; font-size: 15px; font-weight: bold; }
        .footer { border-top: 1px solid #dce2ee; bottom: 20px; color: #6b7280; font-size: 8px; left: 42px; padding-top: 10px; position: fixed; right: 42px; text-align: center; }
        .reference { color: #6b7280; margin-bottom: 12px; }
    </style>
</head>
<body>
    @php($issuer = $invoice->issuer)
    @php($customer = $invoice->customer)
    <table class="header">
        <tr>
            <td>
                @if($logoDataUri)<img class="logo" src="{{ $logoDataUri }}" alt="Logo"><br>@endif
                <span class="company-name">{{ $issuer['name'] }}</span>
            </td>
            <td>
                <div class="title">FACTURE</div>
                <div class="meta"><strong>{{ $invoice->number }}</strong><br>Date : {{ $invoice->issued_at->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="addresses" cellspacing="8">
        <tr>
            <td class="issuer">
                <div class="label">Émetteur</div>
                <strong>{{ $issuer['name'] }}</strong><br>
                {{ $issuer['address'] ?? '' }}<br>
                {{ trim(($issuer['postal_code'] ?? '').' '.($issuer['city'] ?? '')) }}<br>
                @if($issuer['phone'] ?? null)Tél. {{ $issuer['phone'] }}<br>@endif
                @if($issuer['email'] ?? null){{ $issuer['email'] }}<br>@endif
                @if($issuer['siret'] ?? null)SIRET : {{ $issuer['siret'] }}@endif
            </td>
            <td class="customer">
                <div class="label">Facturé à</div>
                <strong>{{ $customer['name'] }}</strong><br>
                @if($customer['contact'] ?? null)Contact : {{ $customer['contact'] }}<br>@endif
                {{ $customer['address'] ?? '' }}<br>
                @if($customer['address_complement'] ?? null){{ $customer['address_complement'] }}<br>@endif
                {{ trim(($customer['postal_code'] ?? '').' '.($customer['city'] ?? '')) }}<br>
                @if($customer['phone'] ?? null)Tél. {{ $customer['phone'] }}<br>@endif
                @if($customer['email'] ?? null){{ $customer['email'] }}<br>@endif
                @if($customer['siret'] ?? null)SIRET : {{ $customer['siret'] }}@endif
            </td>
        </tr>
    </table>

    @if ($invoice->intervention)<div class="reference">Intervention : <strong>{{ $invoice->intervention->reference }}</strong></div>@endif
    <table class="lines">
        <thead><tr><th>Désignation</th><th class="num">Qté</th><th>Unité</th><th class="num">P.U. HT</th><th class="num">TVA</th><th class="num">Total HT</th><th class="num">Total TTC</th></tr></thead>
        <tbody>
        @foreach($invoice->lines as $line)
            <tr>
                <td>{{ $line['description'] }}</td>
                <td class="num">{{ number_format($line['quantity'], 2, ',', ' ') }}</td>
                <td>{{ $line['unit'] }}</td>
                <td class="num">{{ number_format($line['unit_price_ht'], 2, ',', ' ') }} €</td>
                <td class="num">{{ number_format($line['vat_rate'] ?? 0, 2, ',', ' ') }} %</td>
                <td class="num">{{ number_format($line['total_ht'], 2, ',', ' ') }} €</td>
                <td class="num">{{ number_format($line['total_ttc'] ?? $line['total_ht'], 2, ',', ' ') }} €</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="conditions">
                @if($invoice->terms)
                    <div class="conditions-section">
                        <div class="conditions-title">Conditions générales</div>
                        {!! nl2br(e($invoice->terms)) !!}
                    </div>
                @endif
                @if($invoice->payment_terms)
                    <div class="conditions-section">
                        <div class="conditions-title">Conditions de paiement</div>
                        {!! nl2br(e($invoice->payment_terms)) !!}
                    </div>
                @endif
            </td>
            <td class="totals-cell">
                <table class="totals">
                    <tr><td>Sous-total HT</td><td class="num">{{ number_format($invoice->subtotal_ht, 2, ',', ' ') }} €</td></tr>
                    <tr><td>Total HT</td><td class="num">{{ number_format($invoice->total_ht, 2, ',', ' ') }} €</td></tr>
                    @if ($invoice->vat_enabled)
                        <tr><td>TVA ({{ number_format($invoice->vat_rate, 2, ',', ' ') }} %)</td><td class="num">{{ number_format($invoice->vat_amount, 2, ',', ' ') }} €</td></tr>
                    @endif
                    <tr class="grand"><td>Total TTC</td><td class="num">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        @if ($invoice->legal_notice){{ $invoice->legal_notice }}@endif
        @if($issuer['siret'] ?? null) — SIRET {{ $issuer['siret'] }}@endif
    </div>
</body>
</html>
