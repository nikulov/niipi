<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use App\Models\Post;

final class CategoryListRenderer implements BlockRenderer
{
    public static function key(): string { return 'category-list'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        
        if ( ! $model instanceof Post) {
            return  '';
        }
        
        $categories = [
           'categories' => $model->categories()
               ->orderBy('name')
               ->pluck('name')
               ->toArray()
        ];
        
        $categories = implode(', ', $categories['categories']);
        
        return view('components.sections.category-list', compact('categories'))->render();
    }
    
}