<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'treatment_id',
        'specialist_id',
        'created_by',
        'start_at',
        'end_at',
        'status',
        'notes',
        'patient_treatment_id',

    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function specialist()
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function patientTreatment()
{
    return $this->belongsTo(\App\Models\PatientTreatment::class);
}
public function package()
{
    return $this->belongsTo(\App\Models\PatientTreatment::class, 'patient_treatment_id');
}


}
