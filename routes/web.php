<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => redirect('/admin/login'))->name('login');

Route::get('/', [ContentController::class, 'page'])->name('home');
Route::get('/news/{slug}', [ContentController::class, 'post'])->name('news.show');
Route::get('/projects/{slug}', [ContentController::class, 'project'])->name('projects.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/{slug}', [ContentController::class, 'page'])
    ->where('slug', '^(?!admin|api|login|register).+')
    ->name('page.index');
