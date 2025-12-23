<?php

namespace App\Observers;

use App\Enums\PageStatus;
use App\Models\Page;

class PageObserver
{
    public function saving(Page $page): void
    {
        if (
            $page->status === PageStatus::Published &&
            $page->published_at === null
        ) {
            $page->published_at = now();
        }
    }
}
