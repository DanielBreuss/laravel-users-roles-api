<?php

namespace App\Models;

use App\Observers\GeneralObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[ObservedBy([GeneralObserver::class])]
class Role extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get all Roles from cache and if cache is empty get it from database and put it to the cache.
     */
    public static function getAllUsingCache(string $cacheKey, int $minutes)
    {
        return
            Cache::tags('role')->remember($cacheKey, now()->addMinutes($minutes), function () {
                return self::paginate(10);
            });
    }

    /**
     * Get role by id from cache and if cache is empty get it from database and put it to the cache.
     * @param int $id
     * @return User
     */
    public static function getRoleByIdUsingCache(int $id, int $minutes): Role
    {
        return
            Cache::tags('role')->remember('role_{$id}', now()->addMinutes($minutes), function () use ($id) {
                return self::findOrFail($id);
            });
    }
}
