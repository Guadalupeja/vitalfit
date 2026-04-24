<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'branch_id',
        'patient_id',
        'patient_package_id',
        'amount',
        'method',
        'paid_at',
        'notes',
        'receipt_path',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function package()
    {
        return $this->belongsTo(\App\Models\PatientPackage::class, 'patient_package_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}