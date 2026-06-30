<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;

class AutomatismeRun extends Model
{
    use BelongsToSociety;
    public $timestamps = false;

    protected $fillable = ['automatisme_id', 'intervention_id', 'ran_at'];

    protected function casts(): array
    {
        return ['ran_at' => 'datetime'];
    }
}
