<?php

namespace App\Providers;

use App\Models\Log;
use App\Models\Task;
use App\Models\User;
use App\Models\Skill;
use App\Models\Logtime;
use App\Models\Project;
use App\Models\ProjectOwner;
use App\Models\Attendance;
use App\Observers\LogObserver;
use App\Observers\TaskObserver;
use App\Observers\UserObserver;
use App\Observers\SkillObserver;
use App\Observers\LogtimeObserver;
use App\Observers\ProjectObserver;
use App\Observers\ProjectOwnerObserver;
use App\Observers\AttendanceObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Attendance::observe(AttendanceObserver::class);
        Log::observe(LogObserver::class);
        Logtime::observe(LogtimeObserver::class);
        Project::observe(ProjectObserver::class);
        ProjectOwner::observe(ProjectOwnerObserver::class);
        Skill::observe(SkillObserver::class);
        Task::observe(TaskObserver::class);
        User::observe(UserObserver::class);
    }
}
