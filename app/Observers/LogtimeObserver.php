<?php

namespace App\Observers;

use App\Models\Logtime;
use Illuminate\Support\Facades\Cache;

class LogtimeObserver
{
    private function clearLogtimeCaches(): void
    {
        Cache::add('logtimes_cache_version', 1);
        Cache::increment('logtimes_cache_version');
    }

    public function saved(Logtime $logtime): void { $this->clearLogtimeCaches(); }
    public function deleted(Logtime $logtime): void { $this->clearLogtimeCaches(); }
}