<?php

namespace Tests\Unit\Observers;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProjectObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_at_is_set_on_publish(): void
    {
        Carbon::setTestNow('2026-02-03 12:00:00');

        $project = Project::create([
            'title' => 'Project',
            'description' => 'Desc',
            'slug' => 'project',
            'status' => ProjectStatus::Published,
            'published_at' => null,
        ]);

        $this->assertNotNull($project->published_at);
        $this->assertSame('2026-02-03 12:00:00', $project->published_at->format('Y-m-d H:i:s'));
    }
}
