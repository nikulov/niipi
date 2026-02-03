<?php

namespace Tests\Unit\Mail;

use App\Mail\TemplatedFormSubmissionMail;
use Illuminate\Mail\Attachment;
use Tests\TestCase;

class TemplatedFormSubmissionMailTest extends TestCase
{
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
