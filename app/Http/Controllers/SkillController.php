<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Exports\SkillMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Facades\Redis;
use Inertia\Inertia;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $userId = $request->query('user_id');
    $auth = Auth::user();
    $cacheKey = "skills_user_" . ($userId ?? $auth->id);

    $skills = \Cache::remember($cacheKey, 1800, function() use ($userId, $auth) {
        $skillsQuery = Skill::orderBy('created_at', 'desc');

        if ($userId && in_array($auth->role, ['other', 'co'])) {
            $skillsQuery->where('user_id', $userId);
        } else {
            $skillsQuery->where('user_id', $auth->id);
        }

        return $skillsQuery->get();
    });

    $users = \Cache::remember('all_users', 1800, function() {
        return User::all();
    });

    return Inertia::render('User/Skill', compact('skills', 'users'));   
}

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'skill' => 'required|string|max:255',
        ]);

        $user = User::where('id', Auth::id())->first();
        $skill = $user->skills()->create($validated);
        
        \Cache::flush();

        Auth::user()->logs()->create([
            'target' => 'skill',
            'description' => "[CREATE] skill {$skill->skill}",
        ]);

        return back()->with('success', "Skill '{$skill->skill}' berhasil ditambahkan!");
    }

    /**
     * Delete the user's skill.
     */
    public function destroy($id): RedirectResponse
    {
        $skill = Skill::findOrFail($id);
        $skill->delete();

        \Cache::flush();

        Auth::user()->logs()->create([
            'target' => 'skill',
            'description' => "[DELETE] skill {$skill->skill}",
        ]);

        return back()->with('warning', "Skill '{$skill->skill}' berhasil dihapus!");
    }

    /**
     * Export skill.
     */ 
    public function export(Request $request)
    {
        $query = $request->query();

        $filename = 'skills.xlsx';
        if (isset($query['user_id'])) {
            $user = User::where('id', $query['user_id'])->first();
            $filename =  $user->name . '_skills.xlsx';
        }

        Auth::user()->logs()->create([
            'target' => 'skill',
            'description' => "[EXPORT] skills",
        ]);

        return Excel::download(new SkillMultiSheetExport($query['user_id'] ?? null), $filename);
    }
}
