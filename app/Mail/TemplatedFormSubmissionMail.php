<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TemplatedFormSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public function __construct(
        private readonly string $subjectLine,
        private readonly string $htmlBody,
        private readonly ?string $textBody = null,
        private readonly array $mailAttachments = [],
    ) {}
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }
    
    public function content(): Content
    {
        return new Content(
            text: $this->textBody !== null ? 'emails.plain-text' : null,
            with: $this->textBody !== null ? ['textBody' => $this->textBody] : [],
            htmlString: $this->htmlBody,
        );
    }
    
    public function attachments(): array
    {
        return $this->mailAttachments;
    }
}