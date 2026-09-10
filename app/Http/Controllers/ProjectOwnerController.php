<?php

namespace App\Http\Controllers;

use App\Models\ProjectOwner;
use App\Models\User;
use App\Services\ProjectOwnerService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ProjectOwnerController extends Controller
{
    public function __construct(
        protected ProjectOwnerService $projectOwnerService
    ) {}

    public function index(Request $request)
    {
        $projectOwners = $this->projectOwnerService->getFilteredProjectOwners($request->query());

        $userVersion = Cache::get('users_cache_version', 1);
        $users = Cache::remember("all_user_u{$userVersion}", 1800, function () {
            return User::orderBy('name')->get();
        });

        return Inertia::render('ProjectOwner/Index', [
            'projectOwners' => $projectOwners,
            'users' => $users
        ]); 
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:project_owners,name',
        ]);

        $projectOwner = $this->projectOwnerService->storeProjectOwner($validated);

        return redirect(route('projectOwner.list', absolute: false))->with('success', 'The project owner ' . $projectOwner->name . ' has been successfully created!');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:project_owners,name,' . $id,
        ]);

        $projectOwner = $this->projectOwnerService->updateProjectOwner($validated, $id);

        return redirect(route('projectOwner.list', absolute: false))->with('success', 'The project owner ' . $projectOwner->name . ' has been successfully updated!');
    }

    public function changeIsActive(ProjectOwner $projectOwner): RedirectResponse
    {
        $this->projectOwnerService->activeProjectOwner($projectOwner);

        return back()->with('success', 'Status has been successfully updated!');
    }
}