<?php

namespace Tests\Feature\Livewire;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Enums\ProjectStatus;
use App\Livewire\Components\ProjectsFull;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectsFullTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_category_resets_invalid_slug(): void
    {
        $category = Category::create([
            'name' => 'Projects',
            'slug' => 'projects',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Projects->value,
        ]);

        $project = Project::create([
            'title' => 'Project A',
            'description' => 'Desc',
            'slug' => 'project-a',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $project->categories()->attach($category->id);

        Livewire::test(ProjectsFull::class)
            ->call('setCategory', 'missing')
            ->assertSet('category', null);
    }
}
