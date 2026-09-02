<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'branch_id',
        'product',
        'presentation',
        'entry_date',
        'expiration_date',
        'segment',
        'quantity',
        'unit',
        'minimum_stock',
        'notes',
        'active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'expiration_date' => 'date',
        'quantity' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'active' => 'boolean',
    ];

    protected $appends = [
        'is_expired',
        'is_low_stock',
        'expires_soon',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiration_date
            ? $this->expiration_date->isPast()
            : false;
    }

    public function getExpiresSoonAttribute(): bool
    {
        return $this->expiration_date
            ? now()->startOfDay()->lte($this->expiration_date)
                && $this->expiration_date->lte(now()->addDays(30)->endOfDay())
            : false;
    }

    public function getIsLowStockAttribute(): bool
    {
        if ($this->minimum_stock === null) {
            return false;
        }

        return (float) $this->quantity <= (float) $this->minimum_stock;
    }
}