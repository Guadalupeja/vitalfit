<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientTreatment extends Model
{
    protected $fillable = [
        'patient_id',
        'treatment_id',
        'sessions_purchased',
        'package_total',
        'status',
        'started_on',
        'ends_on',
        'notes',
    ];

    protected $casts = [
        'sessions_purchased' => 'integer',
        'package_total' => 'decimal:2',
        'started_on' => 'date',
        'ends_on' => 'date',
    ];

    public function patient()
    {
      return $this->belongsTo(\App\Models\Patient::class);

    }

    public function treatment()
    {
    return $this->belongsTo(\App\Models\Treatment::class);
    }
    public function appointments()
{
    return $this->hasMany(\App\Models\Appointment::class, 'patient_treatment_id');
}

public function payments()
{
    return $this->hasMany(\App\Models\Payment::class, 'patient_treatment_id');
}

}
