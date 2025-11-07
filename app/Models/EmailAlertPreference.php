<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAlertPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'alert_type',
        'threshold',
        'watched_ratios',
        'enabled',
    ];

    protected $casts = [
        'watched_ratios' => 'array',
        'enabled' => 'boolean',
        'threshold' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'symbol');
    }
}
