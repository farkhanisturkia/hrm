<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Exports\SkillMultiSheetExport;

use App\Models\Skill;

class SkillService
{
    public function getFilteredSkills(array $filters)
    {
        $userId = $filters['user_id'] ?? null;
        $auth = Auth::user();
        $targetUserId = ($userId && in_array($auth->role, ['manager', 'communicator'])) ? $userId : $auth->id;

        $skillVersion = Cache::get('skills_cache_version', 1);
        $cacheKey = "all_skill_s{$skillVersion}_uID{$targetUserId}";
        return Cache::remember($cacheKey, 1800, function () use ($targetUserId) {
            return Skill::where('user_id', $targetUserId)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function storeSkill(array $data): Skill
    {
        $auth = Auth::user();
        $skill = $auth->skills()->create([
            'skill' => $data['skill'],
        ]);

        $this->createLog("[CREATE] {$auth->name}'s skill {$skill->skill}");

        return $skill;
    }

    public function deleteSkill(Skill $skill): void
    {
        $auth = Auth::user();
        $skillName = $skill->skill;

        $skill->delete();

        $this->createLog("[DELETE] {$auth->name}'s skill {$skillName}");
    }

    public function exportSkills(?string $userId): array
    {
        $filename = 'skills.xlsx';

        if ($userId) {
            $user = User::select('id', 'name')->find($userId);
            if ($user) {
                $sanitizedName = preg_replace('/[^A-Za-z0-9\-]/', '_', $user->name);
                $filename = "{$sanitizedName}_skills.xlsx";
            }
        }

        $this->createLog("[EXPORT] skills");

        return [
            'export'   => new SkillMultiSheetExport($userId),
            'filename' => $filename,
        ];
    }

    public function createLog(string $description): void
    {
        Auth::user()->logs()->create([
            'target'        => 'skill',
            'description'   => $description,
        ]);
    }
}