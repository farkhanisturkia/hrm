<?php

namespace App\Notifications;

use App\Models\PullRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCommentNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $sender;
    protected $pullRequest;

    public function __construct(Task $task, User $sender, PullRequest $pullRequest)
    {
        $this->task = $task;
        $this->sender = $sender;
        $this->pullRequest = $pullRequest;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Comment on Task',
            'message' => $this->sender->name . ' commented on task: "' . $this->task->issue . '"',
            'task_id' => $this->task->id,
            'comment_id' => $this->pullRequest->id,
            'url' => route('task.show', $this->task->id),
            'created_at' => now()->format('d M Y, H:i'),
        ];
    }
}