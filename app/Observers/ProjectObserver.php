<?php

namespace App\Observers;

use App\Enums\ProjectStatus;
use App\Models\Project;

class ProjectObserver
{
    public function saving(Project $project): void
    {
        if (
            $project->status === ProjectStatus::Published &&
            $project->published_at === null
        ) {
            $project->published_at = now();
        }
    }
}
