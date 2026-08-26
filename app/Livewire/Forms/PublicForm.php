<?php

namespace App\Livewire\Forms;

use App\Actions\Forms\SubmitFormAction;
use App\Models\Form;
use App\Presenters\Forms\PublicFormPresenter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use League\Flysystem\FilesystemException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class PublicForm extends Component
{
    use WithFileUploads;

    // Всё, что компонент строит сам, закрыто от клиента: чексумма покрывает
    // снапшот, но не карту `updates`, и без #[Locked] эти свойства
    // переписываются запросом. Открытыми остаются только те, что на wire:model.
    #[Locked]
    public Form $form;

    #[Locked]
    public array $viewData = [];

    public array $data = [];

    public array $uploads = [];

    #[Locked]
    public bool $submitted = false;

    #[Locked]
    public ?string $componentKey = null;

    public string $website = '';

    public function mount(int $formId, ?string $componentKey = null): void
    {
        $this->componentKey = $componentKey ?: ('form:'.$formId);

        $this->form = Form::query()
            ->whereKey($formId)
            ->where('is_active', true)
            ->with([
                'fields' => fn ($q) => $q
                    ->where('is_enabled', true)
                    ->orderBy('sort'),
            ])
            ->firstOrFail();

        $this->viewData = app(PublicFormPresenter::class)->present($this->form);

        $this->applySelectAndRadioDefaults();
    }

    private function applySelectAndRadioDefaults(): void
    {
        foreach (($this->viewData['fields'] ?? []) as $field) {
            $type = $field['type'] ?? null;

            if (! in_array($type, ['select', 'radio'], true)) {
                continue;
            }

            $name = $field['name'] ?? null;
            if (! is_string($name) || $name === '') {
                continue;
            }

            if (! array_key_exists($name, $this->data) || $this->data[$name] === null || $this->data[$name] === '') {
                $default = $field['default'] ?? null;

                if (is_string($default)) {
                    $this->data[$name] = $default;
                }
            }
        }
    }

    public function submit(SubmitFormAction $action): void
    {
        if ($this->website !== '') {
            $this->submitted = true;
            $this->reset(['data', 'uploads', 'website']);

            return;
        }

        $uploads = $this->normalizeUploads($this->uploads);

        $action->handle(
            $this->form,
            $this->data,
            $uploads,
            request()->getClientIp(),
            request()->userAgent(),
        );

        $this->submitted = true;

        $this->reset(['data', 'uploads', 'website']);

        $this->dispatch('form-submitted', componentKey: $this->componentKey);
    }

    private function normalizeUploads(array $uploads): array
    {
        $uploads = $this->rejectMissingFiles($uploads);

        foreach (($this->viewData['fields'] ?? []) as $field) {
            if (($field['type'] ?? null) !== 'file') {
                continue;
            }

            $name = $field['name'] ?? null;
            if (! is_string($name) || $name === '') {
                continue;
            }

            $cfg = is_array($field['file'] ?? null) ? $field['file'] : [];
            $multiple = (bool) ($cfg['multiple'] ?? false);

            if (! array_key_exists($name, $uploads)) {
                $uploads[$name] = $multiple ? [] : null;

                continue;
            }

            if ($multiple) {
                $value = $uploads[$name];

                // Livewire may pass single file even with multiple=true
                if ($value && ! is_array($value)) {
                    $uploads[$name] = [$value];
                }
            }
        }

        return $uploads;
    }

    /**
     * Livewire takes the temporary file path from the client at face value.
     * A failed write answers `200 {"paths":[""]}`, and after hydration that
     * empty path becomes a dead `livewire-tmp/livewire-tmp` which the `max:`
     * rule turns into a 500 on `size()`. Drop such a file before validation —
     * and out of the state as well, otherwise it survives the retry and the
     * visitor stays stuck on the same error until the page is reloaded.
     *
     * Walks `$uploads` itself rather than `viewData['fields']`: with an empty
     * `viewData` the field loop is a no-op and the hole stays open.
     */
    private function rejectMissingFiles(array $uploads): array
    {
        $lost = [];

        foreach ($uploads as $name => $value) {
            if (is_array($value)) {
                $kept = [];

                foreach ($value as $file) {
                    if ($this->isMissingFile($file)) {
                        $lost[$name][] = $file->getFilename();

                        continue;
                    }

                    $kept[] = $file;
                }

                if (count($kept) !== count($value)) {
                    $uploads[$name] = array_values($kept);
                }

                continue;
            }

            if ($this->isMissingFile($value)) {
                $lost[$name][] = $value->getFilename();
                $uploads[$name] = null;
            }
        }

        if ($lost === []) {
            return $uploads;
        }

        $this->uploads = $uploads;

        // the visitor gets a field error, but a broken disk has to be visible
        // from the logs as well — nothing else on this path reports it
        Log::warning('public form dropped temporary files that no longer exist', [
            'form' => $this->form->id,
            'fields' => $lost,
        ]);

        // staying silent is not an option: for an optional field the submission
        // would go through without the attachment while the visitor believes
        // the file was attached
        throw ValidationException::withMessages(
            array_fill_keys(
                array_map(fn (string $name) => "uploads.{$name}", array_keys($lost)),
                __('panel.upload_lost'),
            )
        );
    }

    private function isMissingFile(mixed $file): bool
    {
        if (! $file instanceof TemporaryUploadedFile) {
            return false;
        }

        try {
            return ! $file->exists();
        } catch (FilesystemException) {
            // the disk could not answer — treat the file as dead: validation
            // would fail on it anyway, only with a 500
            return true;
        }
    }

    public function render()
    {
        return view('livewire.forms.public-form');
    }
}
