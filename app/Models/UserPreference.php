<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'kpi_preferences',
        'dashboard_settings'
    ];

    protected $casts = [
        'kpi_preferences' => 'array',
        'dashboard_settings' => 'array'
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default KPI preferences
     */
    public static function getDefaultKpiPreferences(): array
    {
        return [
            'revenue' => true,
            'profit' => true,
            'market_cap' => true,
            'pe_ratio' => true,
            'dividend' => true,
            'volume' => true,
            'high_52w' => true,
            'low_52w' => true,
            'beta' => true,
            'eps' => true
        ];
    }
}
