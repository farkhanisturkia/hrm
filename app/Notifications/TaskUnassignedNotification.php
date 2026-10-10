<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskUnassignedNotification extends Notification
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title'      => 'Unassigned from Task',
            'message'    => 'You have been unassigned from task: "' . $this->task->issue . '"',
            'task_id'    => $this->task->id,
            'url'        => route('task.show', $this->task->id),
            'created_at' => now()->format('d M Y, H:i'),
        ];
    }
}