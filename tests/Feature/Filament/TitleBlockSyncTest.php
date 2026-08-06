<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Projects\Pages\CreateProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TitleBlockSyncTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: class-string}> */
    public static function createPages(): array
    {
        return [
            'post' => [CreatePost::class],
            'project' => [CreateProject::class],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->userOfRole(UserRole::Admin), 'web');
    }

    #[DataProvider('createPages')]
    public function test_create_form_starts_with_an_empty_title_block(string $page): void
    {
        $block = $this->firstMainBlock(Livewire::test($page)->get('data'));

        $this->assertSame('title', $block['type']);
        $this->assertSame('h2', $block['data']['type']);
        $this->assertEmpty($block['data']['title'] ?? null);
    }

    #[DataProvider('createPages')]
    public function test_record_title_is_copied_into_the_title_block(string $page): void
    {
        $data = Livewire::test($page)
            ->set('data.title', 'Новость про мост')
            ->get('data');

        $this->assertSame('Новость про мост', $this->firstMainBlock($data)['data']['title']);
        $this->assertSame('novost-pro-most', $data['slug']);
    }

    #[DataProvider('createPages')]
    public function test_retyped_record_title_reaches_the_title_block(string $page): void
    {
        $data = Livewire::test($page)
            ->set('data.title', 'Черновой заголовок')
            ->set('data.title', 'Финальный заголовок')
            ->get('data');

        $this->assertSame('Финальный заголовок', $this->firstMainBlock($data)['data']['title']);
    }

    #[DataProvider('createPages')]
    public function test_manually_edited_heading_is_kept(string $page): void
    {
        $component = Livewire::test($page)->set('data.title', 'Новость про мост');

        $key = array_key_first($component->get('data')['main_section']);

        $data = $component
            ->set("data.main_section.{$key}.data.title", 'Свой заголовок')
            ->set('data.title', 'Новость про мост и переправу')
            ->get('data');

        $this->assertSame('Свой заголовок', $this->firstMainBlock($data)['data']['title']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function firstMainBlock(array $data): array
    {
        $blocks = $data['main_section'] ?? [];

        $this->assertCount(1, $blocks);

        return reset($blocks);
    }
}
