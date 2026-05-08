<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskUnassignmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $role;

    public function __construct(Task $task, $role = 'Member')
    {
        $this->task = $task;
        $this->role = $role;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[KNDI] Update: Kamu dihapus dari task {$this->task->issue}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task_unassignment',
        );
    }
}