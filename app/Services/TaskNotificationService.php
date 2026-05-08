<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Mail\CommentReplyNotification;
use App\Mail\TaskAssignmentNotification;
use App\Mail\TaskCommentNotification;
use App\Mail\TaskReviewCompletedNotification;
use App\Mail\TaskUnassignmentNotification;
use App\Models\PullRequest;
use App\Models\Reply;
use App\Models\Task;
use App\Models\TaskReviewer;
use App\Models\User;

class TaskNotificationService
{
    public function sendAssignmentNotification(Task $task, array $oldAssignments, array $newAssignments): void
    {
        foreach($newAssignments as $role => $newIds) {
            $oldIds = $oldAssignments[$role] ?? [];
            
            $addedIds = array_diff($newIds, $oldIds);
            $removeIds = array_diff($oldIds, $newIds);

            if (!empty($addedIds)) {
                $this->sendBulkEmail(
                    userIds: $addedIds,
                    mailable: fn(User $user) => new TaskAssignmentNotification($task, $role),
                );
            }

            if (!empty($removeIds)) {
                $this->sendBulkEmail(
                    userIds: $removeIds,
                    mailable: fn(User $Task) => new TaskUnassignmentNotification($task, $role),
                );
            }
        }
    }

    public function sendReviwerAssigned(Task $task, array $userIds): void
    {
        $this->sendBulkEmail(
            userIds: $userIds,
            mailable: fn(User $user) => new TaskAssignmentNotification($task, 'Reviewer'),
        );
    }

    public function sendReviewerUnassigned(Task $task, array $userIds): void
    {
        $this->sendBulkEmail(
            userIds: $userIds,
            mailable: fn(User $user) => new TaskUnassignmentNotification($task, 'Reviewer'),
        );
    }

    public function sentCommentNotification(Task $task, User $commenter, PullRequest $pullRequest): void
    {
        $recepientIds = collect([
            $task->creator,
            $task->pl,
            ...($task->programmer ?? []),
            ...($task->designer ?? []),
            ...($task->communicator ?? []),
            ...($task->reviewer ?? []),
        ])->filter()->unique()->reject(fn($id) => $id === $commenter->id);
    }

    public function sendReplyNotification(Task $task, User $replier, Reply $reply, int $targetUserId): void
    {
        if ($targetUserId === $replier->id) {
            return;
        }

        $targetUser = User::findOrFail($targetUserId);

        if ($targetUser?->email) {
            $this->sendMail(
                $targetUser,
                new CommentReplyNotification($task, $replier, $reply),
            );
        }
    }

    public function sendReviewCompletedNotification(Task $task, TaskReviewer $taskReviewer): void
    {
        $recepientIds = collect([
            $task->pl,
            $task->creator,
        ])->filter()->unique()->all();

        $this->sendBulkEmail(
            userIds: $recepientIds,
            mailable: fn(User $user) => new TaskReviewNotification($task, $taskReviewer),
        );
    }

    private function sendBulkEmail(array $userIds, callable $mailable): void
    {
        if (empty($userIds)) {
            return;
        }

        User::whereIn('id', $userIds)
            ->whereNotNull('email')
            ->get()
            ->each(fn(User $user) => $this->sendMail($user, $mailable($user)));
    }

    private function sendMail(User $user, object $mailable): void
    {
        try {
            Mail::to($user->email)->send($mailable);
        } catch (\Exception $err) {
            Log::error("Failed to send email to {$user->email}" . $err->getMessage());
        }
    }
}
