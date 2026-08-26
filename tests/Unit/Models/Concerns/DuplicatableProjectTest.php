<?php

namespace Tests\Unit\Models\Concerns;

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicatableProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_appends_kopiya_suffix_and_resets_status(): void
    {
        $project = Project::create([
            'title' => 'Проект',
            'description' => 'desc',
            'slug' => 'project-1',
            'status' => ProjectStatus::Published->value,
            'published_at' => now(),
        ]);

        $copy = $project->duplicate();

        $this->assertSame('Проект (копия)', $copy->title);
        $this->assertSame('project-1-copy', $copy->slug);
        $this->assertSame(ProjectStatus::Draft, $copy->status);
        $this->assertNull($copy->published_at);
    }

    public function test_duplicate_copies_categories_pivot(): void
    {
        $project = Project::create([
            'title' => 'Проект',
            'description' => 'desc',
            'slug' => 'project-cat',
            'status' => ProjectStatus::Draft->value,
        ]);
        $cat = Category::create([
            'name' => 'Cat',
            'slug' => 'proj-cat',
            'status' => 'active',
            'type' => 'projects',
        ]);
        $project->categories()->attach($cat->id);

        $copy = $project->duplicate();

        $this->assertSame([$cat->id], $copy->categories()->pluck('categories.id')->all());
    }

    public function test_repeated_duplicate_increments_counter(): void
    {
        $project = Project::create([
            'title' => 'X',
            'description' => 'desc',
            'slug' => 'x-proj',
            'status' => ProjectStatus::Draft->value,
        ]);

        $project->duplicate();
        $project->duplicate();
        $third = $project->duplicate();

        $this->assertSame('X (копия 3)', $third->title);
        $this->assertSame('x-proj-copy-3', $third->slug);
    }
}
