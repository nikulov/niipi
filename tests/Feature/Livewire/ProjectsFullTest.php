<?php

namespace Tests\Feature\Livewire;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Enums\ProjectStatus;
use App\Livewire\Components\ProjectsFull;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
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

    public function test_updated_category_resets_invalid_slug(): void
    {
        Livewire::test(ProjectsFull::class)
            ->set('category', 'missing')
            ->assertSet('category', null);
    }

    public function test_config_properties_are_locked_against_client_updates(): void
    {
        $updates = [
            'limit' => 999999,
            'categoryIds' => [[1, 2]],
            'componentKey' => 'block:projects-full:page:1:0',
        ];

        foreach ($updates as $property => $value) {
            try {
                Livewire::test(ProjectsFull::class)->set($property, $value);
                $this->fail("Property [{$property}] is writable from the client.");
            } catch (CannotUpdateLockedPropertyException $e) {
                $this->assertSame($property, $e->property);
            }
        }
    }

    public function test_limit_from_block_data_is_clamped(): void
    {
        $perPage = fn (int $limit) => Livewire::test(ProjectsFull::class, ['limit' => $limit])
            ->viewData('cards')
            ->perPage();

        $this->assertSame(ProjectsFull::MAX_LIMIT, $perPage(999999));
        $this->assertSame(ProjectsFull::MIN_LIMIT, $perPage(0));
        $this->assertSame(ProjectsFull::MIN_LIMIT, $perPage(-5));
    }

    public function test_category_ids_from_block_data_are_sanitized(): void
    {
        $category = Category::create([
            'name' => 'Design',
            'slug' => 'design',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Projects->value,
        ]);

        $categorized = Project::create([
            'title' => 'Categorized',
            'description' => 'Desc',
            'slug' => 'categorized',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $categorized->categories()->attach($category->id);

        Project::create([
            'title' => 'Loose',
            'description' => 'Desc',
            'slug' => 'loose',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $total = fn (array $categoryIds) => Livewire::test(ProjectsFull::class, ['categoryIds' => $categoryIds])
            ->viewData('cards')
            ->total();

        // A nested array reaches whereIn(), which rejects one that does not
        // flatten to the same length — exactly the crash seen in production.
        $this->assertSame(2, $total([[$category->id, $category->id + 1]]));

        $this->assertSame(1, $total(['abc', -1, (string) $category->id]));
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
