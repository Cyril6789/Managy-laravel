<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use BelongsToSociety;

    protected $fillable = [
        'society_id', 'intervention_id', 'created_by', 'number', 'issued_at',
        'issuer', 'customer', 'lines', 'subtotal_ht', 'total_ht', 'vat_enabled',
        'vat_rate', 'vat_amount', 'total_ttc', 'currency',
        'legal_notice', 'pdf_path',
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

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
