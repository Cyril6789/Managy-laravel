@props(['title'])
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ $appSettings['company_name'] ?? 'Managy' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; color: #111; margin: 0; padding: 24px; font-size: 13px; line-height: 1.45; }
        .sheet { max-width: 760px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111; padding-bottom: 12px; margin-bottom: 18px; }
        .company h1 { font-size: 18px; margin: 0 0 4px; }
        .company p { margin: 0; color: #555; font-size: 12px; }
        .logo { max-height: 56px; max-width: 200px; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .doc-title .num { font-size: 16px; font-weight: bold; color: #2563eb; }
        .grid { display: flex; gap: 24px; }
        .box { border: 1px solid #ddd; border-radius: 8px; padding: 12px 14px; margin-bottom: 14px; }
        .box h3 { margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #666; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 6px 4px; border-bottom: 1px solid #eee; }
        th { font-size: 11px; text-transform: uppercase; color: #666; }
        .right { text-align: right; }
        .qr { text-align: center; }
        .qr svg { width: 150px; height: 150px; }
        .sign { margin-top: 28px; display: flex; justify-content: space-between; gap: 24px; }
        .sign div { flex: 1; border-top: 1px solid #999; padding-top: 6px; font-size: 12px; color: #555; }
        footer { margin-top: 28px; border-top: 1px solid #ddd; padding-top: 8px; font-size: 11px; color: #777; text-align: center; }
        .pre { white-space: pre-line; }
        @media print { body { padding: 0; } .noprint { display: none; } @page { margin: 14mm; } }
        .noprint { text-align: center; margin-bottom: 16px; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; border: none; cursor: pointer; }
    </style>
</head>
<body onload="if (location.hash !== '#noprint') window.print()">
<div class="noprint"><button class="btn" onclick="window.print()">Imprimer / PDF</button></div>
<div class="sheet">
    @php $s = $appSettings; @endphp
    <header>
        <div class="company">
            @if (!empty($s['company_logo']))
                <img class="logo" src="{{ \Illuminate\Support\Facades\Storage::url($s['company_logo']) }}" alt="logo">
            @else
                <h1>{{ $s['company_name'] ?? 'Mon entreprise' }}</h1>
            @endif
            <p>{{ $s['company_address'] ?? '' }} {{ trim(($s['company_postal_code'] ?? '').' '.($s['company_city'] ?? '')) }}</p>
            <p>{{ $s['company_phone'] ?? '' }} @if(!empty($s['company_email'])) · {{ $s['company_email'] }} @endif</p>
        </div>
        <div class="doc-title">
            {{ $slot }}
        </div>
    </header>
    {{ $body }}
    <footer>
        {{ $s['company_name'] ?? '' }}
        @if (!empty($s['company_siret'])) · SIRET {{ $s['company_siret'] }} @endif
        @if (!empty($s['company_vat'])) · TVA {{ $s['company_vat'] }} @endif
    </footer>
</div>
</body>
</html>
