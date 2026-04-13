<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use App\Models\Appointment;



class Patient extends Model
{
    protected $fillable = [
        'full_name',
        'phone',
        'notes',
        'treatment_id',
        'sessions_purchased',
        'package_total',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sessions_purchased' => 'integer',
        'package_total' => 'decimal:2',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    // Ticket 4: relación a pagos (payments)
     public function payments()
     {
      return $this->hasMany(Payment::class);
      }
      public function appointments()
{
    return $this->hasMany(Appointment::class);
}
public function patientTreatments()
{
    return $this->hasMany(\App\Models\PatientTreatment::class);
}
public function patientTreatment()
{
    return $this->belongsTo(\App\Models\PatientTreatment::class);
}

public function packages()
{
    return $this->hasMany(\App\Models\PatientTreatment::class, 'patient_id');
}


 }


