<?php

namespace Tests\Feature\Filament;

use App\Enums\PostStatus;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SeoSyncTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: class-string, 1: class-string, 2: class-string<Model>}> */
    public static function resources(): array
    {
        return [
            'post' => [CreatePost::class, EditPost::class, Post::class],
            'project' => [CreateProject::class, EditProject::class, Project::class],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->userOfRole(UserRole::Admin), 'web');
    }

    #[DataProvider('resources')]
    public function test_create_form_fills_seo_from_title_and_description(string $createPage): void
    {
        $data = Livewire::test($createPage)
            ->set('data.title', 'Новость про мост')
            ->set('data.description', 'Короткое описание новости')
            ->get('data');

        $this->assertSame('Новость про мост', $data['meta_title']);
        $this->assertSame('Короткое описание новости', $data['meta_description']);
    }

    #[DataProvider('resources')]
    public function test_edit_form_updates_seo_that_still_mirrors_the_record(string $createPage, string $editPage, string $modelClass): void
    {
        $record = $this->record($modelClass, [
            'meta_title' => 'Заголовок',
            'meta_description' => 'Описание',
        ]);

        $data = Livewire::test($editPage, ['record' => $record->getRouteKey()])
            ->set('data.title', 'Новый заголовок')
            ->set('data.description', 'Новое описание')
            ->get('data');

        $this->assertSame('Новый заголовок', $data['meta_title']);
        $this->assertSame('Новое описание', $data['meta_description']);
    }

    #[DataProvider('resources')]
    public function test_edit_form_keeps_hand_written_seo(string $createPage, string $editPage, string $modelClass): void
    {
        $record = $this->record($modelClass, [
            'meta_title' => 'Свой SEO-заголовок',
            'meta_description' => 'Своё SEO-описание',
        ]);

        $data = Livewire::test($editPage, ['record' => $record->getRouteKey()])
            ->set('data.title', 'Новый заголовок')
            ->set('data.description', 'Новое описание')
            ->get('data');

        $this->assertSame('Свой SEO-заголовок', $data['meta_title']);
        $this->assertSame('Своё SEO-описание', $data['meta_description']);
    }

    #[DataProvider('resources')]
    public function test_edit_form_fills_empty_seo(string $createPage, string $editPage, string $modelClass): void
    {
        $record = $this->record($modelClass, []);

        $data = Livewire::test($editPage, ['record' => $record->getRouteKey()])
            ->set('data.title', 'Новый заголовок')
            ->get('data');

        $this->assertSame('Новый заголовок', $data['meta_title']);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $attributes
     */
    private function record(string $modelClass, array $attributes): Model
    {
        return $modelClass::create([
            'title' => 'Заголовок',
            'description' => 'Описание',
            'slug' => 'zagolovok',
            'status' => $modelClass === Post::class ? PostStatus::Draft->value : ProjectStatus::Draft->value,
            ...$attributes,
        ]);
    }
}
