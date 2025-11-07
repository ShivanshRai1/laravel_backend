<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SavedComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'companies',
        'comparison_data'
    ];

    protected $casts = [
        'companies' => 'array',
        'comparison_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the saved comparison
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}