<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\User;
use App\Services\SkillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class SkillController extends Controller
{
    public function __construct(
        protected SkillService $skillService
    ) {}

    public function index(Request $request)
    {
        $skills = $this->skillService->getFilteredSkills($request->query());

        $userVersion = Cache::get('users_cache_version', 1);
        $users = Cache::remember("all_user_u{$userVersion}", 1800, function () {
            return User::orderBy('name')->get();
        });

        return Inertia::render('User/Skill', [
            'skills' => $skills,
            'users'  => $users
        ]);   
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'skill' => 'required|string|max:255',
        ]);

        $skill = $this->skillService->storeSkill($validated);

        return back()->with('success', 'The skill ' . $skill->skill . ' has been successfully added!');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skillName = $skill->skill;

        $this->skillService->deleteSkill($skill);

        return back()->with('warning', 'The ' . $skillName . ' skill was successfully deleted!');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $userId = $request->query('user_id');

        $exportData = $this->skillService->exportSkills($userId);

        return Excel::download(
            $exportData['export'], 
            $exportData['filename']
        );
    }
}