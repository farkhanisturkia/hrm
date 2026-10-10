<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskReviewer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReviewerCompletedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $taskReviewer;

    public function __construct(Task $task, TaskReviewer $taskReviewer)
    {
        $this->task = $task;
        $this->taskReviewer = $taskReviewer;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $reviewerName = $this->taskReviewer->user->name ?? 'A reviewer';

        return [
            'title' => 'Review Completed',
            'message' => $reviewerName . ' has completed reviewing task: "' . $this->task->issue . '"',
            'task_id' => $this->task->id,
            'reviewer_id' => $this->taskReviewer->user_id,
            'url' => route('task.show', $this->task->id),
            'created_at' => now()->format('d M Y, H:i'),
        ];
    }
}