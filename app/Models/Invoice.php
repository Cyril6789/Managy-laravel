<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToSociety;

    protected $fillable = [
        'society_id', 'intervention_id', 'client_id', 'created_by', 'number', 'issued_at',
        'issuer', 'customer', 'lines', 'subtotal_ht', 'total_ht', 'vat_enabled',
        'vat_rate', 'vat_amount', 'total_ttc', 'currency',
        'legal_notice', 'terms', 'payment_terms', 'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'issuer' => 'array',
            'customer' => 'array',
            'lines' => 'array',
            'subtotal_ht' => 'decimal:2',
            'total_ht' => 'decimal:2',
            'vat_enabled' => 'boolean',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_ttc' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Une facture émise est immuable.'));
        static::deleting(fn () => throw new \LogicException('Une facture émise ne peut pas être supprimée.'));
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('paid_at')->orderBy('id');
    }

    public function paidAmount(): float
    {
        return round((float) ($this->relationLoaded('payments') ? $this->payments->sum('amount') : $this->payments()->sum('amount')), 2);
    }

    public function balanceDue(): float
    {
        return round(max(0, (float) $this->total_ttc - $this->paidAmount()), 2);
    }

    public function paymentStatus(): string
    {
        $paid = $this->paidAmount();

        return $paid <= 0 ? 'pending' : ($paid + 0.001 >= (float) $this->total_ttc ? 'paid' : 'partial');
    }

    public function paymentStatusLabel(): string
    {
        return ['pending' => 'En attente de paiement', 'partial' => 'Partiellement payée', 'paid' => 'Payée'][$this->paymentStatus()];
    }
}
