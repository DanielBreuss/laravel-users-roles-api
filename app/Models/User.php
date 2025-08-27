<?php

namespace App\Models;

use App\Observers\GeneralObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy([GeneralObserver::class])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    protected static function booted()
    {
        static::deleting(function ($user) {
            $user->tokens()->delete();
        });
    }

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

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Role-check for auth purpose
     * @param string $role
     * @return bool
     */

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Get all Users from cache and if cache is empty get it from database and put it to the cache.
     */
    public static function getAllUsingCache($cacheKey, $minutes)
    {
        return
            Cache::tags('user')->remember($cacheKey, now()->addMinutes($minutes), function () {
            return self::with('roles')->paginate(10);
        });
    }

    /**
     * Get user by id from cache and if cache is empty get it from database and put it to the cache.
     * @param int $id
     * @return User
     */
    public static function getUserByIdUsingCache(int $id): User
    {
     return
         Cache::tags('user')->remember('user_{$id}', now()->addMinutes(10), function () use ($id) {
         return self::findOrFail($id)->load('roles');
     });
    }
}
