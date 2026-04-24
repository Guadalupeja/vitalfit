<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientTreatment extends Model
{
    protected $fillable = [
        'branch_id',
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

    protected $appends = [
        'completed_sessions_count',
        'remaining_sessions',
        'is_available_for_booking',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'patient_treatment_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_treatment_id');
    }

    public function getCompletedSessionsCountAttribute(): int
    {
        return (int) $this->appointments()
            ->where('status', 'completed')
            ->count();
    }

    public function getRemainingSessionsAttribute(): int
    {
        return max(0, (int)$this->sessions_purchased - (int)$this->completed_sessions_count);
    }

    public function getIsAvailableForBookingAttribute(): bool
    {
        return $this->status === 'active' && $this->remaining_sessions > 0;
    }

    public function refreshStatusIfFinished(): void
    {
        if ($this->status === 'active' && $this->remaining_sessions <= 0) {
            $this->update([
                'status' => 'finished',
            ]);
        }
    }
}
