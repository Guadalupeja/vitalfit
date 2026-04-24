<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    protected $fillable = [
        'branch_id',
        'treatment_type_id',
        'name',
        'category',
        'color_hex',
        'duration_minutes',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $appends = [
        'resolved_color_hex',
        'resolved_type_name',
    ];

    public function type()
    {
        return $this->belongsTo(TreatmentType::class, 'treatment_type_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getResolvedColorHexAttribute(): string
    {
        return $this->type?->color_hex
            ?? $this->color_hex
            ?? '#9CA3AF';
    }

    public function getResolvedTypeNameAttribute(): string
    {
        return $this->type?->name
            ?? $this->category
            ?? 'Sin tipo';
    }
}