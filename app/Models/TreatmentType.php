<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentType extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'color_hex',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function treatments()
    {
        return $this->hasMany(Treatment::class, 'treatment_type_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}