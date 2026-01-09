<?php

namespace App\Http\Controllers;

use App\Contracts\HasMeta;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Services\ContentRenderer;

final class ContentController extends Controller
{
    public function page(ContentRenderer $renderer, ?string $slug = null)
    {
        $slug = $this->normalizePageSlug($slug);
        
        $model = Page::query()->where('slug', $slug)->firstOrFail();
        
        return view('layout.page', [
            'page' => $model,
            'meta' => $model instanceof HasMeta ? $model->meta() : [],
            'bgForMainSection' => $model->getBgForMainSection(),
            'renderer' => $renderer,
        ]);
    }
    
    public function post(ContentRenderer $renderer, string $slug)
    {
        $model = Post::query()->where('slug', $slug)->firstOrFail();
        
        return view('layout.page', [
            'page' => $model,
            'meta' => $model instanceof HasMeta ? $model->meta() : [],
            'bgForMainSection' => $model->getBgForMainSection(),
            'renderer' => $renderer,
        ]);
    }
    
    public function project(ContentRenderer $renderer, string $slug)
    {
        $model = Project::query()->where('slug', $slug)->firstOrFail();
        
        return view('layout.page', [
            'page' => $model,
            'meta' => $model instanceof HasMeta ? $model->meta() : [],
            'bgForMainSection' => $model->getBgForMainSection(),
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
