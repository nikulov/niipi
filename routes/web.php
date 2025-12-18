<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');

Route::get('/news-test', [PageController::class, 'news'])->name('page.news');



Route::get('/{slug}', [PageController::class,'index'])
    ->where('slug','^(?!admin|api|login|register).+')
    ->name('page.index');
