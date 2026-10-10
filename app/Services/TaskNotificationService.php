<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\PullRequest;
use App\Models\Reply;
use App\Models\TaskReviewer;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskUnassignedNotification;
use App\Notifications\TaskReviewerAssignedNotification;
use App\Notifications\TaskReviewerUnassignedNotification;
use App\Notifications\TaskCommentNotification;
use App\Notifications\TaskReplyNotification;
use App\Notifications\TaskReviewerCompletedNotification;
use Illuminate\Support\Facades\Notification;

class TaskNotificationService
{
    /**
     * Send a notification when there is a change in assignments (PM, Engineer, Designer, Communicator).
     */
    public function sendAssignmentNotification(Task $task, array $oldAssignments, array $newAssignments): void
    {
        $oldUserIds = collect($oldAssignments)->flatten()->filter()->unique();
        $newUserIds = collect($newAssignments)->flatten()->filter()->unique();

        $assignedUserIds = $newUserIds->diff($oldUserIds);
        $unassignedUserIds = $oldUserIds->diff($newUserIds);

        if ($assignedUserIds->isNotEmpty()) {
            $assignedUsers = User::whereIn('id', $assignedUserIds)->get();
            Notification::send($assignedUsers, new TaskAssignedNotification($task));
        }

        // Kirim notifikasi untuk user yang di-unassign
        if ($unassignedUserIds->isNotEmpty()) {
            $unassignedUsers = User::whereIn('id', $unassignedUserIds)->get();
            Notification::send($unassignedUsers, new TaskUnassignedNotification($task));
        }
    }

    /**
     * Send a notification when a new reviewer is added.
     */
    public function sendReviewerAssigned(Task $task, array $newReviewerIds): void
    {
        if (empty($newReviewerIds)) {
            return;
        }

        $reviewers = User::whereIn('id', $newReviewerIds)->get();
        $submittedBy = auth()->user();

        Notification::send($reviewers, new TaskReviewerAssignedNotification($task, $submittedBy));
    }

    /**
     * Send a notification when a reviewer is removed from a task.
     */
    public function sendReviewerUnassigned(Task $task, array $removedReviewerIds): void
    {
        if (empty($removedReviewerIds)) {
            return;
        }

        $removedReviewers = User::whereIn('id', $removedReviewerIds)->get();

        Notification::send($removedReviewers, new TaskReviewerUnassignedNotification($task));
    }

    /**
     * Send a notification for new comments on a task.
     */
    public function sentCommentNotification(Task $task, User $sender, PullRequest $pullRequest): void
    {
        $userIdsToNotify = collect([
            $task->creator,
            $task->pm,
        ])
        ->merge($task->engineer ?? [])
        ->merge($task->designer ?? [])
        ->merge($task->communicator ?? [])
        ->filter()
        ->unique()
        ->reject(fn ($id) => $id == $sender->id);

        if ($userIdsToNotify->isNotEmpty()) {
            $users = User::whereIn('id', $userIdsToNotify)->get();
            Notification::send($users, new TaskCommentNotification($task, $sender, $pullRequest));
        }
    }

    /**
     * Send a reply notification for a comment.
     */
    public function sendReplyNotification(Task $task, User $sender, Reply $reply, int $parentCommentOwnerId): void
    {
        if ($parentCommentOwnerId !== $sender->id) {
            $parentOwner = User::find($parentCommentOwnerId);
            if ($parentOwner) {
                $parentOwner->notify(new TaskReplyNotification($task, $sender, $reply));
            }
        }
    }

    /**
     * Send a notification when a reviewer completes a review.
     */
    public function sendReviewCompletedNotification(Task $task, TaskReviewer $taskReviewer): void
    {
        $recipientIds = collect([$task->creator, $task->pm])
            ->filter()
            ->unique()
            ->reject(fn ($id) => $id == $taskReviewer->user_id);

        if ($recipientIds->isNotEmpty()) {
            $recipients = User::whereIn('id', $recipientIds)->get();
            Notification::send($recipients, new TaskReviewerCompletedNotification($task, $taskReviewer));
        }
    }
}