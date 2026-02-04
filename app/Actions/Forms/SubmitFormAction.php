<?php

namespace App\Actions\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\Forms\FormRulesBuilder;
use App\Services\Forms\FormValidationAttributesBuilder;
use App\Services\Forms\SubmissionCreator;
use App\Services\Forms\SubmissionDataNormalizer;
use App\Services\Forms\SubmissionFilesStorer;
use Illuminate\Support\Facades\DB;
use App\Enums\FormSubmissionStatus;
use App\Jobs\SendFormSubmissionEmails;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class SubmitFormAction
{
    public function __construct(
        private readonly FormRulesBuilder $rulesBuilder,
        private readonly FormValidationAttributesBuilder $attributesBuilder,
        private readonly SubmissionDataNormalizer $normalizer,
        private readonly SubmissionCreator $creator,
        private readonly SubmissionFilesStorer $filesStorer,
    ) {}
    
    public function handle(Form $form, array $data, array $uploads, ?string $ip, ?string $userAgent): FormSubmission
    {
        $key = sprintf(
            'forms:%d:%s',
            $form->id,
            $ip ?: 'no-ip'
        );
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'form' => __('panel.too_many_attempts'),
            ]);
        }
        
        RateLimiter::hit($key, 300);
        
        [$rules, $messages] = $this->rulesBuilder->build($form);
        
        $attributes = $this->attributesBuilder->build($form);
        
        $validated = validator(
            ['data' => $data, 'uploads' => $uploads],
            $rules,
            $messages,
            $attributes
        )->validate();
        
        $normalizedData = $this->normalizer->normalize($form, $validated);
        
        $submission = DB::transaction(function () use ($form, $normalizedData, $validated, $ip, $userAgent) {
            $submission = $this->creator->create($form, $normalizedData, $ip, $userAgent);
            
            $uploads = $validated['uploads'] ?? [];
            
            $this->filesStorer->store($form, $submission, $uploads);
            
            return $submission;
        });
        
        $submission->update([
            'status' => FormSubmissionStatus::Processing,
        ]);
        
        SendFormSubmissionEmails::dispatch($submission->id);
        
        return $submission;
    }
}
