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
     * Kirim notifikasi saat ada perubahan penugasan (PM, Engineer, Designer, Communicator).
     */
    public function sendAssignmentNotification(Task $task, array $oldAssignments, array $newAssignments): void
    {
        $oldUserIds = collect($oldAssignments)->flatten()->filter()->unique();
        $newUserIds = collect($newAssignments)->flatten()->filter()->unique();

        // 1. User baru yang ditambahkan ke task
        $assignedUserIds = $newUserIds->diff($oldUserIds);

        // 2. User lama yang dihapus dari task
        $unassignedUserIds = $oldUserIds->diff($newUserIds);

        // Kirim notifikasi untuk user baru yang di-assign
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
     * Kirim notifikasi saat reviewer baru ditambahkan.
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
     * Kirim notifikasi saat reviewer dihapus dari task.
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
     * Kirim notifikasi komentar baru di task.
     */
    public function sentCommentNotification(Task $task, User $sender, PullRequest $pullRequest): void
    {
        // Ambil semua pengguna yang terlibat di task (kecuali pembuat komentar)
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
     * Kirim notifikasi balasan (reply) komentar.
     */
    public function sendReplyNotification(Task $task, User $sender, Reply $reply, int $parentCommentOwnerId): void
    {
        // Notifikasi diberikan terutama ke pemilik komentar utama jika bukan dia yang membalas
        if ($parentCommentOwnerId !== $sender->id) {
            $parentOwner = User::find($parentCommentOwnerId);
            if ($parentOwner) {
                $parentOwner->notify(new TaskReplyNotification($task, $sender, $reply));
            }
        }
    }

    /**
     * Kirim notifikasi saat reviewer menyelesaikan review.
     */
    public function sendReviewCompletedNotification(Task $task, TaskReviewer $taskReviewer): void
    {
        // Beritahukan pembuat task atau PM bahwa review sudah selesai
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