<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class TaskObserver
{
    private function clearTaskCaches(): void
    {
        Cache::add('tasks_cache_version', 1);
        Cache::increment('tasks_cache_version');
    }

    public function saved(Task $task): void { $this->clearTaskCaches(); }
    public function deleted(Task $task): void { $this->clearTaskCaches(); }
}