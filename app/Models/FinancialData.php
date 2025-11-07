<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialData extends Model
{
    protected $fillable = [
        'company_id',
        // Financial statement data
        'revenue',
        'profit',
        'expenses',
        'assets',
        'liabilities',
        'period',
        'year',
        // Stock price data
        'date',
        'open_price',
        'high_price',
        'low_price',
        'close_price',
        'volume',
        'price_change_percent'
    ];

    protected $casts = [
        // Financial statement data
        'revenue' => 'decimal:2',
        'profit' => 'decimal:2',
        'expenses' => 'decimal:2',
        'assets' => 'decimal:2',
        'liabilities' => 'decimal:2',
        // Stock price data
        'open_price' => 'decimal:2',
        'high_price' => 'decimal:2',
        'low_price' => 'decimal:2',
        'close_price' => 'decimal:2',
        'price_change_percent' => 'decimal:2',
        'volume' => 'integer',
        'date' => 'date',
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
