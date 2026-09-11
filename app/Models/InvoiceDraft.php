<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDraft extends Model
{
    use BelongsToSociety;

    protected $fillable = ['society_id', 'intervention_id', 'client_id', 'created_by', 'lines', 'total_discount_type', 'total_discount_value'];

    protected function casts(): array
    {
        return ['lines' => 'array', 'total_discount_value' => 'decimal:2'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }
}
