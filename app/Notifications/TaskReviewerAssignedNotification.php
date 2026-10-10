<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReviewerAssignedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $submittedBy;

    public function __construct(Task $task, $submittedBy)
    {
        $this->task = $task;
        $this->submittedBy = $submittedBy;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Assigned to review the task',
            'message' => $this->submittedBy->name . ' assigning you to review the task: "' . $this->task->issue . '"',
            'task_id' => $this->task->id,
            'url' => route('task.show', $this->task->id),
            'created_at' => now()->format('d M Y, H:i'),
        ];
    }
}