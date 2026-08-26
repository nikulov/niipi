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

    /**
     * The letter tells the client to just reply, but the sending mailbox is not
     * the one that gets read — so replies are steered by `mail.reply_to`.
     */
    public function test_reply_to_comes_from_the_mail_config(): void
    {
        config(['mail.reply_to' => ['address' => 'website@niipi.ru', 'name' => 'НИиПИ']]);
        Mail::forgetMailers();

        Mail::to('client@example.test')->send(
            new TemplatedFormSubmissionMail('Subject', '<p>Hello</p>')
        );

        $message = Mail::mailer()->getSymfonyTransport()->messages()->first()->getOriginalMessage();

        $this->assertSame(
            ['website@niipi.ru'],
            array_map(fn ($address) => $address->getAddress(), $message->getReplyTo())
        );
    }

    public function test_no_reply_to_is_added_when_the_address_is_not_configured(): void
    {
        config(['mail.reply_to' => ['address' => null, 'name' => null]]);
        Mail::forgetMailers();

        Mail::to('client@example.test')->send(
            new TemplatedFormSubmissionMail('Subject', '<p>Hello</p>')
        );

        $message = Mail::mailer()->getSymfonyTransport()->messages()->first()->getOriginalMessage();

        $this->assertSame([], $message->getReplyTo());
    }

    public function test_attachments_are_returned(): void
    {
        $attachments = [Attachment::fromData(fn () => 'x', 'file.txt')];
        $mail = new TemplatedFormSubmissionMail('Subject', '<p>Hello</p>', null, $attachments);

        $this->assertSame($attachments, $mail->attachments());
    }
}
