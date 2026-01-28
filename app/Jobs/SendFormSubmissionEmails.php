<?php

namespace App\Jobs;

use App\Enums\FormSubmissionStatus;
use App\Mail\AdminFormSubmissionMail;
use App\Mail\UserFormSubmissionMail;
use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
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
    
    public function handle(): void
    {
        $submission = FormSubmission::query()
            ->with(['form', 'form.fields', 'files'])
            ->findOrFail($this->submissionId);
        
        try {
            $adminEmail = $submission->form?->recipient_admin_email;
            
            if (is_string($adminEmail) && $adminEmail !== '') {
                Mail::to($adminEmail)->send(new AdminFormSubmissionMail($submission));
            }
            
            $data = is_array($submission->data) ? $submission->data : [];
            $userEmail = $data['email'] ?? $data['user_email'] ?? null;
            
            if (is_string($userEmail) && $userEmail !== '') {
                Mail::to($userEmail)->send(new UserFormSubmissionMail($submission));
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
}