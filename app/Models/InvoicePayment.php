<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    use BelongsToSociety;

    protected $fillable = ['society_id', 'invoice_id', 'recorded_by', 'amount', 'paid_at', 'method', 'reference', 'note', 'state_pdf_path'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Un règlement enregistré est immuable.'));
        static::deleting(fn () => throw new \LogicException('Un règlement enregistré ne peut pas être supprimé.'));
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function methodLabel(): string
    {
        return ['especes' => 'Espèces', 'cb' => 'Carte bancaire', 'cheque' => 'Chèque', 'virement' => 'Virement', 'autre' => 'Autre'][$this->method] ?? $this->method;
    }
}
