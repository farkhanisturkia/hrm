<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;
use App\Models\TaskReviewer;

class TaskReviewCompletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $reviewer;

    public function __construct(Task $task, TaskReviewer $reviewer)
    {
        $this->task = $task;
        $this->reviewer = $reviewer;
    }

    public function build()
    {
        $subject = sprintf('[KNDI] Review completed: %s', $this->task->issue);
        return $this->subject($subject)->view('task_review_completed');
    }
}
