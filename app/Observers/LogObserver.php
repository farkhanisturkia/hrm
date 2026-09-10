<?php

namespace App\Observers;

use App\Models\Log;
use Illuminate\Support\Facades\Cache;

class LogObserver
{
    private function clearLogCaches(): void
    {
        Cache::add('logs_cache_version', 1);
        Cache::increment('logs_cache_version');
    }

    public function saved(Log $log): void { $this->clearLogCaches(); }
    public function deleted(Log $log): void { $this->clearLogCaches(); }
}