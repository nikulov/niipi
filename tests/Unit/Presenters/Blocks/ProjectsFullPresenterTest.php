<?php

namespace Tests\Unit\Presenters\Blocks;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use App\Presenters\Blocks\ProjectsFullPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsFullPresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_make_builds_card_data(): void
    {
        $category = Category::create([
            'name' => 'Projects',
            'slug' => 'projects',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Projects->value,
        ]);

        $project = Project::create([
            'title' => 'Project',
            'description' => 'Desc',
            'slug' => 'project-1',
            'thumbnail' => '/img.jpg',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $project->categories()->attach($category->id);
        $project->load('categories');

        $card = ProjectsFullPresenter::make($project);

        $this->assertSame('Project', $card['title']);
        $this->assertSame('Desc', $card['description']);
        $this->assertSame('projects/project-1', $card['url']);
        $this->assertSame('/img.jpg', $card['thumbnail']);
        $this->assertSame(1, count($card['categories']));
        $this->assertSame('projects', $card['categories'][0]['slug']);
        $this->assertSame('projects/category/projects', $card['categories'][0]['url']);
    }
}
