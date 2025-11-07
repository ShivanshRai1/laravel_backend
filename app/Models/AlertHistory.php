<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertHistory extends Model
{
    use HasFactory;

    protected $table = 'alert_history';

    protected $fillable = [
        'user_id',
        'email_alert_preference_id',
        'company_id',
        'alert_type',
        'alert_content',
        'sent',
        'sent_at',
    ];

    protected $casts = [
        'sent' => 'boolean',
        'sent_at' => 'datetime',
        'alert_content' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emailAlertPreference(): BelongsTo
    {
        return $this->belongsTo(EmailAlertPreference::class);
    }
}
