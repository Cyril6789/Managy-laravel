<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Society;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InvoiceGenerator
{
    public const LEGAL_NOTICE = 'TVA non applicable, article 293 B du Code général des impôts.';

    public function generate(Intervention $intervention): Invoice
    {
        if ($existing = $intervention->invoice()->first()) {
            return $existing;
        }

        return $this->generateFromIntervention($intervention, $this->draftLinesForIntervention($intervention));
    }

    public function generateFromIntervention(Intervention $intervention, array $lines, ?string $discountType = null, float $discountValue = 0): Invoice
    {
        $this->assertInterventionCanBeInvoiced($intervention);
        $intervention->loadMissing(['client', 'contact']);

        return $this->issue($intervention->client, $lines, $intervention, $discountType, $discountValue);
    }

    public function generateManual(Client $client, array $lines, ?string $discountType = null, float $discountValue = 0): Invoice
    {
        if (! $client->society?->invoice_enabled) {
            throw ValidationException::withMessages(['invoice' => 'Le module de facturation PDF n’est pas activé pour cette société.']);
        }

        return $this->issue($client, $lines, null, $discountType, $discountValue);
    }

    public function draftLinesForIntervention(Intervention $intervention): array
    {
        $this->assertInterventionCanBeInvoiced($intervention);
        $intervention->loadMissing(['client', 'contact', 'prestations', 'pieces']);

        return collect($this->lineSnapshots($intervention))->map(fn (array $line) => [
            'description' => $line['description'],
            'quantity' => (float) $line['quantity'],
            'unit' => $line['unit'],
            'unit_price_ht' => (float) $line['unit_price_ht'],
            'vat_rate' => filter_var(Setting::get('invoice_vat_enabled', false), FILTER_VALIDATE_BOOLEAN)
                ? (float) Setting::get('invoice_vat_rate', 20)
                : 0.0,
            'discount_type' => '',
            'discount_value' => 0,
        ])->all();
    }

    private function assertInterventionCanBeInvoiced(Intervention $intervention): void
    {
        if (! $intervention->society?->invoice_enabled) {
            throw ValidationException::withMessages(['invoice' => 'Le module de facturation PDF n’est pas activé pour cette société.']);
        }

        if ($intervention->invoice_ignored_at) {
            throw ValidationException::withMessages(['invoice' => 'Cette intervention est ignorée de la facturation. Réintégrez-la avant de générer une facture.']);
        }

        if (! $intervention->closed_at) {
            throw ValidationException::withMessages(['invoice' => 'Seule une intervention clôturée peut être facturée.']);
        }

        if ($existing = $intervention->invoice()->first()) {
            throw ValidationException::withMessages(['invoice' => 'Cette intervention possède déjà une facture définitive.']);
        }
    }

    private function issue(Client $client, array $lines, ?Intervention $intervention, ?string $discountType, float $discountValue): Invoice
    {
        if ($lines === []) {
            throw ValidationException::withMessages(['lines' => 'Ajoutez au moins une ligne à la facture.']);
        }

        return DB::transaction(function () use ($client, $lines, $intervention, $discountType, $discountValue) {
            Society::query()->whereKey($client->society_id)->lockForUpdate()->firstOrFail();
            if ($intervention && Invoice::query()->where('intervention_id', $intervention->id)->exists()) {
                throw ValidationException::withMessages(['invoice' => 'Cette intervention possède déjà une facture définitive.']);
            }
            [$number, $sequence, $year] = $this->reserveNumber();
            $vatEnabled = filter_var(Setting::get('invoice_vat_enabled', false), FILTER_VALIDATE_BOOLEAN);
            $normalized = collect($lines)->map(function (array $line) use ($vatEnabled) {
                $quantity = max(0.01, (float) $line['quantity']);
                $unitPrice = round((float) $line['unit_price_ht'], 2);
                $gross = round($quantity * $unitPrice, 2);
                $lineDiscount = $this->discountAmount($gross, $line['discount_type'] ?? null, (float) ($line['discount_value'] ?? 0));
                $total = round($gross - $lineDiscount, 2);
                $rate = $vatEnabled ? max(0, (float) ($line['vat_rate'] ?? Setting::get('invoice_vat_rate', 20))) : 0.0;

                return $this->line((string) $line['description'], $quantity, (string) ($line['unit'] ?? 'u'), $unitPrice, $total) + [
                    'vat_rate' => $rate,
                    'discount_type' => $lineDiscount > 0 ? ($line['discount_type'] ?? null) : null,
                    'discount_value' => $lineDiscount > 0 ? (float) ($line['discount_value'] ?? 0) : 0,
                    'discount_amount' => $lineDiscount,
                ];
            })->all();
            $normalized = $this->applyTotalDiscount($normalized, $discountType, $discountValue);
            $normalized = $this->applyVat($normalized);
            $totalHt = round(collect($normalized)->sum('total_ht'), 2);
            $vatAmount = round(collect($normalized)->sum('vat_amount'), 2);
            $totalTtc = round(collect($normalized)->sum('total_ttc'), 2);
            $rates = collect($normalized)->pluck('vat_rate')->unique();
            $fileNumber = trim(preg_replace('/[^A-Za-z0-9._-]+/', '-', $number), '-');

            $invoice = Invoice::create([
                'society_id' => $client->society_id,
                'intervention_id' => $intervention?->id,
                'client_id' => $client->id,
                'created_by' => Auth::id(),
                'number' => $number,
                'issued_at' => today(),
                'issuer' => $this->issuerSnapshot(),
                'customer' => $intervention ? $this->customerSnapshot($intervention) : $this->clientSnapshot($client),
                'lines' => $normalized,
                'subtotal_ht' => round(collect($normalized)->where('total_ht', '>', 0)->sum('total_ht'), 2),
                'total_ht' => $totalHt,
                'vat_enabled' => $vatEnabled,
                'vat_rate' => $rates->count() === 1 ? (float) $rates->first() : 0,
                'vat_amount' => $vatAmount,
                'total_ttc' => $totalTtc,
                'currency' => 'EUR',
                'legal_notice' => $vatEnabled ? '' : self::LEGAL_NOTICE,
                'terms' => Setting::get('invoice_terms'),
                'payment_terms' => Setting::get('invoice_payment_terms'),
                'pdf_path' => "invoices/{$client->society_id}/{$year}/{$fileNumber}.pdf",
            ]);

            Storage::disk('local')->put($invoice->pdf_path, $this->renderPdf($invoice));
            $intervention?->update(['facturee' => true]);
            Setting::put('invoice_next_number', $sequence + 1);

            return $invoice;
        });
    }

    private function discountAmount(float $base, ?string $type, float $value): float
    {
        if ($base <= 0 || $value <= 0) {
            return 0.0;
        }

        return $type === 'pourcent'
            ? min($base, round($base * min($value, 100) / 100, 2))
            : ($type === 'euro' ? min($base, round($value, 2)) : 0.0);
    }

    private function applyTotalDiscount(array $lines, ?string $type, float $value): array
    {
        $base = round(collect($lines)->where('total_ht', '>', 0)->sum('total_ht'), 2);
        $discount = $this->discountAmount($base, $type, $value);
        if ($discount <= 0) {
            return $lines;
        }

        $remaining = $discount;
        $groups = collect($lines)->where('total_ht', '>', 0)->groupBy('vat_rate');
        foreach ($groups as $rate => $group) {
            $groupBase = (float) $group->sum('total_ht');
            $amount = $rate === $groups->keys()->last()
                ? $remaining
                : round($discount * $groupBase / $base, 2);
            $remaining = round($remaining - $amount, 2);
            $label = $type === 'pourcent' ? "Remise globale ({$value} %)" : 'Remise globale';
            $lines[] = $this->line($label, 1, '', -$amount, -$amount) + ['vat_rate' => (float) $rate];
        }

        return $lines;
    }

    private function reserveNumber(): array
    {
        $year = now()->format('Y');
        $format = (string) (Setting::get('invoice_number_format') ?: 'FAC-{YYYY}-####');
        $sequence = max(1, (int) (Setting::get('invoice_next_number') ?: $this->legacyNextNumber($year)));
        $number = $this->formatNumber($format, $sequence);

        while (Invoice::query()->where('number', $number)->exists()) {
            $number = $this->formatNumber($format, ++$sequence);
        }

        return [$number, $sequence, $year];
    }

    private function applyVat(array $lines, ?float $defaultRate = null): array
    {
        return collect($lines)->map(function (array $line) use ($defaultRate) {
            $rate = $defaultRate ?? (float) ($line['vat_rate'] ?? 0);
            $vat = round((float) $line['total_ht'] * $rate / 100, 2);

            return $line + ['vat_rate' => $rate, 'vat_amount' => $vat, 'total_ttc' => round((float) $line['total_ht'] + $vat, 2)];
        })->all();
    }

    private function formatNumber(string $format, int $sequence): string
    {
        $number = str_replace(
            ['{YYYY}', '{YY}', '{MM}'],
            [now()->format('Y'), now()->format('y'), now()->format('m')],
            $format,
        );

        return preg_replace_callback('/#+/', fn (array $match) => str_pad((string) $sequence, strlen($match[0]), '0', STR_PAD_LEFT), $number);
    }

    private function legacyNextNumber(string $year): int
    {
        return Invoice::query()
            ->whereYear('issued_at', $year)
            ->pluck('number')
            ->map(fn (string $number) => preg_match('/-(\d+)$/', $number, $match) ? (int) $match[1] : 0)
            ->max() + 1;
    }

    private function issuerSnapshot(): array
    {
        return [
            'name' => Setting::get('company_name') ?: config('app.name'),
            'address' => Setting::get('company_address'),
            'postal_code' => Setting::get('company_postal_code'),
            'city' => Setting::get('company_city'),
            'phone' => Setting::get('company_phone'),
            'email' => Setting::get('company_email'),
            'website' => Setting::get('company_website'),
            'siret' => Setting::get('company_siret'),
        ];
    }

    private function customerSnapshot(Intervention $intervention): array
    {
        return $this->clientSnapshot($intervention->client, $intervention->contact?->nomComplet());
    }

    private function clientSnapshot(?Client $client, ?string $contact = null): array
    {
        return [
            'name' => $client?->nomComplet() ?: 'Client inconnu',
            'address' => $client?->adresse,
            'address_complement' => $client?->adresse_complement,
            'postal_code' => $client?->code_postal,
            'city' => $client?->ville,
            'phone' => $client?->telephone_mobile ?: $client?->telephone_fixe,
            'email' => $client?->email,
            'siret' => $client?->siret,
            'contact' => $contact,
        ];
    }

    private function lineSnapshots(Intervention $intervention): array
    {
        $lines = [];
        $servicesGross = 0.0;
        foreach ($intervention->prestations as $prestation) {
            $total = round($prestation->montant(), 2);
            $servicesGross += $total;
            $lines[] = $this->line($prestation->designation ?: 'Prestation', (float) $prestation->duree, 'h', (float) $prestation->tarif, $total);
        }

        $servicesNet = (float) ($intervention->montant_prestations ?? $servicesGross);
        if (($discount = round($servicesNet - $servicesGross, 2)) !== 0.0) {
            $lines[] = $this->line('Remise sur prestations', 1, '', $discount, $discount);
        }

        $partsGross = 0.0;
        foreach ($intervention->pieces as $piece) {
            $total = round($piece->total(), 2);
            $partsGross += $total;
            $lines[] = $this->line($piece->designation ?: 'Pièce', (float) $piece->quantite, 'u', (float) $piece->prix, $total);
        }

        $partsNet = (float) ($intervention->montant_pieces ?? $partsGross);
        if (($discount = round($partsNet - $partsGross, 2)) !== 0.0) {
            $lines[] = $this->line('Remise sur pièces', 1, '', $discount, $discount);
        }

        if ((float) $intervention->remise_montant > 0) {
            $label = $intervention->remise_type === 'pourcent'
                ? 'Ristourne ('.rtrim(rtrim((string) $intervention->remise_valeur, '0'), '.').' %)'
                : 'Ristourne commerciale';
            $amount = -(float) $intervention->remise_montant;
            $lines[] = $this->line($label, 1, '', $amount, $amount);
        }

        if ((float) $intervention->montant_maintenance > 0) {
            $amount = -(float) $intervention->montant_maintenance;
            $lines[] = $this->line('Règlement par forfait maintenance', (float) $intervention->maintenance_heures, 'h', $amount, $amount);
        }

        if ((float) $intervention->montant_deplacement > 0) {
            $amount = (float) $intervention->montant_deplacement;
            $lines[] = $this->line('Frais de déplacement', 1, 'forfait', $amount, $amount);
        }

        $expected = (float) ($intervention->montant_total ?? collect($lines)->sum('total_ht'));
        $actual = round(collect($lines)->sum('total_ht'), 2);
        if (($adjustment = round($expected - $actual, 2)) !== 0.0) {
            $label = $intervention->garantie ? 'Prise en charge sous garantie' : 'Ajustement de facturation';
            $lines[] = $this->line($label, 1, '', $adjustment, $adjustment);
        }

        if ($lines === []) {
            $lines[] = $this->line('Intervention '.$intervention->reference, 1, 'forfait', 0, 0);
        }

        return $lines;
    }

    private function line(string $description, float $quantity, string $unit, float $unitPrice, float $total): array
    {
        return compact('description', 'quantity', 'unit', 'unitPrice', 'total') + ['unit_price_ht' => round($unitPrice, 2), 'total_ht' => round($total, 2)];
    }

    public function renderPdf(Invoice $invoice, $payments = null, ?string $paymentStatus = null): string
    {
        $logoDataUri = null;
        $logoPath = Setting::get('company_logo');
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $mime = Storage::disk('public')->mimeType($logoPath) ?: 'image/png';
            $logoDataUri = 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($logoPath));
        }

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('invoices.pdf', compact('invoice', 'logoDataUri', 'payments', 'paymentStatus'))->render(), 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }
}
