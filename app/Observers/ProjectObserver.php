<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class ProjectObserver
{
    private function clearProjectCaches(): void
    {
        Cache::add('projects_cache_version', 1);
        Cache::increment('projects_cache_version');

        Cache::add('tasks_cache_version', 1);
        Cache::increment('tasks_cache_version');
    }

    public function saved(Project $project): void { $this->clearProjectCaches(); }
    public function deleted(Project $project): void { $this->clearProjectCaches(); }
}