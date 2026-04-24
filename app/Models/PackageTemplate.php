<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageTemplate extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'total_price',
        'active',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackageTemplateItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function patientPackages(): HasMany
    {
        return $this->hasMany(PatientPackage::class);
    }


public function cloneToPatient(\App\Models\Patient $patient, ?int $createdBy = null, array $overrides = []): \App\Models\PatientPackage
{
    $package = \App\Models\PatientPackage::create([
        'branch_id' => $patient->branch_id,
        'patient_id' => $patient->id,
        'package_template_id' => $this->id,
        'created_by' => $createdBy,
        'name' => $overrides['name'] ?? $this->name,
        'package_total' => $overrides['package_total'] ?? $this->total_price,
        'status' => $overrides['status'] ?? 'active',
        'started_on' => $overrides['started_on'] ?? now()->toDateString(),
        'notes' => $overrides['notes'] ?? null,
    ]);

    foreach ($this->items()->orderBy('sort_order')->get() as $index => $item) {
        $package->items()->create([
            'treatment_id' => $item->treatment_id,
            'sessions_included' => $item->sessions_included,
            'sort_order' => $index,
        ]);
    }

    return $package;
}


}