<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    protected $fillable = [
        'name',
        'category',
        'duration_minutes',
        'color_hex',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'duration_minutes' => 'integer',
    ];
}
