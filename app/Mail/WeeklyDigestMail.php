<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $watchlistData;
    public $recentPosts;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $watchlistData, $recentPosts)
    {
        $this->user = $user;
        $this->watchlistData = $watchlistData;
        $this->recentPosts = $recentPosts;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Weekly Financial Dashboard Digest',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-digest',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
