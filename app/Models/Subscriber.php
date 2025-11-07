<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    protected $fillable = [
        'email',
        'name',
        'status',
        'verification_token',
        'verified_at',
        'unsubscribed_at'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (isset($model->name)) {
                $model->name = strtoupper($model->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    public function generateVerificationToken(): string
    {
        $this->verification_token = Str::random(32);
        $this->save();
        return $this->verification_token;
    }

    public function verify(): void
    {
        $this->verified_at = now();
        $this->verification_token = null;
        $this->save();
    }

    public function unsubscribe(): void
    {
        $this->status = 'unsubscribed';
        $this->unsubscribed_at = now();
        $this->save();
    }
}
