<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyDigestSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day_of_week',
        'preferred_time',
        'enabled',
        'last_sent_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'last_sent_at' => 'datetime',
        'preferred_time' => 'datetime:H:i:s',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
