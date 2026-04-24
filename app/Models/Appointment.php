<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'branch_id',
        'patient_id',
        'patient_package_id',
        'patient_package_item_id',
        'treatment_id',
        'specialist_id',
        'created_by',
        'start_at',
        'end_at',
        'status',
        'notes',
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

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function patientPackage()
    {
        return $this->belongsTo(\App\Models\PatientPackage::class, 'patient_package_id');
    }

    public function patientPackageItem()
    {
        return $this->belongsTo(\App\Models\PatientPackageItem::class, 'patient_package_item_id');
    }
}