<?php

namespace App\Observers;

use App\Enums\ProjectStatus;
use App\Models\Project;

class ProjectObserver
{
    public function saving(Project $post): void
    {
        if (
            $post->status === ProjectStatus::Published &&
            $post->published_at === null
        ) {
            $post->published_at = now();
        }
    }
}
