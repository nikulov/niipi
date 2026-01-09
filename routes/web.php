<?php

use App\Http\Controllers\ContentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ContentController::class, 'page'])->name('home');
Route::get('/news/{slug}', [ContentController::class, 'post'])->name('news.show');
Route::get('/projects/{slug}', [ContentController::class, 'project'])->name('projects.show');

Route::get('/{slug}', [ContentController::class, 'page'])
    ->where('slug', '^(?!admin|api|login|register).+')
    ->name('page.index');
