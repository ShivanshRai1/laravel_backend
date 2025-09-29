<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialData extends Model
{
    protected $fillable = [
        'company_id',
        'revenue',
        'profit',
        'expenses',
        'assets',
        'liabilities',
        'period',
        'year'
    ];

    protected $casts = [
        'revenue' => 'decimal:2',
        'profit' => 'decimal:2',
        'expenses' => 'decimal:2',
        'assets' => 'decimal:2',
        'liabilities' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the company that owns this financial data
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
