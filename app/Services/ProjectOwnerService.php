<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\ProjectOwner;
use App\Services\ProjectService;

class ProjectOwnerService
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function getFilteredProjectOwners(array $filters): LengthAwarePaginator
    {
        $page    = $filters['page'] ?? 1;
        $search  = $filters['search'] ?? '';

        $projectOwnerVersion = Cache::get('project_owner_cache_version', 1);

        $cacheKey = "all_project_owner_po{$projectOwnerVersion}_s" . md5($search) . "_pg{$page}";
        return Cache::remember($cacheKey, 1800, function () use ($search) {
            $query = ProjectOwner::query();

            if (!empty($search)) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            return $query
                ->paginate(10)
                ->withQueryString();
        });
    }

    public function storeProjectOwner(array $data): ProjectOwner
    {
        $userId = Auth::id();

        $projectOwner = ProjectOwner::create([
            'name'    => $data['name'],
            'creator' => $userId,
            'updater' => $userId,
        ]);

        $this->createLog("[CREATE] {$projectOwner->name}");

        return $projectOwner;
    }

    public function updateProjectOwner(array $data, $id): ProjectOwner
    {
        $userId = Auth::id();

        $projectOwner = ProjectOwner::findOrFail($id);
        $before = $projectOwner->name;

        $projectOwner->update([
            'name' => $data['name'],
            'updater' => $userId
        ]);

        $this->createLog("[UPDATE] {$before} to {$projectOwner->name}");

        return $projectOwner;
    }

    public function activeProjectOwner(ProjectOwner $projectOwner): void
    {
        $newStatus = !$projectOwner->isActive;
        $userId = Auth::id();

        $projectOwner->update([
            'isActive' => $newStatus,
            'updater'  => $userId,
        ]);

        $statusStr = $newStatus ? 'ACTIVE' : 'INACTIVE';

        $affectedProjects = $projectOwner->projects()->update([
            'isActive' => $newStatus,
            'updater'  => $userId,
        ]);

        if ($affectedProjects > 0) {
            $this->projectService->createLog("[{$statusStr}] all project under {$projectOwner->name}");
        }

        $this->createLog("[{$statusStr}] {$projectOwner->name}");
    }

    public function createLog(string $description): void
    {
        Auth::user()->logs()->create([
            'target'        => 'project owner',
            'description'   => $description,
        ]);
    }
}