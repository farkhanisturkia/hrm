<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $projectsVersion = Cache::get('projects_cache_version', 1);
        $tasksVersion    = Cache::get('tasks_cache_version', 1);
        $usersVersion    = Cache::get('users_cache_version', 1);

        $projectsKey = "all_project_p{$projectsVersion}_t{$tasksVersion}";
        $projects = Cache::remember($projectsKey, 1800, function () {
            return Project::with(['tasks' => fn($q) => $q
                ->where('isActive', true)
                ->with('project')
                ->orderByRaw('ISNULL(due_date), due_date ASC')
            ])
            ->where('isActive', true)
            ->withCount(['tasks as active_tasks_count' => fn($q) => $q->where('isActive', true)])
            ->orderByDesc('active_tasks_count')
            ->get();
        });

        $usersKey = "all_user_u{$usersVersion}_t{$tasksVersion}";
        $users = Cache::remember($usersKey, 1800, function () {
            $activeTasks = Task::where('isActive', true)
                ->select(['id', 'programmer', 'designer', 'communicator'])
                ->get();

            $users = User::where('role', '!=', 'manager')
                ->orderBy('name')
                ->get();

            return $users->map(function ($user) use ($activeTasks) {
                $user->total_tasks = $activeTasks->filter(function ($task) use ($user) {
                    $programmers   = is_array($task->programmer) ? $task->programmer : json_decode($task->programmer ?? '[]', true);
                    $designers     = is_array($task->designer) ? $task->designer : json_decode($task->designer ?? '[]', true);
                    $communicators = is_array($task->communicator) ? $task->communicator : json_decode($task->communicator ?? '[]', true);

                    $programmers   = is_array($programmers) ? $programmers : [];
                    $designers     = is_array($designers) ? $designers : [];
                    $communicators = is_array($communicators) ? $communicators : [];

                    return in_array($user->id, $programmers) ||
                           in_array($user->id, $designers) ||
                           in_array($user->id, $communicators);
                })->count();

                return $user;
            })
            ->sortByDesc('total_tasks')
            ->values();
        });

        $tasksKey = "all_task_t{$tasksVersion}_p{$projectsVersion}_u{$usersVersion}";
        $tasks = Cache::remember($tasksKey, 1800, function () {
            return Task::where('isActive', true)
                ->with('project')
                ->orderByRaw('ISNULL(due_date), due_date ASC')
                ->get();
        });

        return Inertia::render('Dashboard', [
            'projects' => $projects,
            'users'  => $users,
            'tasks'    => $tasks,
        ]);
    }
}