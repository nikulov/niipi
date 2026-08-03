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

    public function test_category_and_total_counts_ignore_future_publications(): void
    {
        $category = Category::create([
            'name' => 'Design',
            'slug' => 'design',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Projects->value,
        ]);

        $past = Project::create([
            'title' => 'Past',
            'description' => 'Desc',
            'slug' => 'past',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $future = Project::create([
            'title' => 'Future',
            'description' => 'Desc',
            'slug' => 'future',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->addDay(),
        ]);

        $past->categories()->attach($category->id);
        $future->categories()->attach($category->id);

        $items = Livewire::test(ProjectsFull::class)->viewData('categoryItems');

        $all = $items->firstWhere('slug', null);
        $design = $items->firstWhere('slug', 'design');

        $this->assertSame(1, $all['count']);
        $this->assertSame(1, $design['count']);
    }
}
