<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    
    public function index(?string $slug = null)
    {
        if ($slug === null || $slug === '/' || $slug === 'home') {
            $slug = 'home';
        }

        $slug = ltrim($slug, '/');
        $page = Page::query()->where('slug', $slug)->firstOrFail();

        return view('layout.page', compact('page'));
    }
    
    public function news()
    {
        return view('layout.news');
    }
    
}
