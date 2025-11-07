<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadedFinancialData extends Model
{
    protected $table = 'uploaded_financial_data';

    protected $fillable = [
        'company_id',
        'batch_id',
        'Metrics',
        'Company',
        'Currency',
        'CY_2025_Q1',
        'CY_2024_Q4',
        'CY_2024_Q3',
        'CY_2024_Q2',
        'CY_2024_Q1',
        'CY_2023_Q4',
        'CY_2023_Q3',
        'CY_2023_Q2',
        'CY_2023_Q1',
        'CY_2022_Q4',
        'CY_2022_Q3',
        'CY_2022_Q2',
        'original_filename',
        'uploaded_by',
        'uploaded_at',
        'is_manual',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'is_manual' => 'boolean',
    ];

    /**
     * Get the company this data belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who uploaded this data
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope to get data for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get data for a specific batch
     */
    public function scopeForBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope to get only uploaded (not manual) data
     */
    public function scopeUploaded($query)
    {
        return $query->where('is_manual', false);
    }

    /**
     * Scope to get only manual entries
     */
    public function scopeManual($query)
    {
        return $query->where('is_manual', true);
    }
}

