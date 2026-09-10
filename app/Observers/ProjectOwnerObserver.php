<?php

namespace App\Observers;

use App\Models\ProjectOwner;
use Illuminate\Support\Facades\Cache;

class ProjectOwnerObserver
{
    private function clearProjectOwnerCaches(): void
    {
        Cache::add('project_owner_cache_version', 1);
        Cache::increment('project_owner_cache_version');
    }

    public function saved(ProjectOwner $projectOwner): void { $this->clearProjectOwnerCaches(); }
    public function deleted(ProjectOwner $projectOwner): void { $this->clearProjectOwnerCaches(); }
}