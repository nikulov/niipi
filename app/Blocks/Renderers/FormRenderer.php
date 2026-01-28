<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use Livewire\Livewire;

final class FormRenderer implements BlockRenderer
{
    public static function key(): string
    {
        return 'form';
    }
    
    public static function version(): string
    {
        return '1';
    }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        $formId = (int) ($data['form_id'] ?? 0);
        $layout = (string) ($data['layout'] ?? 'inline');
        
        if ($formId <= 0) {
            return '';
        }
        
        $wireKey = sprintf(
            'block:%s:%s:%d',
            self::key(),
            $model->getRenderCacheId(),
            $index
        );
        
        $mounted = Livewire::mount(
            'forms.public-form',
            [
                'formId' => $formId,
                'layout' => $layout,
                'componentKey' => $wireKey,
            ],
            $wireKey
        );
        
        if (is_string($mounted)) {
            return $mounted;
        }
        
        return method_exists($mounted, 'html') ? $mounted->html() : (string) $mounted;
    }
}