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
    
    public function mount(int $formId): void
    {
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
    }
    
    public function submit(SubmitFormAction $action): void
    {
        try {
            $action->handle(
                $this->form,
                $this->data,
                $this->uploads,
                request()->ip(),
                request()->userAgent(),
            );
            
            $this->submitted = true;
            
            $this->reset(['data', 'uploads']);
            
        } catch (ValidationException $e) {
            throw $e;
        }
    }
    
    public function render()
    {
        return view('livewire.forms.public-form');
    }
}