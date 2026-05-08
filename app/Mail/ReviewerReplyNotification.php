<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;
use App\Models\Reply;
use App\Models\User;

class ReviewerReplyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $reply;
    public $reviewer;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, Reply $reply, User $reviewer)
    {
        $this->task = $task;
        $this->reply = $reply;
        $this->reviewer = $reviewer;
    }

    public function build()
    {
        $subject = sprintf('[KNDI] Reviewer membalas: %s', $this->task->issue);
        return $this->subject($subject)->view('reviewer_reply');
    }
}
