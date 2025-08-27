<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GeneralObserver
{
    public function created(Model $model): void
    {
        $this->clearCache($model);
    }

    public function updated(Model $model): void
    {
        $this->clearCache($model);
    }

    public function deleted(Model $model): void
    {
        $this->clearCache($model);
    }

    public function clearCache(Model $model): void
    {
        match (true) {
            $model instanceof User => $tag = 'user' ,
            $model instanceof Role => $tag = 'role' ,
        };

        Cache::tags($tag)->flush();

    }
}
