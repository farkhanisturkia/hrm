<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = \Cache::remember('all_projects', 1800, function() {
            return Project::with(['tasks' => fn($q) => $q
                ->where('isActive', true)
                ->with('project')
                ->orderByRaw('ISNULL(due_date), due_date ASC')
            ])
            ->where('isDeleted', false)
            ->withCount(['tasks as active_tasks_count' => fn($q) => $q->where('isActive', true)])
            ->orderByDesc('active_tasks_count')
            ->get();
        });

        $members = \Cache::remember('all_members', 1800, function() {
            return User::get()
                ->where('role', '!=', 'other')
                ->map(function ($user) {
                    $user->total_tasks = Task::where('isActive', true)
                        ->where(fn($q) => $q
                            ->whereJsonContains('programmer', $user->id)
                            ->orWhereJsonContains('designer', $user->id)
                            ->orWhereJsonContains('communicator', $user->id)
                        )
                        ->count();
                    return $user;
                })
                ->sortByDesc('total_tasks')
                ->values();
        });

        $tasks = \Cache::remember('all_task', 1800, function() {
            return Task::where('isActive', true)
                ->with('project')
                ->orderByRaw('ISNULL(due_date), due_date ASC')
                ->get();
        });

        return Inertia::render('Dashboard', [
            'projects' => $projects,
            'members'  => $members,
            'tasks'    => $tasks,
        ]);
    }
}
