<?php
namespace App\View\Composers;

use App\Models\Footer;
use Illuminate\View\View;

final class FooterComposer
{
    public function compose(View $view): void
    {
        $view->with('footer', Footer::cachedData());
    }
}