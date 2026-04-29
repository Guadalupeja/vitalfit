<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientPackage extends Model
{
    protected $fillable = [
        'branch_id',
        'patient_id',
        'package_template_id',
        'created_by',
        'name',
        'package_total',
        'status',
        'started_on',
        'ends_on',
        'notes',
    ];

    protected $casts = [
        'package_total' => 'decimal:2',
        'started_on' => 'date',
        'ends_on' => 'date',
    ];

    protected $appends = [
        'completed_items_count',
        'pending_items_count',
        'is_fully_completed',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PackageTemplate::class, 'package_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PatientPackageItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getCompletedItemsCountAttribute(): int
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get();

        return $items->filter(fn ($item) => $item->remaining_sessions <= 0)->count();
    }

    public function getPendingItemsCountAttribute(): int
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get();

        return $items->filter(fn ($item) => $item->remaining_sessions > 0)->count();
    }

    public function getIsFullyCompletedAttribute(): bool
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get();

        if ($items->isEmpty()) {
            return false;
        }

        return $items->every(fn ($item) => $item->remaining_sessions <= 0);
    }

    public function refreshStatusIfCompleted(): void
    {
        if ($this->status === 'active' && $this->is_fully_completed) {
            $this->update([
                'status' => 'finished',
            ]);
        }
    }

public function payments()
{
    return $this->hasMany(\App\Models\Payment::class, 'patient_package_id');
}

    }

