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
        'branch_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sessions_purchased' => 'integer',
        'package_total' => 'decimal:2',
    ];

    protected $appends = [
        'commercial_status',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

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

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function getCommercialStatusAttribute(): string
    {
        $packagesCount = (int) ($this->packages_count ?? 0);

        return $packagesCount > 1 ? 'Reactivado' : 'Nuevo';
    }
    public function patientPackages()
{
    return $this->hasMany(\App\Models\PatientPackage::class);
}

public function packagesNew()
{
    return $this->hasMany(\App\Models\PatientPackage::class, 'patient_id');
}

}