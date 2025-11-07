<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialRatio extends Model
{
    protected $fillable = [
        'company_id',
        'metric_id',
        'quarter',
        'value',
        'is_manual',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'is_manual' => 'boolean',
        'company_id' => 'integer',
        'metric_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get the company this ratio belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the metric definition
     */
    public function metric(): BelongsTo
    {
        return $this->belongsTo(FinancialMetric::class, 'metric_id');
    }

    /**
     * Get the user who created this ratio
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this ratio
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get ratios for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get ratios for a specific quarter
     */
    public function scopeForQuarter($query, $quarter)
    {
        return $query->where('quarter', $quarter);
    }

    /**
     * Scope to get only manual entries
     */
    public function scopeManual($query)
    {
        return $query->where('is_manual', true);
    }
}
