<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antivirus extends Model
{
    use Auditable, BelongsToSociety, SoftDeletes;
    use HasFactory;

    protected $table = 'antivirus';

    protected $fillable = ['nom'];
}
