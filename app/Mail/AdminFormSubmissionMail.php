<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AdminFormSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public function __construct(
        public readonly FormSubmission $submission,
    ) {}
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('panel.submission') . ': ' . ($this->submission->form?->name ?? ''),
        );
    }
    
    public function content(): Content
    {
        return new Content(
            view: 'emails.form-submission-admin',
        );
    }
}
