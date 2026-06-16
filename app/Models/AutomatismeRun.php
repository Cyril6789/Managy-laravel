<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomatismeRun extends Model
{
    public $timestamps = false;

    protected $fillable = ['automatisme_id', 'intervention_id', 'ran_at'];

    protected function casts(): array
    {
        return ['ran_at' => 'datetime'];
    }
}
