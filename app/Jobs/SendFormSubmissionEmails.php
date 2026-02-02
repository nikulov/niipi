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
    ) {
    }
    
    public function handle(FormEmailTemplateRenderer $renderer): void
    {
        $submission = FormSubmission::query()
            ->with(['form', 'form.fields', 'files'])
            ->findOrFail($this->submissionId);
        
        try {
            $form = $submission->form;
            
            // --- ADMIN ---
            $adminEmail = $form?->recipient_admin_email;
            
            if (
                $form?->send_admin_mail === true
                && is_string($adminEmail) && $adminEmail !== ''
            ) {
                $adminSubject = is_string($form->admin_mail_subject ?? null) ? trim($form->admin_mail_subject) : '';
                $adminBodyMd = is_string($form->admin_mail_body_md ?? null) ? trim($form->admin_mail_body_md) : '';
                
                if ($adminSubject !== '' && $adminBodyMd !== '') {
                    $subject = $renderer->renderSubject($submission, $adminSubject);
                    $html = $renderer->renderBodyHtml($submission, $adminBodyMd);
                    $text = $renderer->renderBodyText($submission, $adminBodyMd);
                    
                    Mail::to($adminEmail)->send(new TemplatedFormSubmissionMail($subject, $html, $text));
                } else {
                    Mail::to($adminEmail)->send(new AdminFormSubmissionMail($submission));
                }
            }
            
            // --- USER ---
            $data = is_array($submission->data) ? $submission->data : [];
            $userEmail = $data['email'] ?? $data['user_email'] ?? null;
            
            if ($form?->send_user_mail === true && is_string($userEmail) && $userEmail !== '') {
                $userSubject = is_string($form->user_mail_subject ?? null) ? trim($form->user_mail_subject) : '';
                $userBodyMd = is_string($form->user_mail_body_md ?? null) ? trim($form->user_mail_body_md) : '';
                
                if ($userSubject !== '' && $userBodyMd !== '') {
                    $subject = $renderer->renderSubject($submission, $userSubject);
                    $html = $renderer->renderBodyHtml($submission, $userBodyMd);
                    $text = $renderer->renderBodyText($submission, $userBodyMd);
                    
                    $formAttachments = $this->buildFormUserAttachments(
                        is_array($form?->user_mail_attachments) ? $form->user_mail_attachments : []
                    );
                    
                    Mail::to($userEmail)->send(
                        new TemplatedFormSubmissionMail($subject, $html, $text, $formAttachments)
                    );
                } else {
                    Mail::to($userEmail)->send(new UserFormSubmissionMail($submission));
                }
            }
            
            $submission->update([
                'status' => FormSubmissionStatus::Sent,
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            $submission->update([
                'status' => FormSubmissionStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    private function buildFormUserAttachments(array $paths, string $disk = 'public'): array
    {
        return collect($paths)
            ->filter(fn ($p) => is_string($p) && trim($p) !== '')
            ->filter(fn (string $path) => Storage::disk($disk)->exists($path))
            ->map(fn (string $path) => Attachment::fromStorageDisk($disk, $path)->as(basename($path)))
            ->values()
            ->all();
    }
}