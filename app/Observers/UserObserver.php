<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    private function clearUserCaches(): void
    {
        Cache::add('users_cache_version', 1);
        Cache::increment('users_cache_version');

        Cache::add('tasks_cache_version', 1);
        Cache::increment('tasks_cache_version');
    }

    public function saved(User $user): void { $this->clearUserCaches(); }
    public function deleted(User $user): void { $this->clearUserCaches(); }
}