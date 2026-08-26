<?php

namespace Tests\Unit\Services;

use App\Enums\PageStatus;
use App\Models\MediaFile;
use App\Models\MediaFileUsage;
use App\Models\Page;
use App\Services\MediaUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUsageServiceTest extends TestCase
{
    use RefreshDatabase;

    private MediaUsageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = app(MediaUsageService::class);
    }

    public function test_extract_paths_finds_path_in_json_attribute(): void
    {
        Storage::disk('public')->put('media/hero.jpg', 'x');

        $page = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'status' => PageStatus::Draft->value,
            'top_section' => [
                ['type' => 'image', 'data' => ['url' => 'media/hero.jpg']],
            ],
        ]);

        $paths = $this->service->extractPaths($page->fresh());

        $this->assertArrayHasKey('top_section', $paths);
        $this->assertContains('media/hero.jpg', $paths['top_section']);
    }

    public function test_extract_paths_ignores_external_urls(): void
    {
        $page = Page::create([
            'title' => 'X',
            'slug' => 'x',
            'status' => PageStatus::Draft->value,
            'top_section' => [
                ['data' => ['url' => 'https://example.com/foo.jpg']],
            ],
        ]);

        $this->assertSame([], $this->service->extractPaths($page->fresh()));
    }

    public function test_extract_paths_ignores_strings_without_extension(): void
    {
        $page = Page::create([
            'title' => 'X',
            'slug' => 'x',
            'status' => PageStatus::Draft->value,
            'top_section' => [
                ['data' => ['icon' => 'heroicon-o-photo']],
            ],
        ]);

        $this->assertSame([], $this->service->extractPaths($page->fresh()));
    }

    public function test_sync_creates_media_file_and_usage_when_file_exists(): void
    {
        Storage::disk('public')->put('media/thumb.png', 'x');

        $page = Page::create([
            'title' => 'X',
            'slug' => 'x',
            'status' => PageStatus::Draft->value,
            'top_section' => [
                ['data' => ['url' => 'media/thumb.png']],
            ],
        ]);

        $this->assertDatabaseHas('media_files', ['path' => 'media/thumb.png']);
        $this->assertDatabaseHas('media_file_usages', [
            'usable_type' => Page::class,
            'usable_id' => $page->id,
            'field' => 'top_section',
        ]);
    }

    public function test_sync_skips_paths_that_do_not_exist_on_disk(): void
    {
        $page = Page::create([
            'title' => 'X',
            'slug' => 'x',
            'status' => PageStatus::Draft->value,
            'top_section' => [
                ['data' => ['url' => 'media/ghost.png']],
            ],
        ]);

        $this->assertDatabaseMissing('media_files', ['path' => 'media/ghost.png']);
        $this->assertSame(0, MediaFileUsage::where('usable_id', $page->id)->count());
    }

    public function test_sync_removes_stale_usages_and_adds_new_ones(): void
    {
        Storage::disk('public')->put('media/a.png', 'x');
        Storage::disk('public')->put('media/b.png', 'x');

        $page = Page::create([
            'title' => 'X',
            'slug' => 'x',
            'status' => PageStatus::Draft->value,
            'top_section' => [['data' => ['url' => 'media/a.png']]],
        ]);

        $page->top_section = [['data' => ['url' => 'media/b.png']]];
        $page->save();

        $this->assertDatabaseMissing('media_file_usages', [
            'usable_id' => $page->id,
            'media_file_id' => MediaFile::where('path', 'media/a.png')->value('id'),
        ]);
        $this->assertDatabaseHas('media_file_usages', [
            'usable_id' => $page->id,
            'media_file_id' => MediaFile::where('path', 'media/b.png')->value('id'),
        ]);
    }

    public function test_sync_is_idempotent(): void
    {
        Storage::disk('public')->put('media/x.png', 'x');

        $page = Page::create([
            'title' => 'X',
            'slug' => 'x',
            'status' => PageStatus::Draft->value,
            'top_section' => [['data' => ['url' => 'media/x.png']]],
        ]);

        $countBefore = MediaFileUsage::where('usable_id', $page->id)->count();

        $page->save();
        $page->save();

        $this->assertSame($countBefore, MediaFileUsage::where('usable_id', $page->id)->count());
    }

    public function test_remove_all_for_model_deletes_usages_but_not_media_file(): void
    {
        Storage::disk('public')->put('media/x.png', 'x');

        $page = Page::create([
            'title' => 'X',
            'slug' => 'x',
            'status' => PageStatus::Draft->value,
            'top_section' => [['data' => ['url' => 'media/x.png']]],
        ]);
        $mediaFileId = MediaFile::where('path', 'media/x.png')->value('id');

        $page->delete();

        $this->assertSame(0, MediaFileUsage::where('usable_id', $page->id)->count());
        $this->assertDatabaseHas('media_files', ['id' => $mediaFileId]);
    }

    public function test_extract_paths_finds_scalar_string_path(): void
    {
        Storage::disk('public')->put('media/thumb.jpg', UploadedFile::fake()->image('thumb.jpg')->getContent());

        // Проверяем через сервис напрямую, без модели — экстрактор
        // должен находить пути в скалярных строковых атрибутах.
        $model = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $guarded = [];
        };
        $model->setRawAttributes(['thumbnail' => 'media/thumb.jpg']);

        $paths = $this->service->extractPaths($model);

        $this->assertSame(['thumbnail' => ['media/thumb.jpg']], $paths);
    }
}
