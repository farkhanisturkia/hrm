<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\Project;
use App\Models\ProjectOwner;

class ProjectService
{
    public function getFilteredProjects(array $filters): LengthAwarePaginator
    {
        $page           = $filters['page'] ?? 1;
        $search         = $filters['search'] ?? '';
        $projectOwnerId = $filters['project_owner_id'] ?? null;

        $projectVersion      = Cache::get('projects_cache_version', 1);
        $projectOwnerVersion = Cache::get('project_owner_cache_version', 1);

        $ownerKey = $projectOwnerId ? $projectOwnerId : 'all';
        $cacheKey = "all_project_p{$projectVersion}_po{$projectOwnerVersion}_pok{$ownerKey}_s" . md5($search) . "_pg{$page}";
        return Cache::remember($cacheKey, 1800, function () use ($projectOwnerId, $search) {
            $query = Project::query()->with('projectOwner');

            $query->when($projectOwnerId, function ($q, $id) {
                $q->where('project_owner_id', $id);
            });

            if (!empty($search)) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            return $query
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        });
    }

    public function storeProject(array $data): Project
    {
        $userId = Auth::id();
        $projectOwner = ProjectOwner::findOrFail($data['project_owner_id']);

        $project = $projectOwner->projects()->create([
            'name'    => $data['name'],
            'creator' => $userId,
            'updater' => $userId
        ]);

        $this->createLog("[CREATE] {$project->name}");

        return $project;
    }

    public function updateProject(array $data, $id): Project
    {
        $userId = Auth::id();

        $project = Project::with('projectOwner')->findOrFail($id);
        $newOwner = ProjectOwner::findOrFail($data['project_owner_id']);

        $oldProjectName = $project->name;
        $changes = [];

        if ($project->name !== $data['name']) {
            $changes[] = "name from '{$oldProjectName}' to '{$data['name']}'";
        }

        if ($project->project_owner_id !== $newOwner->id) {
            $oldOwnerName = $project->projectOwner->name ?? 'None';
            $changes[] = "owner from '{$oldOwnerName}' to '{$newOwner->name}'";
        }

        $project->update([
            'name'             => $data['name'],
            'project_owner_id' => $newOwner->id,
            'updater'          => $userId
        ]);

        if (!empty($changes)) {
            $detailLog = implode(', ', $changes);
            $this->createLog("[UPDATE] project {$oldProjectName} ({$detailLog})");
        }

        return $project;
    }

    public function activeProject(Project $project): void
    {
        $newStatus = !$project->isActive;

        if ($newStatus && !$project->projectOwner->isActive) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'project_owner' => "This project cannot be activated because the project owner, '{$project->projectOwner->name}', is currently inactive."
            ]);
        }

        $project->update([
            'isActive' => $newStatus,
            'updater'  => Auth::id(),
        ]);

        $statusStr = $newStatus ? 'ACTIVE' : 'INACTIVE';
        $this->createLog("[{$statusStr}] {$project->name}");
    }

    public function createLog(string $description): void
    {
        Auth::user()->logs()->create([
            'target'        => 'project',
            'description'   => $description,
        ]);
    }
}