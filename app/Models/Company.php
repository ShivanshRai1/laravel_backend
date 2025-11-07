<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'sector',
        'industry',
        'description',
        'website',
        'logo_url',
        'market_cap',
        'employees',
        'country',
        'exchange'
    ];

    protected $casts = [
        'market_cap' => 'decimal:2',
        'employees' => 'integer',
    ];

    /**
     * Get financial data for this company
     */
    public function financialData(): HasMany
    {
        return $this->hasMany(FinancialData::class);
    }

    /**
     * Get watchlist entries for this company
     */
    public function watchlistEntries(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }
}