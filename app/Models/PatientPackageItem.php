<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientPackageItem extends Model
{
    protected $fillable = [
        'patient_package_id',
        'treatment_id',
        'sessions_included',
        'sort_order',
    ];

    protected $appends = [
        'completed_sessions_count',
        'remaining_sessions',
    ];

    public function patientPackage()
    {
        return $this->belongsTo(PatientPackage::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_package_item_id');
    }

    public function getCompletedSessionsCountAttribute(): int
    {
        return $this->appointments()
            ->where('status', 'completed')
            ->count();
    }

    public function getRemainingSessionsAttribute(): int
    {
        return max(0, (int)$this->sessions_included - (int)$this->completed_sessions_count);
    }
}