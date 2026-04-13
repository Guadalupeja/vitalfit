<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
protected $fillable = [
    'patient_id',
    'patient_treatment_id',
    'paid_at',
    'amount',
    'method',
    'reference',
    'note',
    'receipt_path',
    'created_by',
];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function package()
{
    return $this->belongsTo(\App\Models\PatientTreatment::class, 'patient_treatment_id');
}
public function creator()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}


}
