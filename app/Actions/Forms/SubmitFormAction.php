<?php

namespace App\Actions\Forms;

use App\Enums\FormSubmissionStatus;
use App\Jobs\SendFormSubmissionEmails;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\Forms\FormRulesBuilder;
use App\Services\Forms\FormValidationAttributesBuilder;
use App\Services\Forms\SubmissionCreator;
use App\Services\Forms\SubmissionDataNormalizer;
use App\Services\Forms\SubmissionFilesStorer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use League\Flysystem\FilesystemException;
use Throwable;

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

        // `FilesystemAdapter::size()` is the only adapter method without a
        // try/catch: it throws even with `'throw' => false` on the disk, so the
        // `max:` rule turns a broken file into a 500. Catch the whole
        // FilesystemException family — this is a net under the filtering done in
        // PublicForm, there is no point narrowing it to a single class
        try {
            $validated = validator(
                ['data' => $data, 'uploads' => $uploads],
                $rules,
                $messages,
                $attributes
            )->validate();
        } catch (FilesystemException $e) {
            // if anything still got here, the logs have to show it — we should
            // not learn about it from a visitor complaint
            report($e);

            throw ValidationException::withMessages([
                'form' => __('panel.upload_broken'),
            ]);
        }

        $normalizedData = $this->normalizer->normalize($form, $validated);

        $stored = [];

        try {
            $submission = DB::transaction(function () use ($form, $normalizedData, $validated, $ip, $userAgent, &$stored) {
                $submission = $this->creator->create($form, $normalizedData, $ip, $userAgent);

                $uploads = $validated['uploads'] ?? [];

                $this->filesStorer->store($form, $submission, $uploads, $stored);

                $submission->update([
                    'status' => FormSubmissionStatus::Processing,
                ]);

                return $submission;
            });
        } catch (Throwable $e) {
            // строки FormSubmissionFile откатились вместе с транзакцией,
            // а файлы на диске — нет. rescue(): сбой уборки логируется, но не
            // подменяет собой исходную причину отката
            foreach ($stored as $file) {
                rescue(fn () => Storage::disk($file['disk'])->delete($file['path']));
            }

            throw $e;
        }

        SendFormSubmissionEmails::dispatch($submission->id);

        return $submission;
    }
}
