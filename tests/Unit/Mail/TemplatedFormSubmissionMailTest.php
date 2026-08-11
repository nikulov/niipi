<?php

namespace Tests\Unit\Mail;

use App\Mail\TemplatedFormSubmissionMail;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TemplatedFormSubmissionMailTest extends TestCase
{
    /**
     * The renderer no longer escapes the text part, so the view must print it
     * raw — `{{ }}` there used to turn `&` into `&amp;amp;` on the way out.
     */
    public function test_text_part_is_not_escaped_twice(): void
    {
        $mail = new TemplatedFormSubmissionMail(
            'Subject',
            '<p>Hello</p>',
            'Заявка от Иванов & Партнёры «Северный» <офис>'
        );

        Mail::to('client@example.test')->send($mail);

        $message = Mail::mailer()->getSymfonyTransport()->messages()->first()->getOriginalMessage();

        $this->assertSame(
            'Заявка от Иванов & Партнёры «Северный» <офис>',
            trim($message->getTextBody())
        );
    }

    public function test_envelope_and_content_with_text(): void
    {
        $mail = new TemplatedFormSubmissionMail('Subject', '<p>Hello</p>', 'Plain text');

        $this->assertSame('Subject', $mail->envelope()->subject);
        $this->assertSame('emails.plain-text', $mail->content()->text);
        $this->assertSame(['textBody' => 'Plain text'], $mail->content()->with);
        $this->assertSame('<p>Hello</p>', $mail->content()->htmlString);
    }

    public function test_attachments_are_returned(): void
    {
        $attachments = [Attachment::fromData(fn () => 'x', 'file.txt')];
        $mail = new TemplatedFormSubmissionMail('Subject', '<p>Hello</p>', null, $attachments);

        $this->assertSame($attachments, $mail->attachments());
    }
}
