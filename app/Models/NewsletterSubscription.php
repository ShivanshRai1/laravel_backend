<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscribed',
        'frequency',
        'categories',
        'format',
        'enabled'
    ];

    protected $casts = [
        'subscribed' => 'boolean',
        'enabled' => 'boolean',
        'categories' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('enabled', true)->where('subscribed', true);
    }

    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    public function isSubscribed(): bool
    {
        return $this->subscribed && $this->enabled;
    }
}