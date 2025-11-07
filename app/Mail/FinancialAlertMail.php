<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Company;

class FinancialAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $company;
    public $alertType;
    public $alertContent;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Company $company, string $alertType, array $alertContent)
    {
        $this->user = $user;
        $this->company = $company;
        $this->alertType = $alertType;
        $this->alertContent = $alertContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->alertType) {
            'new_data' => "New Financial Data - {$this->company->name}",
            'ratio_change' => "Significant Changes Detected - {$this->company->name}",
            default => "Financial Alert - {$this->company->name}"
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.financial-alert',
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
