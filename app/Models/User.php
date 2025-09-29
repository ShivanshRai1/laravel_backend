<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Valid user roles
     */
    public const ROLES = ['admin', 'editor', 'user'];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Set default role if not specified
            if (empty($user->role)) {
                $user->role = 'user';
            }
            
            // Validate role
            if (!in_array($user->role, self::ROLES)) {
                throw new \InvalidArgumentException('Invalid role specified');
            }
        });

        static::updating(function ($user) {
            // Validate role on update
            if (!in_array($user->role, self::ROLES)) {
                throw new \InvalidArgumentException('Invalid role specified');
            }
        });
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is an editor
     */
    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    /**
     * Check if user has admin or editor privileges
     */
    public function canManageContent(): bool
    {
        return in_array($this->role, ['admin', 'editor']);
    }

    /**
     * Relationship: User's watchlist items
     */
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Relationship: User's blog posts
     */
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }
}
