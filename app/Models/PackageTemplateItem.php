<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageTemplateItem extends Model
{
    protected $fillable = [
        'package_template_id',
        'treatment_id',
        'sessions_included',
        'sort_order',
    ];

    protected $casts = [
        'sessions_included' => 'integer',
        'sort_order' => 'integer',
    ];

    public function packageTemplate(): BelongsTo
    {
        return $this->belongsTo(PackageTemplate::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }
}