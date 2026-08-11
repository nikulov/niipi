<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => redirect('/admin/login'))->name('login');

Route::get('/', [ContentController::class, 'page'])->name('home');
Route::get('/news/{slug}', [ContentController::class, 'post'])->name('news.show');
Route::get('/projects/{slug}', [ContentController::class, 'project'])->name('projects.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Live preview of the email template. Kept commented on purpose: uncomment
// while editing emails/email-template.blade.php, comment back afterwards.
// if (app()->isLocal()) {
//     Route::get('/_preview/email', fn () => view('emails.email-template', [
//         'body' => <<<'HTML'
//             <h2 style="margin: 10px; text-align: center; font-size: 20px;">Уважаемый Александр Александрович</h2>
//             <p>Спасибо за обращение, наша команда уже приняла его в обработку и ответит Вам в ближайшее время.</p>
//             <p>Если Вы хотите что-то добавить или изменить, просто ответьте на это письмо.</p>
//             <p>С уважением, коллектив<br>Института градостроительства Московской области.</p>
//             HTML,
//     ]))->name('preview.email');
// }

Route::get('/{slug}', [ContentController::class, 'page'])
    ->where('slug', '^(?!admin|api|login|register).+')
    ->name('page.index');
