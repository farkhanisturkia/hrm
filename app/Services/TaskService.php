<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\Task;
use App\Models\User;
use App\Models\Reply;
use App\Models\Project;
use App\Models\PullRequest;
use App\Models\ProjectOwner;
use App\Models\TaskReviewer;
use App\Services\TaskNotificationService;

class TaskService
{
    public function __construct(
        protected TaskNotificationService $notificationService
    ) {}

    public function getFormData(): array
    {
        $userVersion = Cache::get('users_cache_version', 1);
        $cacheKey = "all_task_form_data_u{$userVersion}";

        return Cache::remember($cacheKey, 1800, function () {
            $allUsers = User::select('id', 'name', 'email', 'role')->get();

            return [
                'userData' => [
                    'users'        => $allUsers,
                    'creator'      => $allUsers->whereIn('role', ['manager', 'leader', 'communicator'])->values(),
                    'communicator' => $allUsers->where('role', 'communicator')->values(),
                    'programmer'   => $allUsers->whereIn('role', ['engineer', 'leader'])->values(),
                    'designer'     => $allUsers->where('role', 'designer')->values(),
                ],
            ];
        });
    }

    public function getFilteredTasks(array $filters): LengthAwarePaginator
    {
        $user       = Auth::user();
        $page       = $filters['page'] ?? 1;
        $search     = $filters['search'] ?? '';
        $projectId  = $filters['project_id'] ?? null;
        $creatorId  = $filters['creator_id'] ?? null;
        $assignId   = $filters['assign_id'] ?? null;

        $taskVersion = Cache::get('tasks_cache_version', 1);
        $projectVersion = Cache::get('projects_cache_version', 1);

        $projectKey = $projectId ? $projectId : 'all';
        $creatorKey = $creatorId ? $creatorId : 'all';
        $assignKey = $assignId ? $assignId : 'all';
        $cacheKey = "all_task_t{$taskVersion}_p{$projectVersion}_u{$user->id}_pk{$projectKey}_ck{$creatorKey}_ak{$assignKey}_s" . md5($search) . "_pg{$page}";
        return Cache::remember($cacheKey, 1800, function () use ($filters, $user) {
            $query = Task::query()->with('project');

            if (!empty($filters['project_id'])) {
                $query->where('project_id', $filters['project_id']);
            }
            if (!empty($filters['creator_id'])) {
                $query->where('creator', $filters['creator_id']);
            }
            if (!empty($filters['assign_id'])) {
                $assignId = (int) $filters['assign_id'];

                $query->where(function ($q) use ($assignId) {
                    $q->where('pm', $assignId)
                    ->orWhereJsonContains('communicator', $assignId)
                    ->orWhereJsonContains('programmer', $assignId)
                    ->orWhereJsonContains('designer', $assignId);
                });
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('issue', 'LIKE', "%{$search}%")
                    ->orWhere('ticket_link', 'LIKE', "%{$search}%");
                });
            }

            if (in_array($user->role, ['leader', 'engineer', 'designer'])) {
                $query->where('isActive', true);
            }

            if ($user->role === 'engineer') {
                $query->where(function ($q) use ($user) {
                    $q->where('pm', $user->id)
                    ->orwhereJsonContains('programmer', $user->id)
                    ->orWhereJsonContains('reviewer', $user->id);
                });
            } elseif ($user->role === 'designer') {
                $query->whereJsonContains('designer', $user->id);
            }

            return $query
                ->orderByRaw('ISNULL(due_date), due_date ASC')
                ->paginate(10)
                ->withQueryString();
        });
    }

    public function store(array $data): Task
    {
        $project = Project::findOrFail($data['project_id']);

        $task = $project->tasks()->create([
            'issue' => $data['issue'],
            'ticket_link' => $data['ticket_link'],
            'related_links' => !empty($data['related_links']) ? $data['related_links'] : null,
            'description' => $data['description'] ?? null,
            'start_date' => Carbon::parse($data['start_date']),
            'due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            'creator' => Auth::id(),
            'updater' => Auth::id(),
        ]);

        $this->createLog("[CREATE] {$task->issue}!");
        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->load('project');
        $oldProjectName = $task->project->name ?? 'None';
        $oldIssue = $task->issue;

        $newProject = Project::findOrFail($data['project_id']);

        $changes = [];
        if ($task->project_id !== $newProject->id) {
            $changes[] = "project from '{$oldProjectName}' to '{$newProject->name}'";
        }
        if ($task->issue !== $data['issue']) {
            $changes[] = "issue from '{$task->issue}' to '{$data['issue']}'";
        }
        $newTicketLink = $data['ticket_link'] ?? null;
        if ($task->ticket_link !== $newTicketLink) {
            $oldLink = $task->ticket_link ?? 'None';
            $newLink = $newTicketLink ?? 'None';
            $changes[] = "ticket_link from '{$oldLink}' to '{$newLink}'";
        }
        $newRelatedLinks = !empty($data['related_links']) ? $data['related_links'] : null;
        if ($task->related_links !== $newRelatedLinks) {
            $oldRel = !empty($task->related_links) ? json_encode($task->related_links) : 'None';
            $newRel = !empty($newRelatedLinks) ? json_encode($newRelatedLinks) : 'None';
            $changes[] = "related_links from '{$oldRel}' to '{$newRel}'";
        }
        $newDescription = $data['description'] ?? null;
        if ($task->description !== $newDescription) {
            $oldDesc = $task->description ?? 'None';
            $newDesc = $newDescription ?? 'None';
            $changes[] = "description from '{$oldDesc}' to '{$newDesc}'";
        }
        $newStartDate = Carbon::parse($data['start_date'])->format('Y-m-d');
        $oldStartDate = $task->start_date ? Carbon::parse($task->start_date)->format('Y-m-d') : null;
        if ($oldStartDate !== $newStartDate) {
            $changes[] = "start_date from '{$oldStartDate}' to '{$newStartDate}'";
        }
        $newDueDate = !empty($data['due_date']) ? Carbon::parse($data['due_date'])->format('Y-m-d') : null;
        $oldDueDate = $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : null;
        if ($oldDueDate !== $newDueDate) {
            $oldDue = $oldDueDate ?? 'None';
            $newDue = $newDueDate ?? 'None';
            $changes[] = "due_date from '{$oldDue}' to '{$newDue}'";
        }

        $task->update([
            'project_id'    => $newProject->id,
            'issue'         => $data['issue'],
            'ticket_link'   => $newTicketLink,
            'related_links' => $newRelatedLinks,
            'description'   => $newDescription,
            'start_date'    => $newStartDate,
            'due_date'      => $newDueDate,
            'updater'       => Auth::id(),
        ]);

        if (!empty($changes)) {
            $detailLog = implode(', ', $changes);
            $this->createLog("[UPDATE] task {$oldIssue} ({$detailLog})");
        }

        return $task;
    }

    public function closeTask(Task $task): void
    {
        $task->update([
            'end_date' => Carbon::now(),
            'isActive' => false,
        ]);

        $this->createLog("[CLOSE] {$task->issue}");
    }

    public function activeTask(Task $task): void
    {
        $task->update([
            'end_date' => null,
            'isActive' => !$task->isActive,
            'updater'  => Auth::id(),
        ]);

        $status = $task->isActive ? 'ACTIVE' : 'INACTIVE';

        $this->createLog("[{$status}] {$task->name}");
    }

    public function assignTask(Task $task, array $data): void
    {
        $oldAssignments = [
            'Programmer' => $task->programmer ?? [],
            'Designer' => $task->designer ?? [],
            'Communicator' => $task->communicator ?? []
        ];

        $task->update([
            'pm' => $data['pm'] ?? null,
            'programmer'   => !empty($data['programmer']) ? $data['programmer'] : null,
            'designer'     => !empty($data['designer']) ? $data['designer'] : null,
            'communicator' => !empty($data['communicator']) ? $data['communicator'] : null,
        ]);

        $this->createLog("[ASSIGN] {$task->issue}");

        $newAssignments = [
            'Programmer' => $data['programmer'] ?? [],
            'Designer' => $data['designer'] ?? [],
            'Communicator' => $data['communicator'] ?? [],
        ];

        $this->notificationService->sendAssignmentNotification($task, $oldAssignments, $newAssignments);
    }

    public function assignReviewer(Task $task, array $newReviewerIds): void
    {
        $newReviewerIds = array_values(array_filter($newReviewerIds));
        $existingReviewerIds = $task->reviewers()->pluck('user_id')->toArray();

        $toAdd = array_diff($newReviewerIds, $existingReviewerIds);
        $toRemove = array_diff($existingReviewerIds, $newReviewerIds);

        foreach($toAdd as $uid) {
            TaskReviewer::updateOrCreate(
                ['task_id' => $task->id, 'user_id' => $uid],
                ['status' => 'pending'], 
            );
        }

        if (!empty($toRemove)) {
            TaskReviewer::where('task_id', $task->id)
                ->whereIn('user_id', $toRemove)
                ->update(['status' => 'removed']);
        }

        $task->update([
            'reviewer' => !empty($newReviewerIds) ? $newReviewerIds : null,
        ]);

        $this->createLog("[ASSIGN] {$task->issue}");

        if (!empty($toAdd)) {
            $this->notificationService->sendReviwerAssigned($task, array_values($toAdd));
        }

        if (!empty($toRemove)) {
            $this->notificationService->sendReviewerUnassigned($task, array_values($toRemove));
        }
    }

    public function addComment(Task $task, array $data): PullRequest
    {
        $pullRequest = $task->pullRequests()->create([
            'pr_links' => !empty($data['pr_links']) ? $data['pr_links'] : null,
            'comment' => $data['comment'],
            'from' => Auth::id(),
        ]);

        $this->createLog("[COMMENT] {$task->issue}");

        $this->notificationService->sentCommentNotification($task, Auth::user(), $pullRequest);
        return $pullRequest;
    }

    public function addReply(PullRequest $parentComment, array $data): ?Reply
    {
        $isDuplicate = $parentComment->replies()
            ->where('from', Auth::id())
            ->where('comment', $data['comment'])
            ->where('created_at', '>=', Carbon::now()->subSeconds(10))
            ->exists();

        if ($isDuplicate) {
            return null;
        }

        $reply = $parentComment->replies()->create([
            'pr_links' => !empty($data['pr_links']) ? $data['pr_links'] : null,
            'comment'  => $data['comment'],
            'from'     => Auth::id(),
        ]);

        $this->createLog("[REPLY] {$parentComment->task->issue}");

        $this->notificationService->sendReplyNotification(
            $parentComment->task,
            Auth::user(),
            $reply,
            $parentComment->from
        );

        return $reply;
    }

    public function markReviewCompleted(int $taskId, int $reviewerId): TaskReviewer
    {
        $taskReviewer = TaskReviewer::where('task_id', $taskId)
            ->where('user_id', $reviewerId)
            ->firstOrFail();

        $taskReviewer->update([
            'status' => 'done',
            'completed_at' => Carbon::now(),
        ]);

        $task = Task::findOrFail($taskId);
        $this->notificationService->sendReviewCompletedNotification($task, $taskReviewer);

        return $taskReviewer;
    }

    private function createLog(string $description): void
    {
        Auth::user()->logs()->create([
            'target'        => 'task',
            'description'   => $description,
        ]);
    }
}
