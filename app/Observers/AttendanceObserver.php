<?php

namespace App\Observers;

use App\Models\Attendance;
use Illuminate\Support\Facades\Cache;

class AttendanceObserver
{
    private function clearAttendanceCaches(): void
    {
        Cache::add('attendances_cache_version', 1);
        Cache::increment('attendances_cache_version');
    }

    public function saved(Attendance $attendance): void { $this->clearAttendanceCaches(); }
    public function deleted(Attendance $attendance): void { $this->clearAttendanceCaches(); }
}