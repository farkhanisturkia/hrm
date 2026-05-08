<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentReplyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $replier;
    public $replyData; // Ini object Reply

    public function __construct(Task $task, User $replier, $replyData)
    {
        $this->task = $task;
        $this->replier = $replier;
        $this->replyData = $replyData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[KNDI] Balasan Baru dari {$this->replier->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comment_reply',
        );
    }
}