<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

use App\Models\ProjectOwner;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Reply;
use App\Models\PullRequest;
use App\Models\TaskReviewer;
use App\Services\TaskNotificationService;

class TaskService
{
    public function __construct(
        protected TaskNotificationService $notificationService
    ) {}

    public function getFormData(): array
    {
        return [
            'users'        => User::select('id', 'name', 'email', 'role')->get(),
            'communicator' => User::select('id', 'name')->where('role', 'co')->get(),
            'programmer'   => User::select('id', 'name')->whereIn('role', ['pg', 'pm'])->get(),
            'designer'     => User::select('id', 'name')->where('role', 'ds')->get(),
        ];
    }

    public function getFilteredTasks(array $filters): LengthAwarePaginator
    {
        $user  = Auth::user();
        $query = Task::query()->with('project');

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('issue', 'LIKE', "%{$search}%")
                  ->orWhere('ticket_link', 'LIKE', "%{$search}%");
            });
        }

        if (in_array($user->role, ['pm', 'pg', 'ds'])) {
            $query->where('isActive', true);
        }

        if ($user->role === 'pg') {
            $query->where(function ($q) use ($user) {
                $q->whereJsonContains('programmer', $user->id)
                  ->orWhereJsonContains('reviewer', $user->id);
            });
        } elseif ($user->role === 'ds') {
            $query->whereJsonContains('designer', $user->id);
        }

        return $query
            ->orderByRaw('ISNULL(due_date), due_date ASC')
            ->paginate(10)
            ->withQueryString();
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

        $this->createLog("[CREATE] task for {$task->issue}!");
        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $project = Project::findOrFail($data['project_id']);

        $task->update([
            'project_id' => $project->id,
            'issue' => $data['issue'],
            'ticket_link' => $data['ticket_link'],
            'related_links' => !empty($data['related_links']) ? $data['related_links'] : null,
            'description' => $data['description'] ?? null,
            'start_date' => Carbon::parse($data['start_date']),
            'due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            'updater' => Auth::id(),
        ]);

        $this->createLog("[UPDATE] task for task {$task->issue}");
        return $task;
    }

    public function destroy(Task $task): void
    {
        $this->createLog("[DELETE] task for {$task->issue}");
        $task->delete();
    }

    public function closeTask(Task $task): void
    {
        $task->update([
            'end_date' => Carbon::now(),
            'isActive' => false,
        ]);

        $this->createLog("[CLOSE] task for {$task->issue}");
    }

    public function activeTask(Task $task): void
    {
        $task->update([
            'end_date' => null,
            'isActive' => !$task->isActive,
            'updater'  => Auth::id(),
        ]);

        $this->createLog("[ACTIVE] task for {$task->issue}");
    }

    public function assignTask(Task $task, array $data): void
    {
        $oldAssignments = [
            'Programmer' => $task->programmer ?? [],
            'Designer' => $task->designer ?? [],
            'Communicator' => $task->communicator ?? []
        ];

        $task->update([
            'pl' => $data['pl'] ?? null,
            'programmer'   => !empty($data['programmer']) ? $data['programmer'] : null,
            'designer'     => !empty($data['designer']) ? $data['designer'] : null,
            'communicator' => !empty($data['communicator']) ? $data['communicator'] : null,
            'isAssign'     => true,
        ]);

        $this->createLog("[ASSIGN] task for {$task->issue}");
        

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
            'isAssign' => true,
        ]);

        $this->createLog("[ASSIGN] task for {$task->issue}");

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

        $this->createLog("[COMMENT] task for {$task->issue}");

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

        $this->createLog("[REPLY] task for {$parentComment->task->issue}");

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
