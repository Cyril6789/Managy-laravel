<?php

namespace App\Services;

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
        if (! $intervention->society?->invoice_enabled) {
            throw ValidationException::withMessages(['invoice' => 'Le module de facturation PDF n’est pas activé pour cette société.']);
        }

        if (! $intervention->closed_at) {
            throw ValidationException::withMessages(['invoice' => 'Seule une intervention clôturée peut être facturée.']);
        }

        if ($existing = $intervention->invoice()->first()) {
            return $existing;
        }

        $intervention->loadMissing(['client', 'contact', 'prestations', 'pieces']);

        return DB::transaction(function () use ($intervention) {
            Society::query()->whereKey($intervention->society_id)->lockForUpdate()->firstOrFail();

            if ($existing = Invoice::query()->where('intervention_id', $intervention->id)->first()) {
                return $existing;
            }

            $year = now()->format('Y');
            $format = (string) (Setting::get('invoice_number_format') ?: 'FAC-{YYYY}-####');
            $sequence = max(1, (int) (Setting::get('invoice_next_number') ?: $this->legacyNextNumber($year)));
            $number = $this->formatNumber($format, $sequence);

            while (Invoice::query()->where('number', $number)->exists()) {
                $number = $this->formatNumber($format, ++$sequence);
            }

            $issuer = $this->issuerSnapshot();
            $customer = $this->customerSnapshot($intervention);
            $lines = $this->lineSnapshots($intervention);
            $subtotal = round(collect($lines)->where('total_ht', '>', 0)->sum('total_ht'), 2);
            $total = round(collect($lines)->sum('total_ht'), 2);
            $vatEnabled = filter_var(Setting::get('invoice_vat_enabled', false), FILTER_VALIDATE_BOOLEAN);
            $vatRate = $vatEnabled ? max(0, (float) Setting::get('invoice_vat_rate', 20)) : 0.0;
            $vatAmount = round($total * $vatRate / 100, 2);
            $totalTtc = round($total + $vatAmount, 2);

            $fileNumber = trim(preg_replace('/[^A-Za-z0-9._-]+/', '-', $number), '-');
            $invoice = Invoice::create([
                'society_id' => $intervention->society_id,
                'intervention_id' => $intervention->id,
                'created_by' => Auth::id(),
                'number' => $number,
                'issued_at' => today(),
                'issuer' => $issuer,
                'customer' => $customer,
                'lines' => $lines,
                'subtotal_ht' => $subtotal,
                'total_ht' => $total,
                'vat_enabled' => $vatEnabled,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total_ttc' => $totalTtc,
                'currency' => 'EUR',
                'legal_notice' => $vatEnabled ? '' : self::LEGAL_NOTICE,
                'pdf_path' => "invoices/{$intervention->society_id}/{$year}/{$fileNumber}.pdf",
            ]);

            Storage::disk('local')->put($invoice->pdf_path, $this->renderPdf($invoice));
            $intervention->update(['facturee' => true]);
            Setting::put('invoice_next_number', $sequence + 1);

            return $invoice;
        });
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
        $client = $intervention->client;

        return [
            'name' => $client?->nomComplet() ?: 'Client inconnu',
            'address' => $client?->adresse,
            'address_complement' => $client?->adresse_complement,
            'postal_code' => $client?->code_postal,
            'city' => $client?->ville,
            'phone' => $client?->telephone_mobile ?: $client?->telephone_fixe,
            'email' => $client?->email,
            'siret' => $client?->siret,
            'contact' => $intervention->contact?->nomComplet(),
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

        return $lines;
    }

    private function line(string $description, float $quantity, string $unit, float $unitPrice, float $total): array
    {
        return compact('description', 'quantity', 'unit', 'unitPrice', 'total') + ['unit_price_ht' => round($unitPrice, 2), 'total_ht' => round($total, 2)];
    }

    private function renderPdf(Invoice $invoice): string
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
        $dompdf->loadHtml(view('invoices.pdf', compact('invoice', 'logoDataUri'))->render(), 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }
}
