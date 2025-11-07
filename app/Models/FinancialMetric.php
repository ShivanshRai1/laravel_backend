<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialMetric extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'type',
        'unit',
        'formula',
        'category',
        'is_custom',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_custom' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all financial ratios for this metric
     */
    public function ratios(): HasMany
    {
        return $this->hasMany(FinancialRatio::class, 'metric_id');
    }

    /**
     * Scope to get only active metrics
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only custom metrics
     */
    public function scopeCustom($query)
    {
        return $query->where('is_custom', true);
    }

    /**
     * Scope to get only standard metrics
     */
    public function scopeStandard($query)
    {
        return $query->where('is_custom', false);
    }
}
