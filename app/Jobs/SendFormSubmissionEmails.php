<?php

namespace App\Jobs;

use App\Enums\FormSubmissionStatus;
use App\Mail\AdminFormSubmissionMail;
use App\Mail\TemplatedFormSubmissionMail;
use App\Mail\UserFormSubmissionMail;
use App\Models\FormSubmission;
use App\Services\Forms\FormEmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Attachment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class SendFormSubmissionEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(
        public readonly int $submissionId,
    ) {}

    public function handle(FormEmailTemplateRenderer $renderer): void
    {
        $submission = FormSubmission::query()
            ->with(['form', 'form.fields', 'files'])
            ->findOrFail($this->submissionId);

        try {
            $form = $submission->form;

            // Letters that were expected but had nowhere to go. Silence here used
            // to leave the submission marked as delivered.
            $skipped = [];

            // The letter did go out, but not as configured — worth reporting,
            // not worth marking the submission as failed.
            $notes = [];

            // --- ADMIN ---
            $adminEmail = $form?->recipient_admin_email;

            if ($form?->send_admin_mail === true && (! is_string($adminEmail) || trim($adminEmail) === '')) {
                $skipped[] = __('panel.mail_skipped_no_admin_recipient');
            }

            if (
                $form?->send_admin_mail === true
                && is_string($adminEmail) && $adminEmail !== ''
            ) {
                $adminSubject = is_string($form->admin_mail_subject ?? null) ? trim($form->admin_mail_subject) : '';
                $adminBodyMd = is_string($form->admin_mail_body_md ?? null) ? trim($form->admin_mail_body_md) : '';

                if ($adminSubject !== '' && $adminBodyMd !== '') {
                    $subject = $renderer->renderSubject($submission, $adminSubject);
                    $html = $renderer->renderLetterHtml($submission, $adminBodyMd);
                    $text = $renderer->renderBodyText($submission, $adminBodyMd);

                    Mail::to($adminEmail)->send(new TemplatedFormSubmissionMail($subject, $html, $text));
                } else {
                    Mail::to($adminEmail)->send(new AdminFormSubmissionMail($submission));
                }
            }

            // --- USER ---
            $userEmail = $this->resolveUserEmail($submission);

            if ($form?->send_user_mail === true && $userEmail === null) {
                $skipped[] = __('panel.mail_skipped_no_user_email');
            }

            if ($form?->send_user_mail === true && $userEmail !== null) {
                $userSubject = is_string($form->user_mail_subject ?? null) ? trim($form->user_mail_subject) : '';
                $userBodyMd = is_string($form->user_mail_body_md ?? null) ? trim($form->user_mail_body_md) : '';

                if ($userSubject !== '' && $userBodyMd !== '') {
                    $subject = $renderer->renderSubject($submission, $userSubject);
                    $html = $renderer->renderLetterHtml($submission, $userBodyMd);
                    $text = $renderer->renderBodyText($submission, $userBodyMd);

                    [$formAttachments, $missingAttachments] = $this->buildFormUserAttachments(
                        is_array($form?->user_mail_attachments) ? $form->user_mail_attachments : []
                    );

                    if ($missingAttachments !== []) {
                        Log::warning('Form user mail attachments are missing', [
                            'submission_id' => $submission->id,
                            'form_id' => $submission->form_id,
                            'paths' => $missingAttachments,
                        ]);

                        $notes[] = __('panel.mail_attachments_missing', [
                            'files' => implode(', ', $missingAttachments),
                        ]);
                    }

                    Mail::to($userEmail)->send(
                        new TemplatedFormSubmissionMail($subject, $html, $text, $formAttachments)
                    );
                } else {
                    Mail::to($userEmail)->send(new UserFormSubmissionMail($submission));
                }
            }

            if ($skipped !== []) {
                Log::warning('Form submission emails were not sent', [
                    'submission_id' => $submission->id,
                    'form_id' => $submission->form_id,
                    'reasons' => $skipped,
                ]);
            }

            $messages = array_merge($skipped, $notes);

            $submission->update([
                // Notes do not fail the submission: the letter was delivered, and
                // `Failed` would invite a resend — that is a duplicate (bug #2).
                'status' => $skipped === [] ? FormSubmissionStatus::Sent : FormSubmissionStatus::Failed,
                'error_message' => $messages === [] ? null : implode(' ', $messages),
            ]);
        } catch (Throwable $e) {
            $submission->update([
                'status' => FormSubmissionStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Conventional keys first, then the first email field of the form: the field
     * may be named anything, and only its type says it holds an address.
     */
    private function resolveUserEmail(FormSubmission $submission): ?string
    {
        $data = is_array($submission->data) ? $submission->data : [];

        $emailField = $submission->form?->fields
            ->where('is_enabled', true)
            ->firstWhere('type', 'email');

        $keys = array_filter(['email', 'user_email', $emailField?->name]);

        foreach ($keys as $key) {
            $value = $data[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * @return array{0: list<Attachment>, 1: list<string>} attachments and the paths
     *                                                     that are configured but no longer on the disk
     */
    private function buildFormUserAttachments(array $paths, string $disk = 'public'): array
    {
        [$present, $missing] = collect($paths)
            ->filter(fn ($p) => is_string($p) && trim($p) !== '')
            ->values()
            ->partition(fn (string $path) => Storage::disk($disk)->exists($path));

        return [
            $present
                ->map(fn (string $path) => Attachment::fromStorageDisk($disk, $path)->as(basename($path)))
                ->values()
                ->all(),
            $missing->values()->all(),
        ];
    }
}
