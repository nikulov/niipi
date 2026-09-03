<?php

namespace Tests\Feature\Livewire;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Enums\PostStatus;
use App\Livewire\Components\NewsFull;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class NewsFullTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_category_resets_invalid_slug(): void
    {
        $category = Category::create([
            'name' => 'News',
            'slug' => 'news',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Posts->value,
        ]);

        $post = Post::create([
            'title' => 'Post A',
            'description' => 'Desc',
            'slug' => 'post-a',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $post->categories()->attach($category->id);

        Livewire::test(NewsFull::class)
            ->call('setCategory', 'missing')
            ->assertSet('category', null);
    }

    public function test_updated_category_resets_invalid_slug(): void
    {
        Livewire::test(NewsFull::class)
            ->set('category', 'missing')
            ->assertSet('category', null);
    }

    public function test_config_properties_are_locked_against_client_updates(): void
    {
        $updates = [
            'limit' => 999999,
            'categoryIds' => [[1, 2]],
            'componentKey' => 'block:news-full:page:1:0',
        ];

        foreach ($updates as $property => $value) {
            try {
                Livewire::test(NewsFull::class)->set($property, $value);
                $this->fail("Property [{$property}] is writable from the client.");
            } catch (CannotUpdateLockedPropertyException $e) {
                $this->assertSame($property, $e->property);
            }
        }
    }

    public function test_limit_from_block_data_is_clamped(): void
    {
        $perPage = fn (int $limit) => Livewire::test(NewsFull::class, ['limit' => $limit])
            ->viewData('cards')
            ->perPage();

        $this->assertSame(NewsFull::MAX_LIMIT, $perPage(999999));
        $this->assertSame(NewsFull::MIN_LIMIT, $perPage(0));
        $this->assertSame(NewsFull::MIN_LIMIT, $perPage(-5));
    }

    public function test_category_ids_from_block_data_are_sanitized(): void
    {
        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Posts->value,
        ]);

        $categorized = Post::create([
            'title' => 'Categorized',
            'description' => 'Desc',
            'slug' => 'categorized',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $categorized->categories()->attach($category->id);

        Post::create([
            'title' => 'Loose',
            'description' => 'Desc',
            'slug' => 'loose',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $total = fn (array $categoryIds) => Livewire::test(NewsFull::class, ['categoryIds' => $categoryIds])
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
            'name' => 'Tech',
            'slug' => 'tech',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Posts->value,
        ]);

        $past = Post::create([
            'title' => 'Past',
            'description' => 'Desc',
            'slug' => 'past',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $future = Post::create([
            'title' => 'Future',
            'description' => 'Desc',
            'slug' => 'future',
            'status' => PostStatus::Published->value,
            'published_at' => now()->addDay(),
        ]);

        $past->categories()->attach($category->id);
        $future->categories()->attach($category->id);

        $items = Livewire::test(NewsFull::class)->viewData('categoryItems');

        $all = $items->firstWhere('slug', null);
        $tech = $items->firstWhere('slug', 'tech');

        $this->assertSame(1, $all['count']);
        $this->assertSame(1, $tech['count']);
    }
}
