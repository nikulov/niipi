<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Services\ContentRenderer;

final class ContentController extends Controller
{
    public function page(ContentRenderer $renderer, ?string $slug = null)
    {
        $slug = $this->normalizePageSlug($slug);
        
        $model = Page::query()->where('slug', $slug)->firstOrFail();
        
        return view('layout.page', [
            'page' => $model,
            'renderer' => $renderer,
        ]);
    }
    
    public function post(ContentRenderer $renderer, string $slug)
    {
        $model = Post::query()->where('slug', $slug)->firstOrFail();
        
        return view('layout.page', [
            'page' => $model,
            'renderer' => $renderer,
        ]);
    }
    
    private function normalizePageSlug(?string $slug): string
    {
        if ($slug === null || $slug === '/' || $slug === 'home') {
            return 'home';
        }
        
        return ltrim($slug, '/');
    }
}
