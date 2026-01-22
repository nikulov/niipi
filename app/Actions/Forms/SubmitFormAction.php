<?php

namespace App\Actions\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\Forms\FormRulesBuilder;
use App\Services\Forms\SubmissionCreator;
use App\Services\Forms\SubmissionDataNormalizer;
use App\Services\Forms\SubmissionFilesStorer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SubmitFormAction
{
    public function __construct(
        private readonly FormRulesBuilder $rulesBuilder,
        private readonly SubmissionDataNormalizer $normalizer,
        private readonly SubmissionCreator $creator,
        private readonly SubmissionFilesStorer $filesStorer,
    ) {}
    
    public function handle(Form $form, array $data, array $uploads, ?string $ip, ?string $userAgent): FormSubmission
    {
        $rules = $this->rulesBuilder->build($form);
        
        $validated = validator(
            ['data' => $data, 'uploads' => $uploads],
            $rules
        )->validate();
        
        $normalizedData = $this->normalizer->normalize($form, $validated);
        
        return DB::transaction(function () use ($form, $normalizedData, $validated, $ip, $userAgent) {
            $submission = $this->creator->create($form, $normalizedData, $ip, $userAgent);
            
            $uploads = $validated['uploads'] ?? [];
            
            $this->filesStorer->store($form, $submission, $uploads);
            
            return $submission;
        });
    }
}