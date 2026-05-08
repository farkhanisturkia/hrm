<?php

namespace App\Http\Controllers;

use App\Models\ProjectOwner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;

class ProjectOwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $cacheKey = "project_owner_list_{$page}";

        $projectOwners = \Cache::remember($cacheKey, 1800, function() {
            return ProjectOwner::where('isDeleted', false)
                ->paginate(10)
                ->withQueryString();
        });

        $users = \Cache::remember('all_users_list', 1800, function() {
            return User::all();
        });

        return Inertia::render('ProjectOwner/Index', compact('projectOwners', 'users'));   
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $projectOwner = ProjectOwner::create([
            'name' => $request->name,
            'creator' => Auth::id(),
            'updater' => Auth::id()
        ]);

        \Cache::flush();

        Auth::user()->logs()->create([
            'target' => 'project owner',
            'description' => "[CREATE] project owner {$projectOwner->name}",
        ]);

        return redirect(route('projectOwner.list', absolute: false))->with('success', "Project owner '{$projectOwner->name}' berhasil dibuat!");
    }

    /**
     * Update the ProjectOwner's data.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $ProjectOwner = ProjectOwner::findOrFail($id);
        $ProjectOwner->update([
            'name' => $request->name,
            'updater' => Auth::id()
        ]);

        \Cache::flush();

        Auth::user()->logs()->create([
            'target' => 'project owner',
            'description' => "[UPDATE] project owner {$ProjectOwner->name}",
        ]);

        return redirect(route('projectOwner.list', absolute: false))->with('success', "Project owner '{$ProjectOwner->name}' berhasil diperbarui!");
    }

    /**
     * Delete the Project Owner's data.
     */
    public function destroy($id): RedirectResponse
    {
        $ProjectOwner = ProjectOwner::findOrFail($id);
        $ProjectOwner->update([
            'isDeleted' => true,
            'updater' => Auth::id()
        ]);

        \Cache::flush();

        Auth::user()->logs()->create([
            'target' => 'project owner',
            'description' => "[SOFT DELETE] project owner {$ProjectOwner->name}",
        ]);

        return redirect(route('projectOwner.list', absolute: false))->with('warning', "Project owner '{$ProjectOwner->name}' berhasil dihapus!");
    }
}
