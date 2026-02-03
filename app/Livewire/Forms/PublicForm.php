<?php

namespace App\Livewire\Forms;

use App\Actions\Forms\SubmitFormAction;
use App\Models\Form;
use App\Presenters\Forms\PublicFormPresenter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

final class PublicForm extends Component
{
    use WithFileUploads;
    
    public Form $form;
    public array $viewData = [];
    public array $data = [];
    public array $uploads = [];
    public bool $submitted = false;
    public ?string $componentKey = null;
    public string $website = '';
    
    public function mount(int $formId, ?string $componentKey = null): void
    {
        $this->componentKey = $componentKey ?: ('form:' . $formId);
        
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
        foreach ($this->viewData['fields'] as $field) {
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
                
                if (is_string($default) && $default !== '') {
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
        
        try {
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
            
        } catch (ValidationException $e) {
            throw $e;
        }
    }
    
    private function normalizeUploads(array $uploads): array
    {
        foreach ($this->viewData['fields'] as $field) {
            if (($field['type'] ?? null) !== 'file') {
                continue;
            }
            
            $name = $field['name'] ?? null;
            if (!is_string($name) || $name === '') {
                continue;
            }
            
            $cfg = is_array($field['file'] ?? null) ? $field['file'] : [];
            $multiple = (bool) ($cfg['multiple'] ?? false);
            
            if (!array_key_exists($name, $uploads)) {
                $uploads[$name] = $multiple ? [] : null;
                continue;
            }
            
            if ($multiple) {
                $value = $uploads[$name];
                
                // Livewire may pass single file even with multiple=true
                if ($value && !is_array($value)) {
                    $uploads[$name] = [$value];
                }
            }
        }
        
        return $uploads;
    }
    
    public function render()
    {
        return view('livewire.forms.public-form');
    }
}
