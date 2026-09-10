<?php

namespace App\Observers;

use App\Models\Skill;
use Illuminate\Support\Facades\Cache;

class SkillObserver
{
    private function clearSkillCaches(): void
    {
        Cache::add('skills_cache_version', 1);
        Cache::increment('skills_cache_version');
    }

    public function saved(Skill $skill): void { $this->clearSkillCaches(); }
    public function deleted(Skill $skill): void { $this->clearSkillCaches(); }
}