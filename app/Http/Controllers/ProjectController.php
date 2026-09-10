<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectOwner;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function index(Request $request)
    {
        $projects = $this->projectService->getFilteredProjects($request->query());
                
        $projectOwnerVersion = Cache::get('project_owner_cache_version', 1);
        $projectOwners = Cache::remember("all_project_owner_po{$projectOwnerVersion}", 1800, function () {
            return ProjectOwner::where('isActive', true)->get();
        });

        $userVersion = Cache::get('users_cache_version', 1);
        $users = Cache::remember("all_user_u{$userVersion}", 1800, function () {
            return User::orderBy('name')->get();
        });
 
        return Inertia::render('Project/Index', [
            'projects' => $projects,
            'projectOwners' => $projectOwners,
            'users' => $users
        ]); 
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255|unique:projects,name',
            'project_owner_id' => 'required|exists:project_owners,id'
        ]);

        $project = $this->projectService->storeProject($validated);

        return redirect()->route('project.list', array_filter(['project_owner_id' => $request->query('project_owner_id')]))
            ->with('success', 'Project ' . $project->name . ' was successfully created!');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:projects,name,' . $id,
            'project_owner_id' => 'required|exists:project_owners,id'
        ]);

        $project = $this->projectService->updateProject($validated, $id);

        return redirect()->route('project.list', array_filter(['project_owner_id' => $request->query('project_owner_id')]))
            ->with('success', 'Project ' . $project->name . ' has been successfully updated!');
    }
    public function changeIsActive(Project $project): RedirectResponse
    {
        try {
            $this->projectService->activeProject($project);
            return back()->with('success', 'Status has been successfully updated!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessage = $e->getMessage(); 
            if (isset($e->errors()['project_owner'][0])) {
                $errorMessage = $e->errors()['project_owner'][0];
            }

            return back()->with('error', $errorMessage);
        }
    }
}