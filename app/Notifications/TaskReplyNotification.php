<?php

namespace App\Notifications;

use App\Models\Reply;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReplyNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $sender;
    protected $reply;

    public function __construct(Task $task, User $sender, Reply $reply)
    {
        $this->task = $task;
        $this->sender = $sender;
        $this->reply = $reply;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Reply on Comment',
            'message' => $this->sender->name . ' replied to your comment on task: "' . $this->task->issue . '"',
            'task_id' => $this->task->id,
            'reply_id' => $this->reply->id,
            'url' => route('task.show', $this->task->id),
            'created_at' => now()->format('d M Y, H:i'),
        ];
    }
}