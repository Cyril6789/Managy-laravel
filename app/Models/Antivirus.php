<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antivirus extends Model
{
    use BelongsToSociety;
    use HasFactory;

    protected $table = 'antivirus';

    protected $fillable = ['nom'];
}
